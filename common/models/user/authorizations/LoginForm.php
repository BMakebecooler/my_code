<?php
/**
 * Created by PhpStorm.
 * User: ubuntu5
 * Date: 17.03.17
 * Time: 9:29
 */

namespace common\models\user\authorizations;

use common\lists\Bitrix;
use common\models\user\User;
use skeeks\cms\models\forms\LoginFormUsernameOrEmail;

class LoginForm extends LoginFormUsernameOrEmail
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
            'identifier' => 'E-mail',
            'phone' => 'Телефон',
            'password' => \Yii::t('skeeks/cms', 'Password'),
            'rememberMe' => \Yii::t('skeeks/cms', 'Remember me'),
        ];
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null|bool
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $user = User::findByUsernameOrEmail($this->identifier);

            if ($user) {
                $this->_user = $user;

            } elseif (false && $bitrixUserId = \Yii::$app->front->getBitrixUserApiAuth([
                'login' => $this->identifier,
                'password' => $this->password,
            ])
            ) {

                if (isset($bitrixUserId['auth']) && !$bitrixUserId['auth']) {
                    return false;
                }

                $bitrixUser = Bitrix::getUserById($bitrixUserId['user_id']);

                if (!$bitrixUser) {
                    return false;
                }

                $registration = new SignupForm();
                $registration->setScenario(User::SCENARIO_RIGISTRATION_FROM_BITRIX);

                $registration->setAttributes([
                    'email' => $bitrixUser['EMAIL'],
                    'username' => $bitrixUser['LOGIN'],
                    'password' => $this->password,
                    'name' => $bitrixUser['NAME'],
                    'surname' => $bitrixUser['LAST_NAME'],
                    'patronymic' => $bitrixUser['SECOND_NAME'],
                    'isSubscribe' => true,
                    'phone' => $bitrixUser['PERSONAL_PHONE'],
                    'bitrix_id' => $bitrixUser['ID'],
                ]);

                if ($user = $registration->signup()) {
                    $this->_user = $user;
                }
            }
        }

        return $this->_user;
    }

}