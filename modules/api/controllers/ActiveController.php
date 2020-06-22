<?php

namespace modules\api\controllers;

use common\helpers\Msg;
use yii\rest\ActiveController as YiiActiveController;
use yii\web\Request;

class ActiveController extends YiiActiveController
{
    /**
     * @int bannerPerPage
     * количество банeров на страницу на страницах списка товаров
     */
    public static $bannerPerPage = 3;

    /**
     * @int bannerPerPage
     * количество банeров на страницу на странице акции
     */
    public static $promoPerPage = 10;

    /**
     * @var Request
     */
    protected $request;

    public $modelClass = '';

    public function init()
    {
        parent::init();

        $this->request = \Yii::$app->request;

        //consumer_key=ck_b7594bc4391db4b56c635fe6da1072a53ca4535a&consumer_secret=cs_980b9edb120e15bd2a8b668cacc734f7eca
    }

    public function actions()
    {
        $actions = parent::actions();

        unset(
            $actions['index'],
            $actions['create'],
            $actions['update'],
            $actions['delete'],
            $actions['options']
        );

        return $actions;
    }

    public function afterAction($action, $result)
    {
        \Yii::$app->response->format = \yii\web\Response:: FORMAT_JSON;

        return parent::afterAction($action, $result);
    }

    public static function debugMsg()
    {
        \Yii::error("[MobAppTest] isPost: " . \Yii::$app->request->isPost ? 1 : 0);
        \Yii::error("[MobAppTest] HEADERS: " . var_export(\Yii::$app->request->headers, true));
        \Yii::error("[MobAppTest] BODY PARAMS: " . var_export(\Yii::$app->request->bodyParams, true));
        \Yii::error("[MobAppTest] POST: " . var_export(\Yii::$app->request->post(), true));
        \Yii::error("[MobAppTest] GET: " . var_export(\Yii::$app->request->get(), true));
        \Yii::error("[MobAppTest] rawBody: " . var_export(\Yii::$app->request->rawBody, true));

        return;
    }

}