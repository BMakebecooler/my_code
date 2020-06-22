<?php

namespace common\models\user;

use common\components\rbac\CmsManager;
use common\helpers\ArrayHelper;
use common\helpers\Strings;
use common\helpers\User as UserHelper;
use common\lists\Users;
use modules\shopandshow\components\task\SendRrEmailTaskHandler;
use modules\shopandshow\models\common\GuidBehavior;
use modules\shopandshow\models\shop\ShopFuserFavorite;
use modules\shopandshow\models\task\SsTask;
use modules\shopandshow\models\users\UserEmail;
use skeeks\cms\components\Cms;
use skeeks\cms\models\CmsUser;
use skeeks\cms\relatedProperties\models\RelatedElementPropertyModel;
use skeeks\cms\relatedProperties\models\RelatedPropertiesModel;
use skeeks\cms\relatedProperties\models\RelatedPropertyModel;
use skeeks\cms\validators\PhoneValidator;

/**
 *
 * @property string $surname
 * @property string $patronymic
 * @property int $bitrix_id
 * @property int $guid_id
 * @property int $source
 * @property int $source_detail
 * @property GuidBehavior $guid
 * @method RelatedPropertiesModel getRelatedPropertiesModel()
 * @property RelatedElementPropertyModel[] relatedElementProperties
 * @property RelatedPropertyModel[] relatedProperties
 * @property RelatedPropertiesModel relatedPropertiesModel
 * @method setGuid($guid) From Guid Behavior
 */
class User extends CmsUser
{

    /**
     * Для того чтобы не отправлять созданного юзера в битрикс
     */
    const SCENARIO_NO_SEND_CREATE_USER = 'no_send_create_user';

    /**
     * Сценарий для быстрого заказа в котором поле телефон будет в свободном формате
     */
    const SCENARIO_RIGISTRATION_FROM_FAST_ORDER = 'fast_order';

    /**
     * Сценарий для битриксового импорта где телефоны проверять не надо
     */
    const SCENARIO_RIGISTRATION_FROM_BITRIX = 'bitrix';

    /**
     * Сценарий для регистрации по емайлу
     */
    const SCENARIO_RIGISTRATION_EMAIL = 'email';

    /**
     * Название сессии где хранится пароль с смс
     */
    const SESSION_NAME_SMS_PASS = 'SMS_PASS';

    /**
     * Название сессии где хранится время отправки смс
     */
    const SESSION_NAME_SMS_TIME = 'SMS_TIME';

    /**
     * Префикс для генерации емайла, когда его нет
     */
    const EMAIL_PREFIX_SITE = 'newsite_';

    /**
     * Часть мыла генерируемого внешними системами если у клиента мыла нет
     */
    const EMAIL_NOREAL_PART = '@no.reg';

    /**
     * Фамилия
     * @var
     */
    public $surname;
    public $patronymic;

    public $open_password;

    public function init()
    {
        parent::init();

        $this->on(self::EVENT_AFTER_INSERT, [$this, "_processAfterInsert"]);
        $this->on(self::EVENT_AFTER_UPDATE, [$this, "_processAfterSave"]);
    }

    public function setPassword($password)
    {
        $this->open_password = $password;
        $this->password_hash = \Yii::$app->security->generatePasswordHash($password);
    }

    public function _processAfterInsert($e)
    {
        if ($this->scenario !== self::SCENARIO_NO_SEND_CREATE_USER) {
            try {
//                \Yii::$app->shopAndShow->sendUpdateUser($this);
                $this->sendRegistrationEmail(); //Отправляем письмо о реге
            } catch (\Exception $e) {

            }
        } else {
            //Данный сценарий используется при импорте заказов сайта с телефона (aka из bitrix)
            $this->addEmailBase(UserEmail::SOURCE_SITE_PHONE, UserEmail::SOURCE_DETAIL_CHECK_ORDER);
        }

        if ($this->scenario === self::SCENARIO_RIGISTRATION_FROM_BITRIX) {
            $this->addEmailBase(UserEmail::SOURCE_BITRIX);
        } elseif (in_array($this->scenario, [self::SCENARIO_DEFAULT, self::SCENARIO_RIGISTRATION_EMAIL])) {
            $this->addEmailBase($this->source ?: null, $this->source_detail ?: null);

            if (!$this->source && !$this->source_detail) {
                \Yii::error("Создание пользователя, источник не определен!\n" . var_export($this, true));
            }
        } elseif ($this->scenario == self::SCENARIO_RIGISTRATION_FROM_FAST_ORDER) {

        } elseif ($this->scenario !== self::SCENARIO_NO_SEND_CREATE_USER) {
            \Yii::error("Создание пользователя, не учитываемый сценарий '{$this->scenario}' [{$this->source} / {$this->source_detail}]!\n");
        }

        //* Отправка приветственного письма пользователю при регистраии *//

        //Только для пользователей регистрируемых непосредственно на сайте

        if (
            $this->scenario === self::SCENARIO_DEFAULT
            && !empty($this->email)
            && UserHelper::isRealEmail($this->email)
            && UserHelper::isValidEmail($this->email)
        ) {
            SsTask::createNewTask(
                SendRrEmailTaskHandler::className(),
                [
                    'email' => $this->email,
                    'template' => 'welcome'
                ]
            );
        }

        //* /Отправка приветственного письма пользователю при регистраии *//
    }

    public function _processAfterSave($e)
    {
        if ($this->isAttributeChanged('email') && $this->email) {
            $this->addEmailBase(UserEmail::SOURCE_SITE, UserEmail::SOURCE_DETAIL_PROFILE);
        }
    }


    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            GuidBehavior::className() => GuidBehavior::className()
        ]);
    }

    public function afterFind()
    {
        parent::afterFind();

        $this->surname = $this->relatedPropertiesModel->getAttribute('LAST_NAME');
        $this->patronymic = $this->relatedPropertiesModel->getAttribute('PATRONYMIC');
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();

        $scenarios[self::SCENARIO_NO_SEND_CREATE_USER] = [];

        $scenarios[self::SCENARIO_RIGISTRATION_EMAIL] = [
            'name', 'username', 'email'
        ];

        $scenarios[self::SCENARIO_RIGISTRATION_FROM_FAST_ORDER] = [
            'name', 'username', 'bitrix_id'
        ];

        return $scenarios;
    }


    /**
     * Переопределяем стандарнтные правила. потому как в них обязательный логин :-(
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['active', 'default', 'value' => Cms::BOOL_Y],
            ['gender', 'default', 'value' => 'men'],
            ['gender', 'in', 'range' => ['men', 'women']],

            [['created_at', 'updated_at', 'email_is_approved', 'phone_is_approved', 'bitrix_id'], 'integer'],

            [['image_id'], 'safe'],
            [['gender'], 'string'],
            [['username', 'password_hash', 'password_reset_token', 'email', 'name', 'patronymic', 'surname'], 'string', 'max' => 255],
            [['auth_key'], 'string', 'max' => 32],

            [['phone'], 'string', 'max' => 64],
//            [['phone'], PhoneValidator::className()],
//            [['phone'], 'unique'],

            ['phone', 'validatePhoneUnique', 'except' => [
                self::SCENARIO_RIGISTRATION_FROM_BITRIX,
                self::SCENARIO_RIGISTRATION_FROM_FAST_ORDER
            ]], //При реге через битрикс и быстрому заказу отключить эту валидацию


            //[['phone', 'email'], 'default', 'value' => null],
//            [['name', 'phone', 'email'], 'required'],

        //TODO РАСКОМЕНТИРОВАТЬ ПРИ БОЕВОЙ ОПЛАТЕ!

            [['phone', 'name', 'patronymic', 'surname'], 'required', 'when' => function($model){
                //Обязательно только на финише, например на перерасчете оно не обязательно
                return \Yii::$app->request->post('Checkout') && \Yii::$app->request->post('Checkout') == 'finish';
            }],

        //TODO УБРАТЬ ПРИ БОЕВОЙ ОПЛАТЕ

//            [['phone'], 'required', 'when' => function($model){
                //Обязательно только на финише, например на перерасчете оно не обязательно
//                return \Yii::$app->request->post('Checkout') && \Yii::$app->request->post('Checkout') == 'finish';
//            }],

            [['email'], 'filter', 'filter' => 'trim'],
            [['phone'], 'filter', 'filter' => function($phone){
                return Strings::getPhoneClean($phone);
            }],

//            [['email'], 'unique'],
            [['email'], 'email'],

            //[['username'], 'required'],

            ['username', 'string', 'min' => 3, 'max' => 255],
            [['username'], 'unique'],

//            [['username'], \skeeks\cms\validators\LoginValidator::className()],

            [['logged_at'], 'integer'],
            [['last_activity_at'], 'integer'],
            [['last_admin_activity_at'], 'integer'],

            [['username'], 'default', 'value' => function (self $model) {
                $userLast = static::find()->orderBy("id DESC")->one();
                return "id" . ($userLast->id + 1);
            }],

            [['email_is_approved', 'phone_is_approved'], 'default', 'value' => 0],

            [['auth_key'], 'default', 'value' => function (self $model) {
                return \Yii::$app->security->generateRandomString();
            }],

            [['password_hash'], 'default', 'value' => function (self $model) {
                return \Yii::$app->security->generatePasswordHash(\Yii::$app->security->generateRandomString());
            }],

            [['roleNames'], 'safe'],
            [['roleNames'], 'default', 'value' => \Yii::$app->cms->registerRoles],

            [['open_password', 'surname', 'patronymic'], 'safe'],
            [['source', 'source_detail'], 'string']
        ];
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'patronymic'   => 'Отчество',
            'surname'   => 'Фамилия',
        ]);
    }

    /**
     * Проверяем в базе сайта и базе битрикса
     * @param $attr
     */
    public function validatePhoneUnique($attr)
    {
        if ($user = Users::userByPhoneContactData($this->phone) && \Yii::$app->user->isGuest) {
            $this->addError($attr, 'Вы уже зарегистрированы по этому телефону, вы можете восстановить пароль по СМС');
        }
    }

    /**
     * Генерация логина пользователя
     * @return $this
     */
    public function generateUsername()
    {
        /*if ($this->email)
        {
            $userName = \skeeks\cms\helpers\StringHelper::substr($this->email, 0, strpos() );
        }*/

        //$userLast = User::find()->orderBy("id DESC")->one();

        $userName = (($this->name) ? $this->name : '') . (($this->surname) ? '_' . $this->surname : '');

        $value = Strings::translit(trim($userName));

        $this->username = strtolower($value);

        if ($userLast = static::find()->where(['username' => $this->username])->one()) {
            $this->username = $this->username . '_' . uniqid() . '_' . time();
        }

        return $this;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFavorites()
    {
        return ShopFuserFavorite::find()->where(['shop_fuser_id' => \Yii::$app->shop->shopFuser->id]);

        return $this->hasMany(ShopFuserFavorite::className(), ['shop_fuser_id' => 'id']);
        //->andOnCondition(['shop_fuser_id' => \Yii::$app->shop->shopFuser->id]);
    }

    /**
     * @return int|string
     */
    public function getFavoritesCount()
    {
        return $this->getFavorites()->count();
    }


    /**
     * Если юзер - разработчик
     * @return bool
     */
    public function isDeveloper()
    {
        $permission = \Yii::$app->authManager->getPermission(CmsManager::PERMISSION_USER_FULL_EDIT);

        return \Yii::$app->user->can($permission->name);
    }

    /**
     * Если юзер - тестировщик
     * @return bool
     */
    public function isTesting()
    {
        $permission = \Yii::$app->authManager->getRole(CmsManager::ROLE_TEST);

        return \Yii::$app->user->can($permission->name);
    }

    /**
     * Если юзер - демо юзер
     * @return bool
     */
    public function isDemo()
    {
        $permission = \Yii::$app->authManager->getRole(CmsManager::ROLE_DEMO);

        return \Yii::$app->user->can($permission->name);
    }

    /**
     * Если юзер - редактор
     * @return bool
     */
    public function isEditor()
    {
        $permission = \Yii::$app->authManager->getRole(CmsManager::ROLE_EDITOR);

        return \Yii::$app->user->can($permission->name);
    }

    /**
     * Если юзер - баер
     * @return bool
     */
    public function isBuyer()
    {
        $permission = \Yii::$app->authManager->getRole(CmsManager::ROLE_BUYER);

        return \Yii::$app->user->can($permission->name);
    }

    /**
     * Если юзер - сервисник
     * @return bool
     */
    public function isService()
    {
        $permission = \Yii::$app->authManager->getRole(CmsManager::ROLE_SERVICE);

        return \Yii::$app->user->can($permission->name);
    }

    /**
     * Если юзер - копирайтер
     * @return bool
     */
    public function isCopyright()
    {
        $permission = \Yii::$app->authManager->getRole(CmsManager::ROLE_COPYRIGHT);

        return \Yii::$app->user->can($permission->name);
    }

    /**
     * Сгенерировать email по телефону
     * @param $phone
     * @return string
     */
    public function generateEmailByPhone($phone)
    {
        $onlyInt = Strings::onlyInt($phone);

        return sprintf('%s%s@shopandshow.ru', self::EMAIL_PREFIX_SITE, $onlyInt);
    }

    /**
     * Сохранить новый пароль
     * @param $password
     * @return bool
     */
    public function saveNewPassword($password)
    {
        $this->setPassword($password);
        $this->generateAuthKey();

        return $this->save();
    }

    /**
     * Сгенерировать пароль
     * @param int $length
     * @return string
     */
    public static function generatePassword($length = 6)
    {
        return Strings::generateRandomInt($length);
    }

    /**
     * Отправить письмо после регистрации
     */
    public function sendRegistrationEmail()
    {

        return;

        \Yii::$app->mailer->htmlLayout = false;
        \Yii::$app->mailer->textLayout = false;

        \Yii::$app->mailer->compose('@templates/mail/site/registration', [
            'user' => $this
        ])
            ->setFrom([\Yii::$app->cms->adminEmail => \Yii::$app->cms->appName . ''])
            ->setTo($this->email)
            ->setSubject('Заказ на новом сайте!' . ' №')
            ->send();
    }


    public function getFormatPhone()
    {
        return preg_replace('/[^0-9]/', '', $this->phone);
    }

    /**
     * Признак что телефон подтвержден
     * @return bool
     */
    public function isApprovePhone()
    {
        return $this->phone_is_approved == YES_INT;
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return $this->name ? $this->name : $this->username;
    }

    /**
     * @param string $source
     * @param string $sourceDetail
     * @return bool
     */
    protected function addEmailBase($source = UserEmail::SOURCE_SITE, $sourceDetail = UserEmail::SOURCE_DETAIL_REGISTER_DESKTOP)
    {
        //При наличии емайла, добавляем его в базу емайлов
        if ($this->email) {
            return UserEmail::addToBase($this->email, [
                'source' => $source,
                'source_detail' => $sourceDetail,
                'user_id' => $this->getId(),
            ]);
        }

        return false;
    }

}