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

use common\lists\Users as UserList;

class LoginByEmailForm extends LoginFormUsernameOrEmail
{

    use CommonAuthorizations;

    private $_user = false;

    public $email;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['email', 'password'], 'required'],
            ['rememberMe', 'boolean'],
            ['password', 'validatePassword'],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'email' => 'E-mail',
            'phone' => 'Телефон',
            'password' => \Yii::t('skeeks/cms', 'Password'),
            'rememberMe' => \Yii::t('skeeks/cms', 'Remember me'),
        ];
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
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, \Yii::t('skeeks/cms', 'Incorrect username or password.'));
            }
        }
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null|bool
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $user = UserList::findByEmail($this->email);

            if ($user) {
                $this->_user = $user;
            } elseif (false && $bitrixUserId = \Yii::$app->front->getBitrixUserApiAuth([
                'login' => $this->email,
                'password' => $this->password,
            ]) // Больше не ищем по апи битркиса
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
                    'guid' => $bitrixUser['guid'],
                ]);

                if ($user = $registration->signup()) {
                    $this->_user = $user;
                } else {
//                    var_dump($registration->getErrors());
//                    die();
                }
            }
        }

        return $this->_user;
    }

}