<?php
/**
 * Created by PhpStorm.
 * User: ubuntu5
 * Date: 05.09.17
 * Time: 13:39
 */

namespace modules\shopandshow\models\shop\forms;

use common\helpers\Strings;
use common\models\user\authorizations\SignupForm;
use common\models\user\User;
use modules\shopandshow\models\shop\ShopOrder;

class QuickOrder extends SignupForm
{

    /**
     * Дефолтное имя при оформлении быстрого заказа
     */
    //const DEFAULT_USER_NAME = 'Быстрый заказ';
    const DEFAULT_USER_NAME = '';

    public $scenario = User::SCENARIO_RIGISTRATION_FROM_FAST_ORDER;

    public $phone;
    public $name = self::DEFAULT_USER_NAME;
    public $surname;
    public $email;
    public $source = ShopOrder::SOURCE_SITE;
    public $source_detail = ShopOrder::SOURCE_DETAIL_FAST_ORDER;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
//            [['phone'], 'required', 'when' => function($model){
//                return \Yii::$app->request->post('Checkout') == 'finish'; //Обязательно только на финише, например на перерасчете оно не обязательно
//            }], //, 'name'
            [['phone', 'name', 'patronymic', 'surname'], 'required', 'when' => function($model){
                //Обязательно только на финише, например на перерасчете оно не обязательно
                return \Yii::$app->request->post('Checkout') && \Yii::$app->request->post('Checkout') == 'finish';
            }],
            ['email', 'email'],
            [
                'phone', 'filter', 'filter' => function($phone){
                    return Strings::getPhoneClean($phone);
                },
            ],
            ['phone', 'validatePhone'],
            [['name'], 'safe'],
            [['name'], 'default', 'value' => function (self $model) {
                return self::DEFAULT_USER_NAME;
            }],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'phone' => 'Ваш телефон',
            'name' => 'Имя',
            'surname' => 'Фамилия',
            'patronymic' => 'Отчество',
            'email' => 'Электронная почта',
        ];
    }

    /**
     * @param $attr
     */
    public function validatePhone($attr)
    {
        $phone = $this->phone;

        if (!$phone || strlen(Strings::onlyInt($phone)) !== 10) {
            $this->addError($attr, 'Не корректный номер телефона. Пример: 88003016010');
        }
    }

    /**
     * Определение источника быстрого заказа - десктоп или мобила
     * @return bool|User
     */
    public function signup()
    {
        $this->source_detail = \Yii::$app->mobileDetect->isMobile() ? ShopOrder::SOURCE_DETAIL_FAST_ORDER_MOBILE : ShopOrder::SOURCE_DETAIL_FAST_ORDER;

        return parent::signup();
    }

}