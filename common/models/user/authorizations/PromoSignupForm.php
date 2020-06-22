<?php

namespace common\models\user\authorizations;

use common\models\user\User;
use skeeks\cms\validators\PhoneValidator;

/**
 * Class PromoSignupForm
 * @package skeeks\cms\models\forms
 */
class PromoSignupForm extends SignupForm
{


    public function scenarios()
    {
        $scenarios = parent::scenarios();

        $scenarios[User::SCENARIO_RIGISTRATION_FROM_BITRIX] = [
            'name', 'email', 'password', 'username', 'bitrix_id', 'phone', 'guid'
        ];

        $scenarios[User::SCENARIO_RIGISTRATION_FROM_FAST_ORDER] = [
            'name', 'username', 'bitrix_id'
        ];

        return $scenarios;
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
                'phone',
//                'password'
            ], 'required'],
//            [['name', 'surname', 'username'], 'string', 'min' => 2, 'max' => 255],
            [['isSubscribe', 'name', 'surname', 'patronymic', 'guid', 'source', 'source_detail'], 'safe'],

            [['bitrix_id'], 'integer'],

            [['username', 'surname', 'patronymic', 'name', 'email'], 'filter', 'filter' => 'trim'],

            ['email', 'email'],

            ['phone', 'string', 'max' => 64],
            ['phone', PhoneValidator::className()],
//            ['phone', 'unique', 'targetClass' => \Yii::$app->user->identityClass, 'targetAttribute' => 'phone', 'message' => 'Данный телефон уже был зарегистрирован.'],
            ['phone', 'default', 'value' => null],

            ['username', 'unique', 'targetClass' => \Yii::$app->user->identityClass, 'message' => \Yii::t('skeeks/cms', 'This login is already in use by another user.')],

//            ['email', 'unique', 'targetClass' => \Yii::$app->user->identityClass, 'message' => \Yii::t('skeeks/cms', 'This Email is already in use by another user')],

//            ['email', 'validateEmailUnique'],

            ['password', 'validatePasswordFromSms', 'except' => [
                User::SCENARIO_RIGISTRATION_FROM_BITRIX,
                User::SCENARIO_RIGISTRATION_FROM_FAST_ORDER
            ]], //При входе через емайл и регу из битрикса отключить эту валидацию

            ['password', 'string', 'min' => self::MIN_PASSWORD_LENGTH],
        ];
    }

}
