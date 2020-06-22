<?php

namespace common\models\user\authorizations;


use common\models\user\User;
use skeeks\cms\validators\PhoneValidator;
use common\lists\Bitrix;

/**
 * Class SignupFormByEmail
 * @package skeeks\cms\models\forms
 */
class SignupFormByEmail extends SignupForm
{

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'username' => \Yii::t('skeeks/cms', 'Login'),
            'email' => \Yii::t('skeeks/cms', 'Email'),
            'password' => 'Придумайте пароль',
            'name' => 'Имя',
            'surname' => 'Фамилия',
            'patronymic' => 'Отчество',
            'isSubscribe' => 'Подписаться на рассылку',
            'phone' => 'Телефон',
            'guid' => 'guid',
            'source' => 'Источник',
            'source_detail' => 'Источник детально',
        ];
    }

    public function init()
    {
        parent::init();
        $this->setScenario(User::SCENARIO_RIGISTRATION_EMAIL);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [

            [[
                'name',
//                'surname',
                'email',
//                'phone',
//                'password'
            ], 'required'],

            [['password'], 'required', 'except' => [ //Пароль не обязателен для быстрого заказа и для импорта юзера из битрикса
                User::SCENARIO_RIGISTRATION_FROM_BITRIX,
                User::SCENARIO_RIGISTRATION_FROM_FAST_ORDER
            ]],

//            [['name', 'surname', 'username'], 'string', 'min' => 2, 'max' => 255],
            [['isSubscribe', 'name', 'surname', 'patronymic', 'guid', 'source', 'source_detail'], 'safe'],

            [['bitrix_id'], 'integer'],

            [['username', 'surname', 'patronymic', 'name', 'email', 'password'], 'filter', 'filter' => 'trim'],

            ['email', 'email'],

            ['phone', 'string', 'max' => 64],
            ['phone', PhoneValidator::className(), 'except' => [
                User::SCENARIO_RIGISTRATION_FROM_BITRIX,
                User::SCENARIO_RIGISTRATION_EMAIL,
            ]],
//            ['phone', 'unique', 'targetClass' => \Yii::$app->user->identityClass, 'targetAttribute' => 'phone', 'message' => 'Данный телефон уже был зарегистрирован.'],
            ['phone', 'default', 'value' => null],

            ['username', 'unique', 'targetClass' => \Yii::$app->user->identityClass, 'message' => \Yii::t('skeeks/cms', 'This login is already in use by another user.')],

            //['email', 'unique', 'targetClass' => \Yii::$app->user->identityClass, 'message' => \Yii::t('skeeks/cms', 'This Email is already in use by another user')],

//            ['email', 'validateEmailUnique'],

/*            ['password', 'validatePasswordFromSms', 'except' => [
                User::SCENARIO_RIGISTRATION_FROM_BITRIX,
                User::SCENARIO_RIGISTRATION_FROM_FAST_ORDER
            ]], //При входе через емайл и регу из битрикса отключить эту валидацию*/

            ['password', 'string', 'min' => self::MIN_PASSWORD_LENGTH],

            [['source', 'source_detail'], 'string'],
        ];
    }



}
