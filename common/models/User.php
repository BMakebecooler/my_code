<?php

namespace common\models;


use common\helpers\ArrayHelper;
use common\helpers\Strings;
use common\models\generated\models\CmsUser;
use ignatenkovnikita\arh\interfaces\ModelHistoryInterface;
use modules\shopandshow\models\common\GuidBehavior;
use Yii;
use yii\web\IdentityInterface;

/**
 * @method setGuid($guid) From Guid Behavior
 */

class User extends CmsUser implements IdentityInterface //, ModelHistoryInterface
{
    const ACTIVE_Y = 'Y';

    const KFSS_STATUS_UNKNOWN = 99;
    const KFSS_STATUS_NOT_FOUND = 1;
    const KFSS_STATUS_OK = 0;
    const KFSS_STATUS_BLOCKED = 2;

    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            GuidBehavior::className() => GuidBehavior::className()
        ]);
    }

    public static function find()
    {
        return new query\CmsUserQuery(get_called_class());
    }

    /**
     * Finds an identity by the given ID.
     * @param string|int $id the ID to be looked for
     * @return IdentityInterface the identity object that matches the given ID.
     * Null should be returned if such an identity cannot be found
     * or the identity is not in an active state (disabled, deleted, etc.)
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'active' => self::ACTIVE_Y]);
    }

    /**
     * Finds an identity by the given token.
     * @param mixed $token the token to be looked for
     * @param mixed $type the type of the token. The value of this parameter depends on the implementation.
     * For example, [[\yii\filters\auth\HttpBearerAuth]] will set this parameter to be `yii\filters\auth\HttpBearerAuth`.
     * @return IdentityInterface the identity object that matches the given token.
     * Null should be returned if such an identity cannot be found
     * or the identity is not in an active state (disabled, deleted, etc.)
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        // TODO: Implement findIdentityByAccessToken() method.
    }

    /**
     * Returns an ID that can uniquely identify a user identity.
     * @return string|int an ID that uniquely identifies a user identity.
     */
    public function getId()
    {
        return $this->id;
        // TODO: Implement getId() method.
    }

    /**
     * Returns a key that can be used to check the validity of a given identity ID.
     *
     * The key should be unique for each individual user, and should be persistent
     * so that it can be used to check the validity of the user identity.
     *
     * The space of such keys should be big enough to defeat potential identity attacks.
     *
     * This is required if [[User::enableAutoLogin]] is enabled.
     * @return string a key that is used to check the validity of a given identity ID.
     * @see validateAuthKey()
     */
    public function getAuthKey()
    {
        // TODO: Implement getAuthKey() method.
    }

    /**
     * Validates the given auth key.
     *
     * This is required if [[User::enableAutoLogin]] is enabled.
     * @param string $authKey the given auth key
     * @return bool whether the given auth key is valid.
     * @see getAuthKey()
     */
    public function validateAuthKey($authKey)
    {
        // TODO: Implement validateAuthKey() method.
    }


    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'active' => self::ACTIVE_Y]);
    }

    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    public static function findByIdentity($identity)
    {
        return User::find()
            ->andWhere([
                'OR',
                ['username' => $identity],
                ['email' => $identity],
                ['phone' => $identity],
            ])
            ->one();
    }

    public static function findByPhone($phone)
    {
        return User::find()
            ->andWhere([
                'OR',
                ['phone' => Strings::getPhoneClean($phone)],
                ['phone' => Strings::getPhoneClean($phone, true)],
            ])
            ->one();
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return boolean
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token))
        {
            return false;
        }
        $expire = Yii::$app->cms->passwordResetTokenExpire;
        $parts = explode('_', $token);
        $timestamp = (int) end($parts);
        return $timestamp + $expire >= time();
    }

    /**
     * @throws \yii\base\Exception
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * @deprecated ModelHistoryInterface использовать не будем
     * @param $attribute
     * @param $value
     * @return mixed
     */
    public function formatValue($attribute, $value)
    {
        return $value;
        // TODO: Implement formatValue() method.
    }

    //Убрано так как такой же метод/свойство используются из поведения
//    public function getGuid()
//    {
//        return $this->hasOne(Guid::className(), ['id' => 'guid_id']);
//    }
}