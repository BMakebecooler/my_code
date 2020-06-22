<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 2019-06-06
 * Time: 17:42
 */

namespace modules\api\controllers\v1;


use frontend\models\form\SubscribeForm;
use yii\rest\ActiveController;
use yii\rest\Controller;
use yii\web\Response;

class SubscribeController extends Controller
{
//    public $modelClass = SubscribeForm::class;


//    public $serializer = [
//        'class' => 'yii\rest\Serializer',
//        'collectionEnvelope' => 'items',
//    ];
    public function actionCreate(){

        $model = new SubscribeForm();

        if($model->load( \Yii::$app->request->bodyParams,'SubscribeForm') && $model->save()){

            return ['data' => $model, 'message' => 'Спасибо за подписку!'];
        }

        \Yii::$app->response->setStatusCode(500);
        return ['data' => $model->errors, 'message' => 'Проверьте правильность заполнения полей'];



    }

}