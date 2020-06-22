<?php
/**
 * Created by PhpStorm.
 * User: ubuntu5
 * Date: 26.05.17
 * Time: 14:58
 */

namespace modules\shopandshow\models\shop;

use common\helpers\ArrayHelper;
use common\helpers\Strings;
use common\helpers\User;
use Props\NotFoundException;
use skeeks\cms\components\Cms;
use skeeks\cms\shop\models\ShopDelivery;
use skeeks\cms\shop\models\ShopFuser as SXShopFuser;
use skeeks\cms\shop\models\ShopPaySystem;
use skeeks\cms\shop\models\ShopPersonTypeProperty;
use skeeks\modules\cms\money\Money;
use yii\base\InvalidParamException;

/**
 * Class ShopFuser
 * @property ShopBasket[] $shopBasketsAll
 * @property ShopBasket[] $shopBasketsOneClick
 * @property ShopBasket[] $shopBasketsWithoutGifts
 * @property string $phone
 * @property string $external_order_id
 * @property string $pvz_data
 *
 * @property ShopFuserFavorite[] $shopFuserFavorites
 * @property int $haveBasketDiscount
 * @property SsShopFuserDiscount $ssShopFuserDiscount
 * @property ShopPersonType $personType
 * @property int $productsPrice
 * @property int $discountPrice
 * @property int $deliveryPrice
 * @package modules\shopandshow\models\shop
 */
class ShopFuser extends SXShopFuser
{

    const DELIVERY_DAYS_FAST = 2;
    const DELIVERY_DAYS_LONG = 8;

    const DELIVERY_ID_DEFAULT = 5; //курьер

    private $_profile = [];

    public function init()
    {
        parent::init();

        if (!$this->delivery_id){
            $this->delivery_id = self::DELIVERY_ID_DEFAULT;
        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['external_order_id'], 'integer'],
            [['pvz_data'], 'string'], //Сериализованный массив данных
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'external_order_id' => "ID сессионного заказа",
            'pvz_data' => "Данные выбранного пункта выдачи заказов",
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSsShopFuserDiscount()
    {
        return $this->hasOne(SsShopFuserDiscount::className(), ['shop_fuser_id' => 'id']);
    }

    /**
     * Итоговая стоимость корзины с учетом скидок, то что будет платить человек
     *
     * @param int $type - тип корзины
     *
     * @return int
     */
    public function getMoney($type = ShopBasket::TYPE_DEFAULT)
    {
//        $money = \Yii::$app->money->newMoney();
        $money = 0;

        $shopBaskets = $type == ShopBasket::TYPE_ONE_CLICK ? $this->shopBasketsOneClick : $this->getShopBaskets()->all();

        foreach ($shopBaskets as $shopBasket) {
//            $money = $money->add($shopBasket->money->multiply($shopBasket->quantity));
            $money += round($shopBasket->price * $shopBasket->quantity, 0);
        }

        //Без кфсс цену доставки не знаем (
//        if ($this->moneyDelivery) {
//            $money = $money->add($this->moneyDelivery);
//        }

        /**
         * Отнимаем скидки корзины
         */
        if ($this->ssShopFuserDiscount) {
//            $fixedMoney = Strings::getMoney($this->ssShopFuserDiscount->discount_price);
            $fixedMoney = $this->ssShopFuserDiscount->discount_price;
            $money -= $fixedMoney;
        }

        return $money;
    }

    public function getProductsPrice($type = ShopBasket::TYPE_DEFAULT)
    {
        $money = 0;
        $shopBaskets = $type == ShopBasket::TYPE_ONE_CLICK ? $this->shopBasketsOneClick : $this->getShopBaskets()->all();

        foreach ($shopBaskets as $shopBasket) {
            $money += round($shopBasket->price * $shopBasket->quantity, 0);
        }

        return $money;
    }

    public function getDiscountPrice($type = ShopBasket::TYPE_DEFAULT)
    {
        $money = 0;

        //* Скидки в товарах *//
        $shopBaskets = $type == ShopBasket::TYPE_ONE_CLICK ? $this->shopBasketsOneClick : $this->getShopBaskets()->all();
        foreach ($shopBaskets as $shopBasket) {
            $money += round($shopBasket->discount_price * $shopBasket->quantity, 0);
        }

        //* /Скидки в товарах *//

        //* Скидки на корзину вцелом *//

        if ($shopFuserDiscount = $this->ssShopFuserDiscount) {
            if ($shopFuserDiscount->discount_price) {
                $money += $shopFuserDiscount->discount_price;
            }
        }

        //* /Скидки на корзину вцелом *//

        return $money;
    }

    public function getDeliveryPrice()
    {
        //Актуальная (из кфсс) цена доставки для фузера хранится в сессии
        //Если ее там нет - можем попробовать определить ее по мере возможности своими силами (промо, БД или не БД)
        $deliveryPriceRemote = null;
        if ($deliveryCacheKey = \Yii::$app->kfssApiV3->_deliveryCacheKey()) {
            $deliveryPriceRemote = \Yii::$app->session->get($deliveryCacheKey);
        }
        
        if ($deliveryPriceRemote === null) {
            $freeDeliveryPrice = \common\models\ShopDiscount::getFreeDeliveryPrice();
            if (\common\helpers\Promo::isPromoShippingPeriod() && $this->productsPrice < $freeDeliveryPrice && $this->productsPrice >= 1000) {
                $deliveryPrice = \common\models\ShopDiscount::PROMO_SHIPPING_PRICE;
            }elseif ($this->productsPrice >= $freeDeliveryPrice){
                $deliveryPrice = 0;
            }else{
                $deliveryPrice = '';
            }
        }else{
            $deliveryPrice = $deliveryPriceRemote;
        }

        return $deliveryPrice;
    }

    /**
     * Итоговая скидка по всей корзине
     * return Money
     * @return int
     */
    public function getMoneyDiscount()
    {
//        $money = \Yii::$app->money->newMoney();
        $money = 0;

        foreach ($this->shopBaskets as $shopBasket) {
//            $money = $money->add($shopBasket->moneyDiscount->multiply($shopBasket->quantity));
            $money += (int)$shopBasket->discount_price;
        }

        if ($this->ssShopFuserDiscount) {
//            $fixedMoney = Strings::getMoney($this->ssShopFuserDiscount->discount_price);
            $fixedMoney = (int)$this->ssShopFuserDiscount->discount_price;
//            $money = $money->add($fixedMoney);
            $money += $fixedMoney;
        }

        return $money;
    }


    /**
     * @return ShopDiscountCoupon[]
     */
    public function getDiscountCoupons()
    {
        if (!$this->discount_coupons) {
            return [];
        }

        return ShopDiscountCoupon::find()->where(['id' => $this->discount_coupons])->all();
    }

    /**
     * проверяет, есть ли скидка на товары в корзине
     * @return int
     */
    public function getHaveBasketDiscount()
    {
        return $this->getShopBaskets()->andWhere('discount_price > 0')->count();
    }

    /**
     * Вернуть активные товары без подарков
     * @return \yii\db\ActiveQuery
     */
    public function getShopBasketsWithoutGifts()
    {
        return $this->getShopBaskets()->joinWith(
            [
                'shopBasketProps p' => function (\yii\db\ActiveQuery $query) {
                    $query->andWhere('IFNULL(p.code, "null") != "' . ShopBasket::GIFT_CODE . '"');
                }
            ]
        );
    }

    /**
     * Вернуть только активные товары в корзине
     * @return \yii\db\ActiveQuery
     */
    public function getShopBaskets()
    {
        return \Yii::$app->db->useMaster(function () {
            return $this->getShopBasketsAll()
                ->joinWith([
//                    'product.cmsContentElement',
                    'product',
                ])
                ->andWhere(['has_removed' => ShopBasket::HAS_REMOVED_FALSE]);
        });
    }

    /**
     * Вернуть все товары в корзине
     * @return \yii\db\ActiveQuery
     */
    public function getShopBasketsAll()
    {
        return $this->hasMany(\common\models\ShopBasket::className(), ['fuser_id' => 'id'])->inverseOf('fuser');
    }

    /**
     * Вернуть товары в корзине через 1 клик
     * @return \yii\db\ActiveQuery
     */
    public function getShopBasketsOneClick()
    {
        return $this->getShopBaskets()->andWhere(['type' => ShopBasket::TYPE_ONE_CLICK]);
    }

    public function getShopFuserFavorites()
    {
        return $this->hasMany(ShopFuserFavorite::className(), ['shop_fuser_id' => 'id'])->inverseOf('shopFuser');
    }

    /**
     * Сумма экономии ( включая полную стоимость подарка )
     * @return int
     */
    public function thrift()
    {
        $sql = <<<SQL
SELECT SUM(t.thrift)
FROM (
    SELECT 
        CASE WHEN stp.def = 'Y' THEN 0
                ELSE 
                 (pr.max_price - pr.price) * b.quantity
                END  AS thrift 
    FROM shop_basket AS b
    INNER JOIN ss_shop_product_prices AS pr ON pr.product_id = b.product_id
    INNER JOIN shop_type_price AS stp ON stp.id = pr.type_price_id
    LEFT JOIN shop_basket_props AS p ON p.shop_basket_id = b.id
    WHERE fuser_id = :f_user AND b.has_removed = :has_removed
) AS t
SQL;

        $discountPrice = \Yii::$app->db->createCommand($sql, [
            ':f_user' => \Yii::$app->shop->shopFuser->id,
            ':has_removed' => ShopBasket::HAS_REMOVED_FALSE,
        ])->queryScalar();

        /**
         * Прибавляем скидки корзины
         */
        if ($this->ssShopFuserDiscount) {
            $discountPrice += (int)$this->ssShopFuserDiscount->discount_price;
        }

        return $discountPrice;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPersonType()
    {
        return $this->hasOne(ShopPersonType::className(), ['id' => 'person_type_id']);
    }


    /**
     * Создать модель покупателя
     * @return ShopBuyer
     * @throws InvalidParamException
     */
    public function createModelShopBuyer()
    {
        if ($this->isNewRecord) {
            throw new InvalidParamException;
        }

        $personType = $this->personType;

        $userId = $this->user_id ?: User::getAuthorizeId();

        if ($userId) {
            /** @var ShopBuyer $shopBuyer */
            $shopBuyer = ShopBuyer::find()
                ->andWhere(['cms_user_id' => $userId])
                ->andWhere(['shop_person_type_id' => $personType->id])
                ->one();

            if ($shopBuyer) {
                return $shopBuyer;
            }
        }

        return new ShopBuyer([
            'shop_person_type_id' => (int)$personType->id
        ]);
    }

    /**
     * Добавить избранные товары этому пользователю
     *
     * @param ShopFuserFavorite[] $favorites
     * @return $this
     */
    public function addFavorites($favorites = [])
    {
        /**
         * @var $currentFavorite ShopFuserFavorite
         */
        foreach ($favorites as $favorite) {
            // Если у пользователя уже есть такой товар в избранном, то просто удаляем старое избранное
            if ($currentFavorite = $this->getShopFuserFavorites()->andWhere(['shop_product_id' => $favorite->shop_product_id])->one()) {
                $favorite->delete();
                if ($currentFavorite->active == Cms::BOOL_N) {
                    $currentFavorite->active = Cms::BOOL_Y;
                    $currentFavorite->save();
                }
            } else {
                $favorite->shop_fuser_id = $this->id;
                $favorite->save();
            }
        }

        return $this;
    }

    /**
     *
     * При пересчете корзины пересчитываем акции
     * @param $type - тип корзины
     * @return $this
     */
    public function recalculate($type = \common\models\ShopBasket::TYPE_DEFAULT)
    {
        $fUser = \Yii::$app->shop->shopFuser;

//        if (!\Yii::$app->kfssApiV2->isDisable || \Yii::$app->kfssApiV2->forcedUseOnly){
//            \Yii::$app->kfssApiV2->recalculateFuser();
//            return $this;
//        }

        //Потоварные скидки
        /** @var \common\models\ShopBasket[] $baskets */
        if ($baskets = $fUser->getShopBaskets()->andWhere(['type' => $type])->all()) {
            foreach ($baskets as $shopBasket) {
                $shopBasket->recalculate()->save();
            }
        }

        return $this;
    }

    /**
     * Получить вес товаров из корзины
     * @return float
     */
    public function getWeightProducts()
    {
        return 0; //На стороне сайта такая проверка более не требуется, все считается по АПИ

        $sql = <<<SQL
    SELECT SUM(IFNULL(pr.value * b.quantity, 0)) AS weight
    FROM shop_basket AS b
    LEFT JOIN cms_content_element AS cce ON cce.id = b.product_id
    LEFT JOIN cms_content_element_property AS pr ON pr.element_id IN (cce.parent_content_element_id, b.product_id)
       AND pr.property_id = (SELECT p.id FROM cms_content_property AS p WHERE p.code = 'VES_PREDVARIT')
       AND pr.value != ''
    WHERE b.fuser_id = :f_user AND b.has_removed = :has_removed
SQL;

        $weight = \Yii::$app->db->createCommand($sql, [
            ':f_user' => \Yii::$app->shop->shopFuser->id,
            ':has_removed' => ShopBasket::HAS_REMOVED_FALSE,
        ])->queryScalar();

        return (float)$weight;
    }

    /**
     * Проверяет, есть ли в корзине лоты, которых нет на складе, и возвращает их кол-во
     * @return int
     * @throws \yii\db\Exception
     */
    public function hasItemsWithNoRest()
    {
        $sql = <<<SQL
    SELECT count(*)
    FROM shop_basket AS b
    LEFT JOIN cms_content_element_property AS pr ON pr.element_id = b.product_id
       AND pr.property_id IN (SELECT p.id FROM cms_content_property AS p WHERE p.code = 'REST')
    WHERE b.fuser_id = :f_user AND b.has_removed = :has_removed AND ifnull(pr.value, 0) <= 0
SQL;

        $amount = \Yii::$app->db->createCommand($sql, [
            ':f_user' => \Yii::$app->shop->shopFuser->id,
            ':has_removed' => ShopBasket::HAS_REMOVED_FALSE,
        ])->queryScalar();

        return (int)$amount;
    }

    /**
     * @param $phone
     * @return bool
     */
    public function savePhone($phone)
    {
        return $this->updateAttributes(['phone' => Strings::getPhoneClean($phone)]);
    }

    /**
     * Считает кол-во дней на отгрузку с нашей стороны с учетом наличия товара на складе
     * @return int
     */
    public function getDeliveryDays()
    {
        // если хотя бы одного товара нет на складе, то доставка 14 дней (ждем отгрузки от байеров)
        return $this->hasItemsWithNoRest() > 0 ? self::DELIVERY_DAYS_LONG : self::DELIVERY_DAYS_FAST;
    }

    /**
     * получает атрибуты профиля пользователя
     * @return mixed|null
     */
    public function getProfileParams()
    {
        //if (!$this->_profile) {
        if (true) { //Если сохранили именения в профиле то последующая выборка в данном хите без явной выборки вернет значения до обновления, выбираем  явно всегда
            //$shopBuyer = $this->buyer;
            $shopBuyer = null;

            if (!$shopBuyer) {
                //Для гостя при первичной инициализации не выходит искать по юзеру, проблемы в корзине
                if ($this->buyer_id) {
                    $shopBuyer = ShopBuyer::findOne($this->buyer_id);
                }elseif ($this->user_id){
                    $shopBuyer = ShopBuyer::findOne(['cms_user_id' => $this->user_id]);
                }
            }

            if ($shopBuyer) {
                $this->_profile = $shopBuyer->relatedPropertiesModel->getAttributes();
            } else {
                $this->_profile = ShopPersonTypeProperty::find()->where(['shop_person_type_id' => 1])->asArray()->all();
                $this->_profile = ArrayHelper::map($this->_profile, 'code', null);

                $geoobject = \Yii::$app->dadataSuggest->getAddress();

                $this->_profile['kladr_id'] = @$geoobject['data']['kladr_id'] ?: '7700000000000';
                $this->_profile['Region'] = @$geoobject['data']['region_with_type'] ?: 'Москва';
                $this->_profile['region_kladr_id'] = @$geoobject['data']['region_kladr_id'] ?: '';
                //$this->_profile['city'] = $this->getProfileCity();
//                $this->_profile['city'] = @$geoobject->data['city'] ?: @$geoobject->data['settlement'] ?: 'г Москва';

                $this->_profile['city'] = @$geoobject['data']['city_with_type'] ?: 'г Москва';

                if (!empty(@$geoobject['data']['settlement_with_type'])){
                    $this->_profile['city'] .= (!empty($this->_profile['city']) ? ', ':'') . @$geoobject['data']['settlement_with_type'];
                }

                //$profileParams['StreetName'] = @$geoobject['data']['street']; //Название улицы
                $this->_profile['StreetName'] = @$geoobject['data']['street_with_type']; //Название улицы
                $this->_profile['StreetNumber'] = \common\helpers\Order::getDeliveryAddressHouseFull($geoobject['data']); //Номер дома
                $this->_profile['BuildNumber'] = @$geoobject['data']['block']; //Номер строения
                $this->_profile['DoorNumber'] = @$geoobject['data']['flat'];

                $this->_profile['FiasCodeCity'] = @$geoobject['data']['city_kladr_id'];
                $this->_profile['FiasCodeStreet'] = @$geoobject['data']['street_kladr_id'];
                $this->_profile['FiasCodeBuilding'] = @$geoobject['data']['house_kladr_id'];

                $this->_profile['postal_code'] = @$geoobject['data']['postal_code'];

            }
        }

        return $this->_profile;
    }

    /**
     * получает значение параметра профиля
     * @param $param
     * @return null
     */
    public function getProfileParam($param)
    {
        $params = $this->getProfileParams();

        return $params[$param] ?? null;
    }

    /**
     * получает адрес пользователя
     * @return mixed|null
     */
    public function getProfileCity()
    {
        $city = $this->getProfileParam('city');

        if (!$city && (\Yii::$app instanceof \yii\web\Application)) {
            $address = \Yii::$app->dadataSuggest->address;
            $city = @$address->unrestricted_value ?: @$address->data['city'] ?: @$address->data['settlement'] ?: 'г Москва';
        }

        return $city;
    }

    public function getCityFromSession()
    {
        $city = \Yii::$app->dadataSuggest->getAddress();

        if ($city->unrestrictedValue) {
            return $city->unrestrictedValue;
        }

        return 'Москва';

    }

    /**
     * получает адрес пользователя
     * @return mixed|null
     */
    public function getProfileAddress()
    {
        return $this->getProfileParam('address');
    }

    /**
     * получает адрес пользователя
     * @return mixed|null
     */
    public function getProfilePostalCode()
    {
        return $this->getProfileParam('postal_code');
    }

    /**
     * сохраняет поля в профиле пользователя
     * @param array $params
     *
     * @return bool|array
     */
    public function saveProfileParams($params = [])
    {
        $buyer = $this->getBuyer()->one();

        if (!$buyer && User::isAuthorize()) {
            $user = \Yii::$app->user->identity;

            $buyer = new ShopBuyer([
                'shop_person_type_id' => 1, //1 - физ лицо. Из-за обновления моделей не используем констарнты из старых
                'cms_user_id' => $user->id
            ]);
            $buyer->save();
            $this->buyer_id = $buyer->id;
            $this->save();
        }
        if (!$buyer) {
            \Yii::error("[saveProfileParams] fUser #{$this->id} Has No Buyer");
            return false;
        }

        $buyer->relatedPropertiesModel->setAttributes($params);

        if (!$buyer->relatedPropertiesModel->save(false, array_keys($params))) {
            return $buyer->relatedPropertiesModel->getErrors();
        }

        return true;
    }

    public function setDelivery($deliveryId)
    {
        $deliveryService = ShopDelivery::findOne($deliveryId);
        if (!$deliveryService) {
            \Yii::error("Не найден способ доставки #{$deliveryId}");
            return false;
        }

        \Yii::$app->shop->shopFuser->delivery_id = $deliveryId;

        if (\Yii::$app->shop->shopFuser->save()) {
            $orderKfss = \Yii::$app->kfssApiV2->updateOrder();
            \Yii::$app->kfssApiV2->recalculateOrder($orderKfss);

            return true;
        } else {
            \Yii::error("Ошибка при сохранении способа доставки #{$deliveryId} для fUser=" . \Yii::$app->shop->shopFuser->id, 'fuser.delivery');
            return false;
        }
    }

    public function setPayment($paymentId, $updateRemote = true)
    {
        $paymentService = ShopPaySystem::findOne($paymentId);
        if (!$paymentService) {
            \Yii::error("Не найден способ оплаты #{$paymentId}");
            return false;
        }

        \Yii::$app->shop->shopFuser->pay_system_id = $paymentId;

        if (\Yii::$app->shop->shopFuser->save()) {
            //Пересчет перенесен в метод обновления данных /api/cart/update/
//            if ($updateRemote){
//                $orderKfss = \Yii::$app->kfssApiV2->updateOrder();
//                \Yii::$app->kfssApiV2->recalculateOrder($orderKfss);
//            }

            return true;
        } else {
            \Yii::error("Ошибка при сохранении способа оплаты #{$paymentId} для fUser=" . \Yii::$app->shop->shopFuser->id, 'fuser.payment');
            return false;
        }
    }

    /**
     * Получить данные о сохраненном ПВЗ
     * @return mixed|null
     */
    public function getPvz()
    {
        return $this->pvz_data ? unserialize($this->pvz_data) : null;
    }
}