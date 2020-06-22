<?php

namespace modules\shopandshow\models\shop;

use common\helpers\ArrayHelper;
use common\helpers\Strings;
use common\models\Setting;
use modules\shopandshow\components\shop\ShopDiscountCalculateBasket;
use modules\shopandshow\components\shop\ShopDiscountCalculateFuser;
use modules\shopandshow\controllers\shop\shopdiscount\ConfigurationController;
use modules\shopandshow\controllers\shop\ShopDiscountLogicController;
use modules\shopandshow\models\shop\shopdiscount\Configuration;
use modules\shopandshow\models\shop\shopdiscount\ConfigurationGroup;
use modules\shopandshow\models\shop\shopdiscount\Entity;
use modules\shopandshow\query\shop\ShopDiscountActiveQuery;
use skeeks\cms\components\Cms;
use skeeks\cms\Exception;
use skeeks\cms\models\behaviors\HasStorageFile;
use skeeks\cms\models\CmsContentElement;
use skeeks\cms\models\StorageFile;
use skeeks\cms\shop\models\ShopDiscount as SXShopDiscount;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\data\ActiveDataProvider;

/**
 * @property string $code
 * @property int $gift
 * @property string $giftTextValue
 * @property StorageFile $image
 * @property Configuration[] $configurations
 * @property SsShopDiscountLogic[] $shopDiscountLogics
 * @property ShopDiscountCoupon[] $shopDiscountCoupons
 */
class ShopDiscount extends SXShopDiscount
{
    CONST VALUE_TYPE_DELIVERY = "D";
    CONST VALUE_TYPE_LADDER = "L";
    CONST VALUE_TYPE_GIFT = "G";
    CONST VALUE_TYPE_BASKET_QTY = 'B';

    const DISCOUNT_CODE_500RUB = '500RUB';

    //[DEPRECATED] Перевести на на self::getFreeDeliveryPrice() || Setting::getFreeDeliveryPrice()
    const PROMO_SHIPPING_ORDER_PRICE = 4990; //Если стоимость заказа меньше этой суммы то цена доставки = PROMO_SHIPPING_PRICE
    const PROMO_SHIPPING_PRICE = 199; //TODO Вынести в настройки
    const DELIVERY_DISCOUNT_SUM = 500; //TODO Вынести в настройки

    // product_id подарка для типа акции VALUE_TYPE_GIFT
    public $gift;

    public static $freeDeliveryPrice = 0;

    public static function getValueTypes()
    {
        return [
            self::VALUE_TYPE_P => \Yii::t('skeeks/shop/app', 'In percentages'),
            self::VALUE_TYPE_F => \Yii::t('skeeks/shop/app', 'Fixed amount'),
            //self::VALUE_TYPE_S => \Yii::t('skeeks/shop/app', 'Set the price for the goods'),
            self::VALUE_TYPE_DELIVERY => 'Скидка на доставку',
            self::VALUE_TYPE_LADDER => 'Лестница скидок',
            self::VALUE_TYPE_GIFT => 'Подарок',
            self::VALUE_TYPE_BASKET_QTY => 'Скидка на наименьшую сумму позиции корзины'
        ];
    }

    public function initDefaultValues()
    {
        foreach ($this->rules() as $rule) {
            if ($rule[1] == 'default') {
                if (!is_array($rule[0])) $rule[0] = [$rule[0]];
                foreach ($rule[0] as $attr) {
                    $this->{$attr} = $rule['value'];
                }
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'site_id', 'max_uses', 'count_uses', 'type', 'count_size',
                'active_from', 'active_to', 'count_from', 'count_to', 'action_size', 'priority', 'version', 'image_id'], 'integer'],
            [['max_discount', 'value', 'min_order_sum'], 'number'],
            [['currency_code', 'name', 'code'], 'required'],
            [['conditions', 'unpack'], 'string'],
            [['active', 'renewal', 'value_type', 'count_period', 'count_type', 'action_type', 'last_discount'], 'string', 'max' => 1],
            [['name', 'notes', 'xml_id', 'code'], 'string', 'max' => 255],
            [['coupon'], 'string', 'max' => 20],
            [['currency_code'], 'string', 'max' => 3],

            [['active'], 'default', 'value' => Cms::BOOL_Y],
            [['last_discount'], 'default', 'value' => Cms::BOOL_N],
            [['type'], 'default', 'value' => self::TYPE_DEFAULT],
            [['value_type'], 'default', 'value' => self::VALUE_TYPE_F],
            [['value', 'max_discount', 'min_order_sum'], 'default', 'value' => 0],
            [['priority'], 'default', 'value' => 1],
            [['currency_code'], 'default', 'value' => 'RUB'],
            [['site_id'], 'default', 'value' => \Yii::$app->cms->site->id],

            ['typePrices', 'safe'], // allow set permissions with setAttributes()
            ['gift', 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => \Yii::t('skeeks/shop/app', 'ID'),
            'created_by' => \Yii::t('skeeks/shop/app', 'Created By'),
            'updated_by' => \Yii::t('skeeks/shop/app', 'Updated By'),
            'created_at' => \Yii::t('skeeks/shop/app', 'Created At'),
            'updated_at' => \Yii::t('skeeks/shop/app', 'Updated At'),
            'site_id' => \Yii::t('skeeks/shop/app', 'Site'),
            'active' => \Yii::t('skeeks/shop/app', 'Active'),
            'active_from' => \Yii::t('skeeks/shop/app', 'Active from'),
            'active_to' => \Yii::t('skeeks/shop/app', 'Active to'),
            'renewal' => \Yii::t('skeeks/shop/app', 'Renewal'),
            'name' => \Yii::t('skeeks/shop/app', 'Name'),
            'max_uses' => \Yii::t('skeeks/shop/app', 'Max Uses'),
            'count_uses' => \Yii::t('skeeks/shop/app', 'Count Uses'),
            'coupon' => 'Промокод', //\Yii::t('skeeks/shop/app', 'Coupon'),
            'max_discount' => \Yii::t('skeeks/shop/app', 'The maximum amount of discount (in currency of discount ; 0 - the discount is not limited to)'),
            'value_type' => \Yii::t('skeeks/shop/app', 'Discount Type'),
            'value' => \Yii::t('skeeks/shop/app', 'Markdown'),
            'currency_code' => \Yii::t('skeeks/shop/app', 'Currency discount'),
            'min_order_sum' => 'Минимальная сумма заказа (0 - нет ограничений)',//\Yii::t('skeeks/shop/app', 'Min Order Sum'),
            'notes' => \Yii::t('skeeks/shop/app', 'Short description (up to 255 characters)'),
            'type' => \Yii::t('skeeks/shop/app', 'Type'),
            'xml_id' => \Yii::t('skeeks/shop/app', 'Xml ID'),
            'count_period' => \Yii::t('skeeks/shop/app', 'Count Period'),
            'count_size' => \Yii::t('skeeks/shop/app', 'Count Size'),
            'count_type' => \Yii::t('skeeks/shop/app', 'Count Type'),
            'count_from' => \Yii::t('skeeks/shop/app', 'Count From'),
            'count_to' => \Yii::t('skeeks/shop/app', 'Count To'),
            'action_size' => \Yii::t('skeeks/shop/app', 'Action Size'),
            'action_type' => \Yii::t('skeeks/shop/app', 'Action Type'),
            'priority' => \Yii::t('skeeks/shop/app', 'Priority applicability'),
            'last_discount' => \Yii::t('skeeks/shop/app', 'Stop further application of discounts'),
            'conditions' => \Yii::t('skeeks/shop/app', 'Conditions'),
            'unpack' => \Yii::t('skeeks/shop/app', 'Unpack'),
            'version' => \Yii::t('skeeks/shop/app', 'Version'),
            'typePrices' => \Yii::t('skeeks/shop/app', 'Types of prices, to which the discount is applicable'),

            'gift' => 'Подарок',
            'configurations' => 'Условия',
            'image_id' => 'image ID',
            'code' => 'Символьный код',
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            BlameableBehavior::className(),
            TimestampBehavior::className(),
            HasStorageFile::className() =>
                [
                    'class' => HasStorageFile::className(),
                    'fields' => ['image_id']
                ],
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getConfigurations()
    {
        return $this->hasMany(Configuration::className(), ['shop_discount_id' => 'id'])->indexBy('id')->orderBy('shop_discount_entity_id ASC')->inverseOf('shopDiscount');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopDiscountLogics()
    {
        return $this->hasMany(SsShopDiscountLogic::className(), ['shop_discount_id' => 'id'])->indexBy('id')->orderBy('value DESC')->inverseOf('shopDiscount');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopDiscountCoupons()
    {
        return $this->hasMany(ShopDiscountCoupon::className(), ['shop_discount_id' => 'id'])->indexBy('id')->inverseOf('shopDiscount');
    }

    public function getPromoCode()
    {
        $promoCodes = $this->shopDiscountCoupons;
        if ($promoCodes) return reset($promoCodes)->coupon;
        return null;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getImage()
    {
        return $this->hasOne(StorageFile::className(), ['id' => 'image_id']);
    }

    /**
     * @return ActiveDataProvider
     */
    public function getConfigDataProvider()
    {
        $query = $this->getConfigurations()->joinWith('entity');

        return new ActiveDataProvider(['query' => $query]);
    }

    /**
     * Формирует текстовое наименование подарка
     * @return string
     */
    public function getGiftTextValue()
    {
        if ($this->value_type != self::VALUE_TYPE_GIFT) return $this->value;

        $shopProduct = CmsContentElement::findOne(intval($this->gift));
        return '[' . $shopProduct->code . '] ' . $shopProduct->name;
    }

    /**
     * TODO: вместо метода save нужно использовать соответствующий метод update контроллера AdminDiscountController,
     *       но я пока не нашел как этот метод там имплементировать ввиду особенностей реализации skeeks cms
     * @param bool $runValidation
     * @param null $attributeNames
     *
     * @return bool
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            // сохраняем значение "подарок" в value (вместо суммы скидки)
            if ($this->value_type == self::VALUE_TYPE_GIFT) {
                $this->value = $this->gift;
            }
            if (!parent::save($runValidation, $attributeNames)) {
                throw new Exception('Не удалось сохранить акцию');
            }

            // только для веб сохранения
            if (\Yii::$app instanceof \yii\web\Application) {
                if ($this->value_type == self::VALUE_TYPE_LADDER) {
                    ShopDiscountLogicController::actionSave($this);
                }
                ConfigurationController::actionSave($this);
            }

            $transaction->commit();

        } catch (\Exception $e) {
            if (\Yii::$app instanceof \yii\web\Application) {
                echo '<div class="alert-danger alert fade in">' . $e->getMessage() . '</div>';
            }
            $transaction->rollBack();
            return false;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        parent::afterFind();
        // формирует виртуальный атрибут gift для формы
        if ($this->value_type == self::VALUE_TYPE_GIFT) $this->gift = (int)$this->value;
    }

    /**
     * @return ShopDiscountActiveQuery
     */
    public static function find()
    {
        return new ShopDiscountActiveQuery(get_called_class());
    }

    /**
     * Возвращает список акций для пересчета
     * @param string|array $discountValueType
     * @return ShopDiscount[]
     */
    public static function getDiscountsToRecalc($discountValueType)
    {
        return static::find()
            ->active()
            ->orderBy(['shop_discount.priority' => SORT_ASC])
            ->andWhere([
                'or',
                ['shop_discount.site_id' => ""],
                ['shop_discount.site_id' => null],
                ['shop_discount.site_id' => \Yii::$app->cms->site->id],
            ])
            ->andWhere(['shop_discount.value_type' => $discountValueType])
            ->all();
    }

    /**
     * Возвращает список акций "подарок" при пересчете корзины
     * @return ShopDiscount[]
     */
    public static function getGiftDiscountsToRecalc()
    {
        return static::find()
            ->active()
            ->orderBy(['shop_discount.priority' => SORT_ASC])
            ->andWhere([
                'or',
                ['shop_discount.site_id' => ""],
                ['shop_discount.site_id' => null],
                ['shop_discount.site_id' => \Yii::$app->cms->site->id],
            ])
            ->andWhere(['shop_discount.value_type' => self::VALUE_TYPE_GIFT])
            ->all();
    }

    /**
     * Проверяет, соответствует ли данная акция хотя бы одному элементу корзины
     * Возвращает список элементов корзины, удовлетворяющих условию акции
     * @param ShopFuser $shopFuser
     *
     * @return ShopBasket[] $baskets
     */
    public function canApplyFuser(ShopFuser $shopFuser)
    {
        $baskets = [];
        foreach ($shopFuser->shopBasketsWithoutGifts as $shopBasket) {
            if ($this->canApply($shopBasket)) {
                $baskets[] = $shopBasket;
            }
        }
        return $baskets;
    }

    /**
     * Проверяет, соответствует ли данная акция продукту корзины
     * @param ShopBasket $shopBasket
     * @param bool $checkCoupon
     *
     * @return bool
     */
    public function canApply(ShopBasket $shopBasket, $checkCoupon = false)
    {
        // todo: должна ли работать акция если нет условий вообще никаких? Я думаю что нет, дабы не было ложных срабатываний
        if (empty($this->configurations)) return false;

        /*
        // проверка "ограничения" по юзерам
        if (!\Yii::$app->user->can($this->permissionName)) {
            return false;
        }
        */

        // Получаем продует
        $cmsContentElement = \common\lists\Contents::getContentElementById($shopBasket->product->id);
        $shopProduct = ShopProduct::getInstanceByContentElement($cmsContentElement);

        // сверяем ограничение акции по типу цен
        $typePrices = ArrayHelper::getColumn($this->typePrices, 'id');
        if (!empty($typePrices) && !ArrayHelper::isIn($shopProduct->getTypePriceId(), $typePrices)) return false;

        $couponExists = false;

        /** @var ConfigurationGroup[] $configurationGroups */
        $configurationGroups = ConfigurationGroup::createFromArray($this->configurations);

        // если хотя бы одна группа не подходит, значит и вся акция не подходит
        foreach ($configurationGroups as $configurationGroup) {
            if (!$configurationGroup->validate($shopBasket, $configurationGroups)) {
                return false;
            }

            if ($checkCoupon && $configurationGroup->isCoupon()) $couponExists = true;
        }

        if ($checkCoupon) return $couponExists;

        return true;
    }

    public function canProductApply(ShopProduct $shopProduct, $checkCoupon = false)
    {
        // сверяем ограничение акции по типу цен
        $typePrices = ArrayHelper::getColumn($this->typePrices, 'id');
        if (!empty($typePrices) && !ArrayHelper::isIn($shopProduct->getTypePriceId(), $typePrices)) return false;

        $couponExists = false;

        /** @var ConfigurationGroup[] $configurationGroups */
        $configurationGroups = ConfigurationGroup::createFromArray($this->configurations);

        $shopBasket = new ShopBasket();
        $shopBasket->fuser_id = \Yii::$app->shop->shopFuser->id;
        $shopBasket->product_id = $shopProduct->id;

        // если хотя бы одна группа не подходит, значит и вся акция не подходит
        foreach ($configurationGroups as $configurationGroup) {
            // если не надо проверять купон, то и не проверяем
            if (!$checkCoupon && $configurationGroup->isCoupon()) {
                continue;
            }

            if (!$configurationGroup->validate($shopBasket)) {
                return false;
            }
            if ($checkCoupon && $configurationGroup->isCoupon()) $couponExists = true;
        }

        if ($checkCoupon) return $couponExists;

        return true;
    }

    public static function fuserRecalculate(ShopFuser $shopFuser)
    {
        $shopDiscountCalculate = new ShopDiscountCalculateFuser(['shopFuser' => $shopFuser]);
        return $shopDiscountCalculate->recalculate();
    }

    public static function basketRecalculate(ShopBasket $shopBasket)
    {
        $shopDiscountCalculate = new ShopDiscountCalculateBasket(['shopBasket' => $shopBasket]);
        return $shopDiscountCalculate->recalculate();
    }

    /**
     * Получить ид товаров в акции
     * @return array
     */
    public function getProductsIds()
    {
        /** @var Configuration[] $configurations */
        $configurations = $this->getConfigurations()
            ->joinWith(['entity'])
            ->andWhere(['OR', ['class' => Entity::LOTS_ENTITY], ['class' => Entity::CTS_PLUS_LOTS_ENTITY]])->all();

        $products = [];

        foreach ($configurations as $configuration) {
            $products = array_merge($products,
                ArrayHelper::getColumn($configuration->values, 'value', false));
        }

        return $products;
    }

    public function getActiveProductsIdsCodeS()
    {
        $sql = "
SELECT 
  val.value AS product_id
FROM ss_shop_discount_values AS val
INNER JOIN ss_shop_discount_configuration AS conf 
  ON conf.id = val.shop_discount_configuration_id
INNER JOIN ss_shop_discount_entity AS entity 
  ON entity.id = conf.shop_discount_entity_id
INNER JOIN shop_discount AS sd 
  ON sd.id = conf.shop_discount_id AND sd.code='codes' AND (sd.active_from <= UNIX_TIMESTAMP() AND sd.active_to >= UNIX_TIMESTAMP())
INNER JOIN shop_discount_coupon AS sd_coupon 
  ON sd.id = sd_coupon.shop_discount_id
WHERE 
  entity.class = 'ForLots' 
  AND sd_coupon.is_active = 1 
  AND sd_coupon.coupon IS NOT NULL 
  AND (sd_coupon.active_from <= UNIX_TIMESTAMP() AND sd_coupon.active_to >= UNIX_TIMESTAMP())
GROUP BY product_id";

        $products = \Yii::$app->db->createCommand($sql)->queryAll();

        $productsIds = [];

        if ($products) {
            $productsIds = array_merge($productsIds,
                ArrayHelper::getColumn($products, 'product_id', false));
        }

        return $productsIds;
    }

    /**
     * Получить ид категорий в акции
     * @return array
     */
    public function getCategoryIds()
    {
        /** @var Configuration[] $configurations */
        $configurations = $this->getConfigurations()
            ->joinWith(['entity'])
            ->andWhere(['class' => Entity::SECTION_ENTITY])->all();

        $categories = [];

        foreach ($configurations as $configuration) {
            $categories = array_merge($categories,
                ArrayHelper::getColumn($configuration->values, 'value', false));
        }

        return $categories;
    }

    /**
     * Получить сумму корзины по акции
     * @return int
     */
    public function getSum()
    {
        /** @var Configuration $configuration */
        $configuration = $this->getConfigurations()
            ->joinWith(['entity'])
            ->andWhere(['class' => Entity::SUM_ENTITY])->one();

        $sum = 0;

        if ($configuration) {
            $values = ArrayHelper::getColumn($configuration->values, 'value', false);
            if ($values) {
                $sum = end($values);
            }
        }

        return $sum;
    }

    /**
     * Получить предварительную стоимость по акции
     * @param int $price
     *
     * @return int $price
     */
    public function getDiscountedPrice($price)
    {
        // в процентах
        if ($this->value_type == self::VALUE_TYPE_P) {
            $percent = $this->value / 100;
            $discountPrice = intval($price * $percent);
            // ограничение на макс. скидку
            if ($this->max_discount > 0 && $discountPrice > $this->max_discount) $discountPrice = $this->max_discount;

            return $price - $discountPrice;
        }

        return $price;
    }

    /**
     * Получить предварительную стоимость по акции (отформатированную)
     * @param $price
     *
     * @return string
     */
    public function getDiscountedPriceMoney($price)
    {
        return Strings::getMoneyFormat($this->getDiscountedPrice($price));
    }

    /**
     * Получение цены бесплатной доставки в зависимости от расписания
     * @deprecated 
     * @return int
     */
    public static function getFreeDeliveryPrice()
    {


        return Setting::getFreeDeliveryPrice();

//
//        if (self::$freeDeliveryPrice) {
//            return self::$freeDeliveryPrice;
//        }
//
//        if (date('Y-m-d H') >= date('2019-04-02 07') && date('Y-m-d H:i:s') <= date('2019-04-03 06:59:59')) {
//            $freeDeliveryPrice = 3990;
//        } elseif (date('Y-m-d H') >= date('2019-01-05 07') && date('Y-m-d H:i:s') <= date('2019-01-07 06:59:59')) {
//            $freeDeliveryPrice = 4990;
//        } else {
//            $freeDeliveryPrice = Promo::isLowSeasonPeriod() ? 4990 : 5990;
//        }
//
//        self::$freeDeliveryPrice = $freeDeliveryPrice;
//
//        return $freeDeliveryPrice;
    }
}