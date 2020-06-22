<?php
/**
 * Created by PhpStorm.
 * User: ubuntu5
 * Date: 17.03.17
 * Time: 9:29
 */

namespace common\models\user\authorizations;


class LoginByPhoneEmailForm extends LoginByPhoneForm
{

    public $email;

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'phone' => 'Телефон',
            'email' => 'email',
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
            [['email'], 'required', 'message' => 'Необходимо заполнить поле!'],
            [['password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }
}