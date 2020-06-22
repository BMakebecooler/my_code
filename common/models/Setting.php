<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 2019-04-03
 * Time: 11:45
 */

namespace common\models;


class Setting extends \common\models\generated\models\Setting
{

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'id' => 'ID',
            'free_delivery_price' => 'Сумма корзины для бесплатной доставки',
            'phone_code' => 'Код телефона сайта',
            'phone_number' => 'Номер телефона сайта',
            'robots' => 'Содержимое файла Robots.txt',
            'is_999' => 'Режим 999',
            'is_show_product_feedback' => 'Показывать фидбек в товаре (ФАК/Отзывы)',
            'is_online_payment_allowed' => 'Разрешить оплату картами на сайте',
            'onair_youtube_code' => 'Код Youtube прямого эфира',
            'onair_product_id' => 'Id товара, который в эфире в данный момент',
            'use_captcha' => 'Использовать капчу',
            'use_price_prime' => 'Использовать Цену PRIME на сайте',
            'use_filters' => 'Использовать фильтры',
            'count_brands_products' => 'Считать кол-во товаров брендов',
        ]);
    }

    public static function getFreeDeliveryPrice()
    {
        return self::getValueByAttreibute('free_delivery_price');
    }

    public static function getPhoneCode()
    {
        return self::getValueByAttreibute('phone_code');
    }

    public static function getPhoneNumber()
    {
        return self::getValueByAttreibute('phone_number');
    }

    public static function getRobots()
    {
        return self::getValueByAttreibute('robots');
    }

    public static function getIs999()
    {
        return (bool)self::getValueByAttreibute('is_999');
    }

    public static function isShowProductFeedback()
    {
        return (bool)self::getValueByAttreibute('is_show_product_feedback');
    }

    public static function isOnlinePaymentAllowed()
    {
        return (bool)self::getValueByAttreibute('is_online_payment_allowed');
    }

    public static function getOnairYoutubeCode()
    {
        return self::getValueByAttreibute('onair_youtube_code');
    }

    public static function getOnairProductId()
    {
        return self::getValueByAttreibute('onair_product_id');
    }

    public static function getUseCaptcha()
    {
        return (bool)self::getValueByAttreibute('use_captcha');
    }

    public static function getUsePricePrime()
    {
        return (bool)self::getValueByAttreibute('use_price_prime');
    }

    public static function getUseFilters()
    {
        return (bool)self::getValueByAttreibute('use_filters');
    }

    public static function countBrandsProducts()
    {
        return (bool)self::getValueByAttreibute('count_brands_products');
    }

    private static function getValueByAttreibute($name)
    {
        $value = 0;
        $setting = Setting::findOne(1);
        if ($setting) {
            $value = $setting->$name;
        }

        return $value;
    }
}