<?php

/**
 * Created by PhpStorm.
 * User: ubuntu5
 * Date: 30.08.17
 * Time: 17:37
 */

namespace common\lists;

use common\helpers\Strings;
use common\helpers\User as UserHelper;
use common\helpers\User;
use common\models\user\User as UserModel;
use common\lists\Users as UserList;
use modules\shopandshow\models\users\ContactDataBitrixUser;
use skeeks\cms\components\Cms;

class Users
{

    /**
     * @param $phone
     * @return UserModel|boolean
     */
    public static function findByPhone($phone)
    {
        //$formatPhone = UserHelper::phoneFormat($phone);
        $formatPhone = Strings::getPhoneClean($phone);

        if (!$formatPhone) {
            return false;
        }

        return UserModel::findOne(['phone' => $formatPhone, 'active' => Cms::BOOL_Y]);
    }

    /**
     * Finds user by email
     * @param $email
     * @return UserModel
     */
    public static function findByEmail($email)
    {
        return UserModel::findOne(['email' => $email, 'active' => Cms::BOOL_Y]);
    }

    /**
     * @param null $phone
     * @param null $email
     * @return array|bool|null|\yii\db\ActiveRecord
     */
    public static function findContactData($phone = null, $email = null)
    {
        if ($phone == null && $email == null) {
            return false;
        }

        $formatPhone = UserHelper::phoneFormat($phone);

        if ($phone && !$formatPhone) {
            return false;
        }

        $contactData = ContactDataBitrixUser::find()->alias('contact');

        if ($phone && $email) {
            $contactData->andWhere('contact.phone = :phone', [':phone' => $formatPhone]);
            $contactData->andWhere('contact.email = :email', [':email' => $email]);
        } elseif ($phone) {
            $contactData->andWhere('contact.phone = :phone', [':phone' => $formatPhone]);
        } elseif ($email) {
            $contactData->andWhere('contact.email = :email', [':email' => $email]);
        }

        return $contactData->one();
    }

    /**
     * Проверить есть ли такой пользователь по телефону
     * @param $phone
     * @return bool
     */
    public static function isUserByPhone($phone)
    {
        return (bool)self::findByPhone($phone);
    }

    /**
     * Проверить есть ли такой пользователь по емайлу
     * @param $email
     * @return bool
     */
    public static function isUserByEmail($email)
    {
        return (bool)UserList::findByEmail($email);
    }

    /**
     * Глобально искать пользователя по телефону
     * @param $phone
     * @return bool
     */
    public static function isUserByPhoneContactData($phone)
    {
        $user = self::isUserByPhone($phone);

        if (!$user) {
            $user = self::findContactData($phone);
        }

        return (bool)$user;
    }

    /**
     * @param $phone
     * @return array|bool|UserModel|ContactDataBitrixUser|null|\yii\db\ActiveRecord
     */
    public static function userByPhoneContactData($phone)
    {
        $user = self::findByPhone($phone);

        if (!$user) {
//            $user = self::findContactData($phone);
        }

        return array_filter([
            'phone' => $user ? $user->phone : null,
            'email' => $user ? $user->email : null,
        ]);
    }

    /**
     * Глобально искать пользователя по емайлу
     * @param $email
     * @return bool
     */
    public static function isUserByEmailContactData($email)
    {
        $user = self::isUserByEmail($email);

        if (!$user) {
//            $user = self::findContactData(null, $email);
        }

        if (!$user) {
//            $user = Bitrix::getUserByLoginOrEmail($email);
        }

        return (bool)$user;
    }

    /**
     * @param $email
     * @return array|bool|UserModel|mixed|null|\yii\db\ActiveRecord
     */
    public static function userByEmailContactData($email)
    {
        $user = self::findByEmail($email);

        if (!$user) {
//            $user = self::findContactData(null, $email);
        }

        if (!$user) {
//            $user = Bitrix::getUserByLoginOrEmail($email);
        }

        return array_filter([
            'phone' => $user ? $user['phone'] : null,
            'email' => $user ? $user['email'] : null,
        ]);
    }
}