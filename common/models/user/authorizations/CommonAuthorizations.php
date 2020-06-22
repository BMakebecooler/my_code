<?php
/**
 * Created by PhpStorm.
 * User: ubuntu5
 * Date: 31.08.17
 * Time: 21:18
 */

namespace common\models\user\authorizations;

use common\models\user\User as UserModel;
use modules\shopandshow\models\statistic\PassTime;

trait CommonAuthorizations
{


    /**
     * @return bool|mixed
     */
    public function getPasswordFromSms()
    {
        //Проверка сессии
        if (\Yii::$app->getSession()->offsetExists(UserModel::SESSION_NAME_SMS_PASS)) {
            return \Yii::$app->getSession()->get(UserModel::SESSION_NAME_SMS_PASS);
        }

        return false;
    }

    /**
     * Проверить существует ли сессия с паролем с СМС
     * @return bool
     */
    public function checkPasswordFromSms()
    {
        if ($password = $this->getPasswordFromSms()) {
            return $password === $this->password;
        }

        return false;
    }

    /**
     * @return bool|mixed
     */
    public function validatePasswordFromSms($attr)
    {
        //Проверка сессии
        if (!$this->checkPasswordFromSms()) {
            $this->addError($attr, 'Пароль должен совпадать с СМС');
        }
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors() && !$this->checkPasswordFromSms()) {

            $user = $this->getUser();

            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, \Yii::t('skeeks/cms', 'Incorrect username or password.'));
            }
        }
    }

    /**
     * Сохраняет время, потраченное на ввод пароля приавторизации/регистрации после отправки пароля по смс
     *
     * @param string $fUserIdBeforeLogin - fUserId для кого искать запись с учетом времени авторизации (ибо текущий может быть обновленным)
     * @return bool
     */
    public function storeLoginAttempt($fUserIdBeforeLogin = '')
    {
        //Проверка сессии
        if (\Yii::$app->getSession()->offsetExists(UserModel::SESSION_NAME_SMS_TIME)) {
            $timeSmsSent = \Yii::$app->getSession()->get(UserModel::SESSION_NAME_SMS_TIME);
            if (!$timeSmsSent) {
                return true;
            }

            //Уже должна быть запись для данной авторизации/регистрации с записью времени начала ввода пароля
            $passTime = PassTime::find()
                ->andWhere(['not', ['seconds_first_symbol' => null]])
                ->andWhere(['seconds' => null]);

            if ($fUserIdBeforeLogin){
                $passTime->andWhere(['fuser_id' => $fUserIdBeforeLogin]);
            }else{
                $passTime->andWhere(['created_at' => $timeSmsSent]);
            }

            $passTime = $passTime->one();

            if ($passTime){
                $passTime->seconds = time() - $timeSmsSent;
                $passTime->fuser_id = \Yii::$app->shop->shopFuser->id;
                $passTime->save();

                \Yii::$app->getSession()->remove(UserModel::SESSION_NAME_SMS_TIME);
            }else{
                return false;
            }

            return true;
        }

        return false;
    }
}