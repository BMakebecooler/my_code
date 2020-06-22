<?php

namespace modules\shopandshow\models\shop;

use common\helpers\ArrayHelper;
use common\helpers\Promo;
use common\helpers\Strings;
use common\helpers\User;
use common\models\cmsContent\CmsContentElement;
use modules\shopandshow\models\shares\badges\SsBadge;
use modules\shopandshow\models\shares\badges\SsBadgeProduct;
use skeeks\cms\components\Cms;
use skeeks\cms\measure\models\Measure;
use skeeks\cms\models\CmsStorageFile;
use skeeks\cms\shop\models\ShopProduct as SP;
use skeeks\cms\shop\models\ShopTypePrice;
use skeeks\cms\shop\models\ShopProductPrice;
use skeeks\cms\shop\models\ShopViewedProduct;
use skeeks\modules\cms\money\Money;
use Yii;
use yii\db\Expression;


class ShopProduct extends SP
{
    /**
     * Название параметра признака логики отображения цены
     */
    const PRICE_ACTIVE = 'PRICE_ACTIVE';
    const TYPE_CARD = 'card';

    /**
     * Название ключа для хранения кол-ва просмотра товара
     */
    const PRODUCT_COUNTER_VIEW_KEY_NAME = 'product_counter_view_';
    const PRODUCT_VIEWED_KEY_NAME = 'product_viewed_';

    /**
     * @var CmsContentElement | ShopContentElement
     */
    public $contentElement = null;

    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'recur_scheme_length', 'trial_price_id', 'vat_id', 'measure_id'], 'integer'],
            [['quantity', 'weight', 'purchasing_price', 'quantity_reserved', 'width', 'length', 'height', 'measure_ratio'], 'number'],
            [['quantity_trace', 'price_type', 'recur_scheme_type', 'without_order', 'select_best_price', 'vat_included', 'can_buy_zero', 'negative_amount_trace', 'barcode_multi', 'subscribe'], 'string', 'max' => 1],
            [['tmp_id'], 'string', 'max' => 40],
            [['purchasing_currency'], 'string', 'max' => 3],
            [['quantity_trace', 'can_buy_zero', 'negative_amount_trace'], 'default', 'value' => Cms::BOOL_N],
            [['weight', 'width', 'length', 'height', 'purchasing_price'], 'default', 'value' => 0],
            [['subscribe'], 'default', 'value' => Cms::BOOL_Y],
            [['measure_ratio'], 'default', 'value' => 1],
            [['measure_ratio'], 'number', 'min' => 0.0001, 'max' => 9999999],
            [['purchasing_currency'], 'default', 'value' => Yii::$app->money->currencyCode],

            [['baseProductPriceValue'], 'number'],
            [['baseProductPriceCurrency'], 'string', 'max' => 3],

            [['vat_included'], 'default', 'value' => Cms::BOOL_Y],
            [['measure_id'], 'default', 'value' => function () {
                return (int)Measure::find()->def()->one()->id;
            }],

            [['product_type'], 'string', 'max' => 10],
            [['product_type'], 'default', 'value' => static::TYPE_SIMPLE],

            [['quantity'], 'default', 'value' => 0],
            //[['quantity'], 'default', 'value' => 1],
            [['quantity_reserved'], 'default', 'value' => 0],
        ];
    }

    /**
     * Если втавленный элемент является дочерним для другого то родительскому нужно изменить тип
     * @override
     * @param $event
     */
    public function _updateParentAfterInsert($event)
    {
        //Если есть родительский элемент
        if ($this->cmsContentElement->parent_content_element_id && $parentProduct = $this->cmsContentElement->parentContentElement->shopProduct)
        {
            $parentProduct->setAttribute('product_type', self::TYPE_OFFERS);
            $parentProduct->save();
        }
    }

    /**
     *
     * Получить цену по типу
     *
     * @param string $code
     *
     * @return null|ShopProductPrice
     */
    public function getProductPriceByType($code = 'BASE')
    {

        $priceType = ShopTypePrice::findOne(['code' => $code]);

        if ($priceType === null)
            return null;

        $price = ShopProductPrice::findOne([
            'product_id' => $this->id,
            'type_price_id' => $priceType->id]);

        if ($price === null) {
            $price = new ShopProductPrice();
            $price->currency_code = "RUB";
            $price->product_id = $this->id;
            $price->type_price_id = $priceType->id;
        }

        return $price;

    }

    /**
     * @param \skeeks\cms\models\CmsContentElement $cmsContentElement
     * @return static
     */
    static public function getInstanceByContentElement(\skeeks\cms\models\CmsContentElement $cmsContentElement)
    {

        /**
         * @var self $self
         */
        $self = parent::getInstanceByContentElement($cmsContentElement);

        if ($self) {
            $self->setContentElement($cmsContentElement);
        }

        return $self;
    }

    public function setContentElement($model)
    {
        $this->contentElement = $model;
    }

    public function setPriceTypeValue($type, $value = null)
    {

        if ((double)$value <= 0)
            throw new \skeeks\cms\Exception($value);

        /** @var ShopProductPrice $p */
        $p = $this->getProductPriceByType($type);

        if ($p === null)
            throw new \skeeks\cms\Exception('Нет Типа цены ' . $type);

        $p->price = $value;

        if (!$p->save())
            throw new \skeeks\cms\Exception(json_encode($p->getErrors()));

        return true;

    }

    public function getPriceTypeValue($type)
    {

        /** @var ShopProductPrice $p */
        $p = $this->getProductPriceByType($type);

        if ($p === null)
            throw new \skeeks\cms\Exception('Нет Типа цены ' . $type);

        return $p->price;

    }

    /**
     * Признак предожения
     * @return bool
     */
    public function isOffer()
    {
        return $this->product_type == self::TYPE_OFFERS;
    }

    /**
     * Признак карточки товара (цвета)
     * @return bool
     */
    public function isCard()
    {
        return $this->product_type == self::TYPE_CARD;
    }

    /**
     * Признак главной карточки товара
     * @return bool
     */
    public function isSimple()
    {
        return $this->product_type == self::TYPE_SIMPLE;
    }

    /**
     * @deprecated
     * Получить текущую цену продажи
     * @return string
     */
    public function getPrice()
    {

        return;

        if ($this->isDiscount()) {
            $price = ($price = $this->baseProductPrice->price) ? $price : false;
        } else {
            $price = ($price = $this->contentElement->price) ? $price->price : false;
        }

        $money = $price ? Money::fromString((string)$price, 'RUB') : null;

        if ($money) {
            $prefix = $this->isFrom() ? 'От ' : '';
            return $prefix . \Yii::$app->money->convertAndFormat($money);
        }

        return '';
    }

    /**
     * Вернуть минимальную цену
     * @return int
     */
    public function minPrice()
    {
        return (int)ArrayHelper::getValue($this, 'contentElement.price.min_price');
    }

    /**
     * Вернуть максимальную цену
     * @return int
     */
    public function maxPrice()
    {
        return (int)ArrayHelper::getValue($this, 'contentElement.price.max_price');
    }

    /**
     * Вернуть цену согласно установленному типу (По умолчанию это базовая цена)
     * @return int
     */
    public function basePrice()
    {
        return (int)ArrayHelper::getValue($this, 'contentElement.price.price');
    }

    /**
     * Вернуть тип цены
     * @return int
     */
    public function getTypePriceId()
    {
        return (int)$this->contentElement->price->type_price_id;
    }

    /**
     * Получить базовую цену в Money
     * @return string
     */
    public function getBasePriceMoney()
    {
        if ($basePrice = (int)$this->basePrice()) {
            return Strings::replaceSpaces(Strings::getMoneyFormat($basePrice));
        } elseif ($minPrice = (int)$this->minPrice()) {
            $prefix = $this->isFrom() ? 'От ' : '';
            return $prefix . Strings::replaceSpaces(Strings::getMoneyFormat($minPrice));
        }
        return '';
    }

    /**
     * Получить максимальную цену в Money
     * @return string
     */
    public function getMaxPriceMoney()
    {
        if ($maxPrice = (int)$this->maxPrice()) {
            return Strings::replaceSpaces(Strings::getMoneyFormat($maxPrice));
        }

        return '';
    }

    /**
     * Получить минимальную цену в Money
     * @return string
     */
    public function getMinPriceMoney()
    {
        if ($minPrice = (int)$this->minPrice()) {
            return Strings::replaceSpaces(Strings::getMoneyFormat($minPrice));
        }

        return '';
    }


    /**
     * Признак цены "От"
     * @return boolean
     */
    public function isFrom()
    {
        return !(int)$this->contentElement->price->price;
    }

    /**
     * @return boolean
     */
    public function isDiscount()
    {
        $baseTypePrice = \Yii::$app->shop->baseTypePrice;
        $baseTypePriceId = $baseTypePrice->id;
        $price = $this->contentElement->price;
        $basePrice = $this->basePrice();
        $maxPrice = $this->maxPrice();
    
        /**
         * Если базовая цена отличается от текущей то это признак скидки
         *
         */
        if ($price) {
            return ($price->type_price_id !== $baseTypePriceId) && ($basePrice != $maxPrice)
            && ($basePrice < $maxPrice);
        }

        return false;
    }


    /**
     * Признак базовой цены
     * @return boolean
     */
    public function isBase()
    {
        $baseTypePrice = \Yii::$app->shop->baseTypePrice;
        $baseTypePriceId = $baseTypePrice->id;

        return $this->contentElement->price->type_price_id === $baseTypePriceId;
    }


    /**
     * Значок процент скидки
     * @return integer
     */
    public function badgeDiscount()
    {
        $discount = 0;
        if ($this->isDiscount()) {

            $discount = (int)$this->contentElement->price->discount_percent;

            return abs($discount);
        }

        return $discount;
    }

    /**
     * Есть ли товар в наличии
     * @return bool
     */
    public function isAvailable()
    {
        return $this->quantity > 0;
    }

    /**
     * Доступен ли товар для покупки
     * @return bool
     */
    public function isSale()
    {
        return $this->isAvailable() && $this->contentElement->isShowProduct();
    }

    /**
     * @return bool
     */
    public function createNewView()
    {
        if ($this->isNewRecord) {
            return false;
        }

        Yii::$app->redis->incr(sprintf('%s%s', ShopProduct::PRODUCT_COUNTER_VIEW_KEY_NAME, $this->id));

        Yii::$app->redis->hset(sprintf('%s%s', ShopProduct::PRODUCT_VIEWED_KEY_NAME, $this->id), \Yii::$app->shop->shopFuser->id, time());


        /*            $shopViewdProduct = new ShopViewedProduct();
                    $shopViewdProduct->name = $this->cmsContentElement->name;
                    $shopViewdProduct->shop_product_id = $this->id;
                    $shopViewdProduct->site_id = \Yii::$app->cms->site->id;
                    $shopViewdProduct->shop_fuser_id = \Yii::$app->shop->shopFuser->id;

                    return $shopViewdProduct->save();*/
    }

    /**
     * @return bool
     */
    public function isRedBadge()
    {

        $date = date('Y-m-d H:i:s');

        $productsDates = [
            24 => [
                222525,
                221481,
                218609,
                777777,
                781414,
                781426,
                793728,
                793741,
                805944,
                828516,
                828950,
                842234,
                854317,
                860014,
                861553,
                861554,
                862183,
            ],

            25 => [
                226844,
                225981,
                225390,
                225095,
                224207,
                222108,
                221591,
                221580,
                221241,
                718236,
                781387,
                793690,
                801491,
                802952,
                802962,
                802969,
                807568,
                807610,
                807617,
                813025,
                813031,
                813379,
                813397,
                814170,
                816538,
                816540,
                816560,
                820781,
                821505,
                821671,
                823954,
                827875,
                827877,
                828537,
                828660,
                831004,
                831032,
                831040,
                831044,
                834114,
                836095,
                838627,
                838653,
                840853,
                841200,
                842226,
                844343,
                844705,
                844718,
                846763,
            ]
        ];

        if ($date >= '2017-11-24 07:00:00' && $date <= '2017-11-25 06:59:59') {
            return in_array($this->id, $productsDates[24]);
        }

        if ($date >= '2017-11-25 07:00:00' && $date <= '2017-11-26 06:59:59') {
            return in_array($this->id, $productsDates[25]);
        }

        return false;
    }

    public function isQuantityLow()
    {
        return ($this->quantity <= 3);
    }

    public function isQuantityEnough()
    {
        return ($this->quantity > 3 && $this->quantity <= 15);
    }

    public function isQuantityMany()
    {
        return ($this->quantity > 15);
    }

    public function getBadgeImageProductCard()
    {
        //Выборка файла плашки товара - отключено до завершения проверки запроса
        return null;

        $file = SsBadgeProduct::find()
            ->alias('badge_products')
            ->select('file.cluster_file AS badge_image_file')
            ->leftJoin(SsBadge::tableName() . ' AS badges', "badges.id = badge_products.badge_id AND badges.active = '" . Cms::BOOL_Y . "'")
            ->leftJoin(CmsStorageFile::tableName() . ' AS file', "file.id = badges.image_id_product_card")
            ->andWhere(['product_id' => $this->id])
            ->andWhere(['<=', 'badges.begin_datetime', new Expression('UNIX_TIMESTAMP(NOW())')])
            ->andWhere(['>=', 'badges.end_datetime', new Expression('UNIX_TIMESTAMP(NOW())')])
            ->orderBy('badges.begin_datetime DESC')
            ->limit(1)
            ->column();

        return $file ? \Yii::$app->storage->getCluster('local')->publicBaseUrl . '/' . current($file) : null;
    }

    /**
     * Признак товара из категории "Рос-теста"
     * @return bool
     */
    public function isRostest()
    {

        if (!\common\helpers\User::isDeveloper()) {
            return false;
        }

        $rosTestCategories = array(
            1755, //https://shopandshow.ru/catalog/gadzhity/telefony/mobilnye-telefony/
            1737, //https://shopandshow.ru/catalog/gadzhity/telefony/smartfony/
            1665, //https://shopandshow.ru/catalog/gadzhity/planshety/
            1657, //https://shopandshow.ru/catalog/gadzhity/igry-i-razvlecheniya/ (hasChild = 2)
            1686, //https://shopandshow.ru/catalog/gadzhity/video/
            1763, //https://shopandshow.ru/catalog/gadzhity/video/tv-pristavki/
            1685, //https://shopandshow.ru/catalog/gadzhity/audio/
            1758, //https://shopandshow.ru/catalog/gadzhity/audio/portativnye-kolonki/
            1759, //https://shopandshow.ru/catalog/gadzhity/audio/sintezatory/
            1670, //https://shopandshow.ru/catalog/gadzhity/portativnaya-elektronika/

            1635, //https://shopandshow.ru/catalog/kukhnya/kukhonnye-izmelchiteli/
            1740, //https://shopandshow.ru/catalog/kukhnya/kukhonnye-izmelchiteli/blendery/
            1741, //https://shopandshow.ru/catalog/kukhnya/kukhonnye-izmelchiteli/myasorubki/
            1679, //https://shopandshow.ru/catalog/kukhnya/chayniki-kofevarki/
            1680, //https://shopandshow.ru/catalog/kukhnya/multivarki-skorovarki/
            1681, //https://shopandshow.ru/catalog/kukhnya/sokovyzhimalki/
            1682, //https://shopandshow.ru/catalog/kukhnya/kukhonnye-pechi/
            1747, //https://shopandshow.ru/catalog/kukhnya/kukhonnye-pechi/aerogrili/
            1746, //https://shopandshow.ru/catalog/kukhnya/kukhonnye-pechi/plity/
            1631, //https://shopandshow.ru/catalog/kukhnya/tekhnika-dlya-kukhni/

            //Красота
            1693, //https://shopandshow.ru/catalog/krasota-i-zdorove/staylery-shchiptsy-dlya-ukladki/
            1641, //https://shopandshow.ru/catalog/krasota-i-zdorove/elektropribory-/

            //Хобби
            1835, //https://shopandshow.ru/catalog/khobbi/shite/shveynyie-mashinyi/
        );

        return in_array($this->contentElement->tree_id, $rosTestCategories);
    }

}