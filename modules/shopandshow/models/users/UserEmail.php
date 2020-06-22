<?php
/**
 * Created by PhpStorm.
 * User: koval
 * Date: 16.04.18
 * Time: 15:46
 */

namespace modules\shopandshow\models\users;

use common\helpers\App;
use common\helpers\ArrayHelper;
use common\helpers\Common;
use common\helpers\User;
use skeeks\cms\components\Cms;
use skeeks\cms\models\CmsUserEmail as SXCmsUserEmail;

/**
 * @property string $source
 * @property string $source_detail
 * @property string $is_valid_site
 * @property string $is_send_coupon_500r
 * @property string $ip
 * @property boolean $approved_rr
 */
class UserEmail extends SXCmsUserEmail
{

    const SOURCE_SITE = 'site';
    const SOURCE_SITE_PHONE = 'site_phone';
    const SOURCE_PHONE = 'phone';
    const SOURCE_KFSS = 'kfss';
    const SOURCE_BITRIX = 'bitrix';
    const SOURCE_MOBILE_APP = 'mobile_app';
    const SOURCE_CPA = 'cpa';
    const SOURCE_RR = 'retail_rocket';

    const SOURCE_DETAIL_ALL = 'all';
    const SOURCE_SECRET_SALE = 'secret_sale';

    /**
     * Ритейл Рокет API
     */
    const SOURCE_DETAIL_RR_API = 'retail_rocket_api';

    /**
     * При оформлении заказа
     */
    const SOURCE_DETAIL_CHECK_ORDER = 'check_order';
    const SOURCE_DETAIL_CHECK_ORDER_MOBILE = 'check_order_mobile';
    const SOURCE_DETAIL_CHECK_ORDER_MOBILE_APP = 'check_order_mobile_app';
    const SOURCE_DETAIL_CPA_KMA = 'cpa_kma';

    /**
     * При регистрации
     */
    const SOURCE_DETAIL_REGISTER_DESKTOP = 'register';
    const SOURCE_DETAIL_LOCK_REGISTER_DESKTOP = 'register_lock';
    const SOURCE_DETAIL_REGISTER_MOBILE = 'register_mobile';
    const SOURCE_DETAIL_REGISTER_MOBILE_APP = 'register_mobile_app';
    const SOURCE_DETAIL_REGISTER_TEST = 'register_test'; //регистрация тестовых пользователей

    /**
     * При редактировании профиля
     */
    const SOURCE_DETAIL_PROFILE = 'update';

    /**
     * 500р на десктопе
     */
    const SOURCE_DETAIL_PROMOCODE_DESKTOP = 'promocode_desktop';
    /**
     * 500р на мобилке
     */
    const SOURCE_DETAIL_PROMOCODE_MOBILE = 'promocode_mobile';
    /**
     * Форма в подвале на десктопе
     */
    const SOURCE_DETAIL_FORM_DESKTOP = 'form_desktop';

    const SOURCE_DETAIL_FORM_SUBSCRIBE = 'form_subscribe';


    /**
     * Поваренок на десктопе
     */
    const SOURCE_DETAIL_RECIPE_DESKTOP = 'recipe_desktop';

    const SOURCE_DETAIL_PROMO_WORD = 'slovo';

    const VALUE_TYPE_EMAIL = 0;
    const VALUE_TYPE_PHONE = 1;


    public static $sourceLabels = [
        'main' => [
            self::SOURCE_SITE => 'Сайт',
            self::SOURCE_SITE_PHONE => 'Телефон сайта',
            self::SOURCE_PHONE => 'Телефон эфира',
            self::SOURCE_KFSS => 'КФСС',
            self::SOURCE_BITRIX => 'Битрикс',
            self::SOURCE_MOBILE_APP => 'Моб.приложение',
        ],
        'detail' => [
            self::SOURCE_DETAIL_ALL => 'Весь сайт', //Служебный элемент (нужен в отчетах)
            self::SOURCE_DETAIL_CHECK_ORDER => 'Оформление заказа',
            self::SOURCE_DETAIL_CHECK_ORDER_MOBILE => 'Оформление заказа (мобильные)',
            self::SOURCE_DETAIL_PROFILE => 'Профиль',
            self::SOURCE_DETAIL_REGISTER_DESKTOP => 'Регистрация (десктоп)',
            self::SOURCE_DETAIL_REGISTER_MOBILE => 'Регистрация (мобильные)',
            self::SOURCE_DETAIL_REGISTER_MOBILE_APP => 'Регистрация (моб.приложение)',
            self::SOURCE_DETAIL_PROMOCODE_DESKTOP => 'Попап 500р (десктоп)',
            self::SOURCE_DETAIL_PROMOCODE_MOBILE => 'Попап 500р (мобильные)',
            self::SOURCE_DETAIL_FORM_DESKTOP => 'Форма в подвале',
            self::SOURCE_DETAIL_RECIPE_DESKTOP => 'Попап рецепты (десктоп)',
            self::SOURCE_DETAIL_LOCK_REGISTER_DESKTOP => 'Рега из закрытого раздела',
            self::SOURCE_DETAIL_FORM_SUBSCRIBE => 'Форма подписки',
        ],
    ];

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['approved_rr'], 'default', 'value' => Common::BOOL_N_INT],
            [['source', 'source_detail'], 'required'],
            [['is_valid_site', 'is_send_coupon_500r', 'ip'], 'string'],
        ]);
    }


    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::rules(), [
            'approved_rr' => 'Подтверждено в Retail Rocket',
            'ip' => 'IP',
            'source' => 'Источник',
            'source_detail' => 'Детельно',
            'is_valid_site' => 'Валидный email (сайт)',
            'is_send_coupon_500r' => 'Запрос на купон500р отправлен',
        ]);
    }

    /**
     * @param $email
     * @param $attributes
     * @return bool
     */
    public static function add($email, $attributes)
    {
        $userEmail = new self();
        $userEmail->value = $email;
        $userEmail->setAttributes($attributes);

        return $userEmail->save();
    }

    /**
     * Добоавление email'ов в базу с проверкой на реальность и валидность
     *
     * @param $email
     * @param array $attributes
     * @return bool
     */
    public static function addToBase($email, $attributes = [])
    {
        //При наличии емайла, добавляем его в базу емайлов
        if ($email && User::isRealEmail($email)) {
            $attributes['source'] = $attributes['source'] ?? self::SOURCE_SITE;
            $attributes['source_detail'] = $attributes['source_detail'] ?? self::SOURCE_DETAIL_REGISTER_DESKTOP;
            $attributes['is_valid_site'] = User::isValidEmail($email) ? Cms::BOOL_Y : Cms::BOOL_N;
            $ip = App::getRealIp();
            if ($ip) {
                $attributes['ip'] = $ip;
            }

            //todo не получает корректный IP
//            $attributes['ip'] = \Yii::$app->request->getUserIP();

            return UserEmail::add($email, $attributes);
        }

        return false;
    }

    public static function getSourceLabel($source)
    {
        return isset(self::$sourceLabels['main'][$source]) ? self::$sourceLabels['main'][$source] : ($source ?: 'НЕ ОПРЕДЕЛЕНО');
    }

    public static function getSourceDetailLabel($source)
    {
        return isset(self::$sourceLabels['detail'][$source]) ? self::$sourceLabels['detail'][$source] : ($source ?: 'НЕ ОПРЕДЕЛЕНО');
    }
}