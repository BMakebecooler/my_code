<?php
/**
 * Created by PhpStorm.
 * User: ubuntu5
 * Date: 07.06.17
 * Time: 20:56
 */

namespace common\helpers;

use common\helpers\User as UserHelper;
use common\models\user\ExpertUser;
use common\models\user\ExpertUserProcessFlag;
use common\models\user\User as UserModel;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use modules\shopandshow\models\shop\forms\QuickOrder;
use yii\base\Exception;

class User
{
    const FLUSH_CACHE_ROLE = 'flush_cache';

    /**
     * Признак разработчика (Пока работает по принципу максимального доступа в админку)
     * @return bool
     */
    public static function isDeveloper()
    {
        return false;
        try {
            $isDeveloper = self::isAuthorize()
                && (
                    (method_exists(\Yii::$app->user->identity, 'isDeveloper') && \Yii::$app->user->identity->isDeveloper())
                    || self::isTester()
                );
        } catch (Exception $exception) {
            $isDeveloper = false;
        }

        return $isDeveloper;
    }

    /**
     * Признак разработчика для условий js
     * @return bool
     */
    public static function isDeveloperJs()
    {
        return self::isDeveloper() ? 1 : 0;
    }

    /**
     * Признак тестера
     * @return bool
     */
    public static function isTester()
    {
        return self::isAuthorize() && method_exists(\Yii::$app->user->identity, 'isTesting') && \Yii::$app->user->identity->isTesting();
    }

    /**
     * Признак что юзер - демо юзер
     * @return bool
     */
    public static function isDemo()
    {
        return self::isAuthorize() && method_exists(\Yii::$app->user->identity, 'isDemo') && \Yii::$app->user->identity->isDemo();
    }

    /**
     * Признак редактора
     * @return bool
     */
    public static function isEditor()
    {
        return self::isAuthorize() && \Yii::$app->user->identity->isEditor();
    }

    /**
     * Признак баера
     * @return bool
     */
    public static function isBuyer()
    {
        return self::isAuthorize() && \Yii::$app->user->identity->isBuyer();
    }

    /**
     * Признак сервисника
     * @return bool
     */
    public static function isService()
    {
        return self::isAuthorize() && \Yii::$app->user->identity->isService();
    }

    /**
     * Признак копирайтера
     * @return bool
     */
    public static function isCopyright()
    {
        return self::isAuthorize() && \Yii::$app->user->identity->isCopyright();
    }

    /**
     * Признак не авторизованного пользователя
     * @return bool
     */
    public static function isGuest()
    {
        return \Yii::$app->user->isGuest;
    }

    /**
     *
     * @return int|null
     */
    public static function getSessionId()
    {
        $shopFuser = \Yii::$app->shop->shopFuser;

        return ($shopFuser) ? $shopFuser->id : null;
    }

    /**
     * Признак авторизованного пользователя
     * @return bool
     */
    public static function isAuthorize()
    {
        return !self::isGuest();
    }

    /**
     * Признак авторизованного пользователя у которого заполнен емайл
     * @return bool
     */
    public static function isAuthorizeAndEmail()
    {
        if (!self::isAuthorize()) {
            return false;
        }

        $email = \Yii::$app->user->identity->email;

        return $email && self::isRealEmail($email);
    }

    /**
     * Признак не бутафорского емайла
     * @param $email
     * @return bool
     */
    public static function isRealEmail($email)
    {
        return !substr_count($email, UserModel::EMAIL_PREFIX_SITE) && !substr_count($email, UserModel::EMAIL_NOREAL_PART);
    }

    /**
     * Признак "живого" имени
     * @param $name
     * @return bool
     */
    public static function isRealName($name)
    {
        if (!QuickOrder::DEFAULT_USER_NAME) {
            return false;
        }
        return !substr_count($name, QuickOrder::DEFAULT_USER_NAME);
    }

    /**
     * Признак валидности емайла
     * @param $email
     * @return bool
     */
    public static function isValidEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Определение присутствия номера телефона в списке телефонов опытных пользователей
     * @param $phone
     * @return bool
     */
    public static function isSkilled($phone)
    {
        //Приводим телефон к формату только цифры, 10 знаков
        $phone = substr(\common\helpers\Strings::onlyInt($phone), -10);
        if (strlen($phone) == 10) {
            $expertUserProcessFlag = ExpertUserProcessFlag::findOne(1);
            if (($expertUserProcessFlag->check_cnt % 2) === 0) { // Каждый второй подходящий пользователь участвует в тестец
                $expertUser = ExpertUser::findOne(['phone' => $phone]);
                if (!empty($expertUser) && !$expertUser->is_processed) {
                    $expertUserProcessFlag->check_cnt = 0;
                    $expertUserProcessFlag->update_time = time();
                    $expertUserProcessFlag->save();
                    $expertUser->is_processed = 1;
                    $expertUser->save();
                    return true;
                }
            }
            $expertUserProcessFlag->check_cnt = $expertUserProcessFlag->check_cnt + 1;
            $expertUserProcessFlag->save();
        }
        return false;
    }


    public static function isBlockedKfssUser($kfssUser)
    {
        return (isset($kfssUser['code']) && $kfssUser['code'] == \common\models\User::KFSS_STATUS_BLOCKED) ? true : false;
    }

    /**
     * Определяет заблокирован ли клиент по его номеру телефона (на удаленном ресурсе)
     *
     * @param $phone
     * @return bool
     */
    public static function isBlockedByPhoneRemote($phone)
    {
        $kfssUser = \Yii::$app->kfssLkApiV2->getUserByPhone($phone);
        return self:: isBlockedKfssUser($kfssUser);
    }

    /**
     * Получить Ид авторизованного пользователя
     * @return int|null
     */
    public static function getAuthorizeId()
    {
        return self::isAuthorize() ? self::getUser()->id : null;
    }

    /**
     * @return UserModel|null
     */
    public static function getUser()
    {
        return self::isAuthorize() ? \Yii::$app->user->identity : null;
    }

    /**
     * Получить номер телефона пользователя
     * @return string
     */
    public static function getPhone()
    {
        return ($user = self::getUser()) ? $user->phone : '';
    }

    /**
     * @param $phone
     * @return bool|string
     */
    public static function phoneFormat($phone)
    {
        if (!$phone) {
            return false;
        }

        $country = 'RU';
        $phoneNumberUtil = PhoneNumberUtil::getInstance();

        try {

            $numberProto = $phoneNumberUtil->parse($phone, $country);

            if ($phoneNumberUtil->isValidNumber($numberProto)) {
                return $phoneNumberUtil->format($numberProto, PhoneNumberFormat::INTERNATIONAL);
            }

        } catch (NumberParseException $e) {
            return false;
        } catch (Exception $e) {
            return false;
        }

        return false;
    }

    public static function can($permission)
    {
        $user = \Yii::$app->user;
        if ($user && $user->can($permission)) {
            return true;
        }
        return false;
    }

    /**
     * Проверка наличия у пользователя указанной роли, поиск юзера по номеру телефону
     *
     * @param $phone
     * @param $roleName
     * @return bool
     */
    public static function hasRoleByPhone($phone, $roleName)
    {
        $user = \common\lists\Users::findByPhone($phone);
        if ($user) {
            return self::hasRole($user->id, $roleName);
        }
        return false;
    }

    /**
     * Проверка наличия у пользователя указанной роли
     *
     * @param $userId
     * @param $roleName
     * @return bool
     */
    public static function hasRole($userId, $roleName)
    {
        $userRoles = \Yii::$app->authManager->getRolesByUser($userId);
        return isset($userRoles[$roleName]);
    }

    /**
     * Для дебага
     * @return bool
     */
    public static function isDebug()
    {
        return isset($_COOKIE[COOKIE_NAME_IS_SHOW_DEBUG_PANEL]);
    }

    /**
     * проверка, есть ли у пользователя роль на очистку кеша
     *
     * @return bool
     */
    public static function canResetCache()
    {
        $userId = self::getAuthorizeId();
        if ($userId) {
            return self::hasRole($userId, self::FLUSH_CACHE_ROLE);
        } else {
            return false;
        }
    }

    public static function getClientDataForSiteApi()
    {
        $fUser = \Yii::$app->shop->shopFuser;
        $formData = $fUser->additional ? unserialize($fUser->additional) : false;
        //Пока все еще оставляем юзера связанного со  скексом ((
//        if (!$user = UserHelper::getUser()) {
//            $user = new QuickOrder();
//        }

        $user = self::getUser();

        $userName = $user->name ?? '';
        $userSurname = $user->surname ?? '';
        $userPatronymic = $user->patronymic ?? '';
        $userEmail = $user->email ?? '';

        return [
            'name' => $formData['name'] ?? $userName,
            'surname' => $formData['surname'] ?? $userSurname,
            'patronymic' => $formData['patronymic'] ?? $userPatronymic,
            'phone' => $formData['phone'] ?? $fUser['phone'],
            'email' => $formData['email'] ?? $userEmail,
        ];
    }

}