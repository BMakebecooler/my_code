<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 2019-03-22
 * Time: 11:06
 */

namespace modules\api\controllers\v1;


use modules\api\behaviors\HttpBearerAuth;
use yii\helpers\ArrayHelper;
use yii\rest\Controller;

class QueueController extends Controller
{

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::className(),
        ];
        return $behaviors;
    }


    public function actionStat()
    {
        $queues = [
            'queue',
            'queueFeed',
            'queueProduct',
            'queueSegment',
        ];
        $array = [];
        foreach ($queues as $queueName) {
            $queue = \Yii::$app->$queueName;
            $prefix = $queue->channel;
            $waiting = $queue->redis->llen("$prefix.waiting");
            $delayed = $queue->redis->zcount("$prefix.delayed", '-inf', '+inf');
            $reserved = $queue->redis->zcount("$prefix.reserved", '-inf', '+inf');
            $array[] = [
                'name' => $queueName . '_waiting',
                'val' => (int)$waiting
            ];
            $array[] = [
                'name' => $queueName . '_delayed',
                'val' => (int)$delayed
            ];
            $array[] = [
                'name' => $queueName . '_reserved',
                'val' => (int)$reserved
            ];
        }

        return $array;

    }
}