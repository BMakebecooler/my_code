<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 30/01/2019
 * Time: 18:28
 */

namespace modules\api\controllers\v1;


use modules\api\behaviors\HttpBearerAuth;
use modules\api\models\form\CreateOrderForm;
use modules\api\resource\Order;
use modules\shopandshow\models\shop\ShopOrder;
use yii\rest\Controller;

class OrderController extends Controller
{


    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::className(),
            'except' => ['create-test']
        ];
        return $behaviors;
    }

    public function actionStat()
    {
        $array = [
            [
                'name' => 'sales',
                'val' => round(ShopOrder::getCurrentFactGraph())
            ],
            [
                'name' => 'plan',
                'val' => round(ShopOrder::getCurrentPlanGraph())
            ],
            [
                'name' => 'count',
                'val' => round(ShopOrder::getCurrentCountGraph())
            ],
        ];
        return $array;
    }

    public function actionCreate()
    {
        $form = new CreateOrderForm();

        if ($form->load(\Yii::$app->request->bodyParams, '') && $form->validate() && $form->save()) {
            return ['id' => $form->getOrder()->id];
        }


        return $form->errors;
    }

    public function actionCreateTest()
    {
        $form = new CreateOrderForm();
//        $data = [
//            "product_id" => 1661614,
//            "phone" => 79265812870,
//            "email" => "email@gmail.com",
//            "name" => "Vasya"
//        ];
        $data = \Yii::$app->request->bodyParams;
        $data['product_id'] = 1661614;


        \Yii::info('data order create test' . print_r(\Yii::$app->request->bodyParams, true), 'apiv1');
        \Yii::info('data order create test' . print_r(\Yii::$app->request->queryParams, true), 'apiv1');
        if ($form->load($data, '') && $form->validate() && $form->save()) {
            return ['id' => $form->getOrder()->id, 'test' => 'test'];
        }


        return $form->errors;
    }

    public function actionStatus($id)
    {

        $model = ShopOrder::findOne($id);
        if (empty($model)) {
            throw new \yii\db\Exception('not found order');
        }

        return ['status_code' => $model->status_code];

    }

    public function actionStatusMany($ids)
    {
        $ids = explode(',', $ids);

        $response = [];
        foreach ($ids as $id) {
            $model = Order::findOne($id);
            if ($model) {
                $response[] = $model;
            }
        }
        return $response;
    }

}