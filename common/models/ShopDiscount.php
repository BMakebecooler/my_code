<?php


namespace common\models;


class ShopDiscount extends \common\models\generated\models\ShopDiscount
{
    const PROMO_SHIPPING_ORDER_PRICE = 4990; //Если стоимость заказа меньше этой суммы то цена доставки = PROMO_SHIPPING_PRICE
    const PROMO_SHIPPING_PRICE = 199; //TODO Вынести в настройки
    const DELIVERY_DISCOUNT_SUM = 500; //TODO Вынести в настройки

    /**
     * Получение цены бесплатной доставки
     * @return int
     */
    public static function getFreeDeliveryPrice()
    {
        return Setting::getFreeDeliveryPrice();
    }
}