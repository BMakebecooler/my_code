<?php


namespace modules\api\controllers\v2;


use common\helpers\User;
use modules\shopandshow\models\shop\forms\FinishOrder2;
use modules\shopandshow\models\shop\forms\QuickOrder;
use modules\shopandshow\models\shop\ShopBuyer;
use modules\shopandshow\models\shop\ShopOrder;
use yii\filters\Cors;
use yii\filters\VerbFilter;
use yii\rest\Controller;

class OrderController extends Controller
{
    private $response = [
        'success' => false,
        'message' => '',
        'data' => []
    ];

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // remove authentication filter
        $auth = $behaviors['authenticator'];
        unset($behaviors['authenticator']);

        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => Cors::className(),
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'HEAD', 'OPTIONS', 'POST'],
            ],
        ];

        // re-add authentication filter
        //$behaviors['authenticator'] = $auth;
        // avoid authentication on CORS-pre-flight requests (HTTP OPTIONS method)
        //$behaviors['authenticator']['except'] = ['options'];

        $behaviors['verbsFilter'] = [
            'class' => VerbFilter::className(),
            'actions' => [
                'index' => ['get'],
                'remove' => ['post'],
                'add-product' => ['post'],
                'add-discount-coupon' => ['post'],
            ],
        ];

        $behaviors['contentNegotiator'] = [
            'class' => \yii\filters\ContentNegotiator::className(),
            'formats' => [
                'application/json' => \yii\web\Response::FORMAT_JSON,
            ],
        ];

        return $behaviors;

        return [
            [
                'class' => \yii\filters\ContentNegotiator::className(),
                'formats' => [
                    'application/json' => \yii\web\Response::FORMAT_JSON,
                ],
            ],
            [
                'class' => Cors::className(),
                'cors' => [
                    'Origin' => ['*'],
                    'Access-Control-Request-Method' => ['GET', 'HEAD', 'OPTIONS', 'POST'],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'index' => ['get'],
                    'remove' => ['post'],
                    'add-product' => ['post'],
                    'add-discount-coupon' => ['post'],
                ],
            ],
        ];
    }

    public function actionOneClick()
    {
        $fuser = \Yii::$app->shop->shopFuser;

        $modelUser = \common\helpers\User::isAuthorize() ? \common\models\user\User::findOne(\Yii::$app->user->id) : new QuickOrder();
        $modelFinishOrder = new FinishOrder2();

        if ($modelUser->isNewRecord){
            $modelUser->setAttributes([
                'phone' => \Yii::$app->request->post('phone'),
            ]);
        }

        if ($modelUser->validate()){
            //Сохраняем юзера (имеющегося или нового)
            $userSaved = false;
            if ($modelUser instanceof \common\models\user\User){
                if ($modelUser->save(true, [
                    'phone'
                ])){
                    $userSaved = true;
                }
            }else{
                if ($registeredUser = $modelUser->signup()){
                    \Yii::$app->user->login($registeredUser, DAYS_30);
                    $modelUser->storeLoginAttempt();
                    $userSaved = true;
                }
            }

            $user = \Yii::$app->user->identity;
            if (!$fuser->buyer && User::isAuthorize()) {
                $buyer = new ShopBuyer([
                    'shop_person_type_id' => 1, //1 - физ лицо. Из-за обновления моделей не используем констарнты из старых
                    'cms_user_id' => $user->id
                ]);
                $buyer->save();
                $fuser->buyer_id = $buyer->id;
                $fuser->save();
            }
        }


        //Сохраняем дадату, то что делает вот это
        //$modelFinishOrder->processed();

        //\Yii::$app->shop->shopFuser->setDelivery($deliveryId);
        //\Yii::$app->shop->shopFuser->setPayment($paymentId)

        //Все предварительное сохранили - создаем заказ
        if ($userSaved && $modelFinishOrder->validate()) {

            //* KFSS *//

            $isKfssApiDisabled = \Yii::$app->kfssApiV2->isDisable;
            $orderNumber = $fuser->external_order_id;

            //Отключаем отключение
            if ($isKfssApiDisabled /*&& !empty($orderNumber)*/){
                \Yii::$app->kfssApiV2->isDisable = false;
            }

            //* Завершение оформления полностью переводим на кфсс *//

            //Если на этом моменте связи с КФСС нет - инициируем ее и сразу отправляем на чекаут
            if (!$fuser->external_order_id){

                //Связи нет - инициируем заказ/
                $kfssOrderId = \Yii::$app->kfssApiV2->initOrder();
                \Yii::$app->kfssApiV2->recalculateOrder();
            }

            //* /Завершение оформления полностью переводим на кфсс *//

            $order = ShopOrder::checkout();

            //Если в целом АПИ отключено то возвращаем в исходное состояние
            if ($isKfssApiDisabled /*&& !empty($orderNumber)*/){
                \Yii::$app->kfssApiV2->isDisable = true;
            }

            //* /KFSS *//

            if ($order) {

                //При онлайн оплате приходится где то сохранять урль формы оплаты, пока лучше комента ниче не нашлось (
                //Отдельно городить поле не стал
                $urlRedirect = $order->comments ?: $order->publicUrl;

                //Сохраняем прочую инфу по заказу
                $orderDataSaved = false;
                if ($modelFinishOrder->processed()){
                    $orderDataSaved = true;
                }else{
                    //err
                }
            }

        }

    }
}









