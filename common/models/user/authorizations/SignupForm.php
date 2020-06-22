<?php

namespace common\models\user\authorizations;


use common\helpers\App;
use common\helpers\Msg;
use common\helpers\Strings;
use common\lists\Users;
use common\models\user\User;
use skeeks\cms\components\Cms;
use skeeks\cms\models\forms\SignupForm as SignupFormSx;
use skeeks\cms\validators\PhoneValidator;
use yii\db\Connection;
use yii\helpers\Json;

/**
 * Class SignupForm
 * @package skeeks\cms\models\forms
 */
class SignupForm extends SignupFormSx
{

    use CommonAuthorizations;

    /**
     * Минимальная длина пароля
     */
    const MIN_PASSWORD_LENGTH = 6;

    public $name;
    public $surname;
    public $patronymic;
    public $phone;
    public $email;
    public $isSubscribe = false;
    public $bitrix_id;
    public $rememberMe;

    public $guid;

    public $source;
    public $source_detail;

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'username' => \Yii::t('skeeks/cms', 'Login'),
            'email' => \Yii::t('skeeks/cms', 'Email'),
            'password' => 'Код из СМС',
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


    public function scenarios()
    {
        $scenarios = parent::scenarios();

        $scenarios[User::SCENARIO_RIGISTRATION_FROM_BITRIX] = [
            'name', 'email', 'password', 'username', 'bitrix_id', 'phone', 'guid', 'source', 'source_detail'
        ];

        $scenarios[User::SCENARIO_RIGISTRATION_FROM_FAST_ORDER] = [
            'name', 'username', 'bitrix_id', 'source', 'source_detail'
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
//                'email',
                'phone',
//                'password'
            ], 'required'],

            [['password'], 'required', 'except' => [ //Пароль не обязателен для быстрого заказа и для импорта юзера из битрикса
                User::SCENARIO_RIGISTRATION_FROM_BITRIX,
                User::SCENARIO_RIGISTRATION_FROM_FAST_ORDER
            ]],

//            [['name', 'surname', 'username'], 'string', 'min' => 2, 'max' => 255],
            [['isSubscribe', 'name', 'surname', 'patronymic', 'guid'], 'safe'],

            [['bitrix_id'], 'integer'],

            [['username', 'surname', 'patronymic', 'name', 'email', 'password'], 'filter', 'filter' => 'trim'],

            ['email', 'email'],

            ['phone', 'string', 'max' => 64],
//            ['phone', PhoneValidator::className(), 'except' => [
//                User::SCENARIO_RIGISTRATION_FROM_BITRIX,
//            ]],
            [
                ['phone'], 'filter', 'filter' => function ($phone) {
                return Strings::getPhoneClean($phone);
            },
                'message' => 'Введите корректный номер телефона',
                'except' => [
                    User::SCENARIO_RIGISTRATION_FROM_BITRIX,
                ]
            ],
//            ['phone', 'unique', 'targetClass' => \Yii::$app->user->identityClass, 'targetAttribute' => 'phone', 'message' => 'Данный телефон уже был зарегистрирован.'],
            ['phone', 'default', 'value' => null],

            ['username', 'unique', 'targetClass' => \Yii::$app->user->identityClass, 'message' => \Yii::t('skeeks/cms', 'This login is already in use by another user.')],

            //['email', 'unique', 'targetClass' => \Yii::$app->user->identityClass, 'message' => \Yii::t('skeeks/cms', 'This Email is already in use by another user')],

//            ['email', 'validateEmailUnique'],

            ['password', 'validatePasswordFromSms', 'except' => [
                User::SCENARIO_RIGISTRATION_FROM_BITRIX,
                User::SCENARIO_RIGISTRATION_FROM_FAST_ORDER
            ]], //При входе через емайл и регу из битрикса отключить эту валидацию

            ['password', 'string', 'min' => self::MIN_PASSWORD_LENGTH],

            [['source', 'source_detail'], 'string'],
        ];
    }

    /**
     * Проверяем в базе сайта и базе битрикса
     * @param $attr
     */
    public function validateEmailUnique($attr)
    {
        $user = Users::findByEmail($this->email);
        $bitrixUser = false;

        if ($this->scenario != User::SCENARIO_RIGISTRATION_FROM_BITRIX && ($user || $bitrixUser)) {
            $this->addError($attr, 'Данный email уже зарегистрирован, пожалуйста воспользуйтесь формой 
        <a class="sx-fancybox remind-password remind-password-reg" href="#popup_restore_password" title="">восстановления пароля</a>');
        }
    }


    /**
     * Signs user up.
     * @return User|boolean the saved model or null if saving fails
     */
    public function signup()
    {
        //Новая форма тут: frontend/models/form/SignupForm.php
        if ($this->validate()) {
            //* КФСС - получение/создание пользователя *//

            //Сервер КФСС не бьется с прод-сервера
            //Если пришло из обмена (консоль) - пользователь уже есть в кфсс.ю доверяем ГУИДу
            if (App::isConsoleApplication() && $this->guid) {
                $kfssUserGuid = $this->guid;
//                \Yii::error("ConsoleSignUpUser. GUID: " . var_export($this->guid, true), 'debug');
            }else{
                //Если юзер есть в кфсс, то надо взять ГУИД от него (ну или создать и взять и него)
                $kfssUser = \Yii::$app->kfssLkApiV2->getUserByPhone($this->phone);
                $kfssUserGuid = false;

                //Если пользователь нашелся - то можем использовать данные из этого пользователя
                //Если пользователь не нашелся - то сначала регистрируем его в КФСС и потом только у нас используя полученный ГУИД
                if (isset($kfssUser['code']) && $kfssUser['code'] == \common\models\User::KFSS_STATUS_OK) {
                    $kfssUserGuid = $kfssUser['data']['guid'];
                } elseif (isset($kfssUser['code']) && $kfssUser['code'] == \common\models\User::KFSS_STATUS_NOT_FOUND) {
                    $kfssUser = \Yii::$app->kfssLkApiV2->signup(['phone' => $this->phone]);
                    if (!empty($kfssUser['data']['guid'])) {
                        $kfssUserGuid = $kfssUser['data']['guid'];
                    }
                }
            }

            //* /КФСС - получение/создание пользователя *//

            //Продолжаем только если ГУИД был получен (то бишь все ок)
            if ($kfssUserGuid) {

                /**
                 * @var User $user
                 */
                $userClassName = \Yii::$app->user->identityClass;
                $user = new $userClassName();

//            $password = ($this->password) ?: $user->generatePassword();
                $password = $this->password;

                $user->name = $this->name;
                $user->surname = $this->surname;
                //$user->email = ($this->email) ?: $user->generateEmailByPhone($this->phone);
                $user->email = $this->email;
                $user->phone = Strings::getPhoneClean($this->phone);
                $user->bitrix_id = $this->bitrix_id;
                $user->phone_is_approved = YES_INT;

                $this->scenario ? $user->setScenario($this->scenario) : null;

//                $this->guid ? $user->guid->setGuid($this->guid) : null;
                $user->setGuid($kfssUserGuid);

                $user->source = $this->source;
                $user->source_detail = $this->source_detail;

                $user->generateUsername();

                if ($this->scenario == User::SCENARIO_RIGISTRATION_FROM_BITRIX) {
                    $user->setScenario(User::SCENARIO_NO_SEND_CREATE_USER); //Отключаем отправку пользователя в битрикс
                } else if (in_array($this->scenario, [
                    User::SCENARIO_RIGISTRATION_FROM_FAST_ORDER,
                    User::SCENARIO_RIGISTRATION_EMAIL,
                ])) {
                    $user->phone_is_approved = NO_INT;
                }

                $user->setPassword($password);
                $user->generateAuthKey();


                /**
                 * @var $connection Connection
                 */
                $connection = \Yii::$app->get('db');

                $transaction = $connection->beginTransaction();

                if ($user->save()) {
                    $user->relatedPropertiesModel->setAttribute('LAST_NAME', $this->surname);
                    $user->relatedPropertiesModel->setAttribute('PATRONYMIC', $this->patronymic);
                    $user->relatedPropertiesModel->setAttribute('SUBSCRIBE_TO_NEWSLETTER', (string)$this->isSubscribe);
                    $user->relatedPropertiesModel->setAttribute('secretCode', $password); // ВРЕМЕННО

                    if ($this->scenario == User::SCENARIO_RIGISTRATION_FROM_BITRIX) {
//                    \Yii::$app->shopAndShow->sendUpdateUser($user); //ТОДО ВКЛЮЧИТЬ КОГДА БУДЕМ ЗАЛИВАТЬ
                    } elseif ($this->scenario == self::SCENARIO_DEFAULT) {
//                    \Yii::$app->shopAndShow->sendCreateUser($user); // Включить перед пушем в мастер
                    }

                    if (\Yii::$app->appComponent->isSiteShik()) {
//                    \Yii::$app->shopAndShow->sendCreateUser($user);
                    }

                    if (!$user->relatedPropertiesModel->save()) {
                        /** TODO: надо иметь ввиду критично важные свйоства (пока они тут)
                         * в таком виде - пользователь заведется, но поля не будут прописаны,
                         * контроллер не получит ответ (убрать die не до конца решит проблему)
                         *
                         * Например, если я заведу поле USER_SECRET_KEY и не смогу его сохранить по разным причинам
                         * при регистрации - я должен удалить пользовтеля
                         *
                         */
                        var_dump($user->relatedPropertiesModel->getErrors());

                        $transaction->rollBack();
                        die();
                    }


                    /*                \Yii::$app->mailer->view->theme->pathMap = ArrayHelper::merge(\Yii::$app->mailer->view->theme->pathMap, [
                                        '@app/mail' =>
                                            [
                                                '@skeeks/cms/mail-templates'
                                            ]
                                    ]);

                                    \Yii::$app->mailer->compose('@app/mail/register-by-email', [
                                        'user' => $user,
                                        'password' => $password
                                    ])
                                        ->setFrom([\Yii::$app->cms->adminEmail => \Yii::$app->cms->appName . ''])
                                        ->setTo($user->email)
                                        ->setSubject(\Yii::t('skeeks/cms', 'Sign up at site') . \Yii::$app->cms->appName)
                                        ->send();*/

                    $transaction->commit();

                    return $user;
                } else {

                    $transaction->rollBack();

                    \Yii::error("User register by email error: {$user->username} " . Json::encode($user->getErrors()), __METHOD__);
//                    var_dump($user->getErrors());
                    die();
                }

            }

        } else {
            \Yii::error('Ошибка при реге-11 ' . print_r($this->getErrors(), true) . print_r($this->getAttributes(), true), __METHOD__);
        }

        return false;
    }


}
