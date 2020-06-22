<?php
/**
 * Created by PhpStorm.
 * User: ubuntu5
 * Date: 17.03.17
 * Time: 9:29
 */

namespace common\models\user\authorizations;

use common\helpers\Strings;
use common\lists\Bitrix;
use common\models\user\User;
use modules\shopandshow\lists\Guids;
use skeeks\cms\models\forms\LoginFormUsernameOrEmail;
use common\lists\Users as UserList;
use Yii;

class LoginByPhoneForm extends LoginFormUsernameOrEmail
{

    use CommonAuthorizations;

    private $_user = false;

    public $phone;

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'phone' => 'Телефон',
            'password' => \Yii::t('skeeks/cms', 'Password'),
            'rememberMe' => \Yii::t('skeeks/cms', 'Remember me'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['phone'], 'required', 'message' => 'Необходимо заполнить поле!'],
            [['password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
            [['phone'], 'filter', 'filter' => function($phone){
                return Strings::getPhoneClean($phone);
            }, 'message' => 'Введите корректный номер телефона'],
        ];
    }

    /**
     * @return boolean whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            $user = $this->getUser();

            if ($user && $this->checkPasswordFromSms()) {
                $user->saveNewPassword($this->getPasswordFromSms());
            }

            if ($user) {
                return Yii::$app->user->login($user, $this->rememberMe ? 3600 * 24 * 30 : 0);
            }
        }

        return false;
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null|bool
     */
    public function getUser()
    {

        if ($this->_user === false && $this->phone) {

            $user = UserList::findByPhone($this->phone);

            if ($user) {
                $this->_user = $user;
            } elseif (false && Yii::$app->appComponent->isSiteSS() && $contactData = UserList::findContactData($this->phone)) { // Больше не ищем в базе битрикса

                $bitrixUser = Bitrix::getUserById($contactData->id);

                if (!$bitrixUser) {
                    return false;
                };

                /**
                 * Для старых пользователей которые были зарегистрированы (на новом сайте) без обяз телефона
                 * но существовавщих в битриксе по телефону, мы просто обновляем их телефон на новом сайте
                 */
                if ($userByGuid = Guids::getEntityByGuid($bitrixUser['guid'])) {

                    $userByGuid->setAttributes([
                        'phone' => $this->phone,
                    ]);

                    $userByGuid->setPassword($this->password);

                    return $userByGuid->save();

                } else {

                    $registration = new SignupForm();

                    $registration->setScenario(User::SCENARIO_RIGISTRATION_FROM_BITRIX);

                    $registration->setAttributes([
                        'email' => $bitrixUser['EMAIL'],
                        'username' => 'ns_' . $bitrixUser['LOGIN'],
                        'password' => $this->password,
                        'phone' => $this->phone,
                        'name' => $bitrixUser['NAME'] ?: 'Не задано',
                        'surname' => $bitrixUser['LAST_NAME'] ?: 'Не задано',
                        'patronymic' => $bitrixUser['SECOND_NAME'],
                        'isSubscribe' => true,
                        'bitrix_id' => $bitrixUser['ID'],
                        'guid' => $bitrixUser['guid'],
                    ]);

                    if ($user = $registration->signup()) {
                        $this->_user = $user;
                    }
                }
            }
        }

        return $this->_user;
    }

}