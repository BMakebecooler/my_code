<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 2019-08-02
 * Time: 16:39
 */

namespace console\controllers;


use yii\console\Controller;

class AppController extends Controller
{


    public function actionSetup()
    {
        \Yii::$app->runAction('migrate/up', ['interactive' => $this->interactive]);


        \Yii::$app->runAction('migrate/up', [
            'interactive' => $this->interactive,
            'migrationPath' => 'vendor/nhkey/yii2-activerecord-history/migrations'
        ]);

        \Yii::$app->runAction('migrate/up', [
            'interactive' => $this->interactive,
            'migrationPath' => 'vendor/webtoolsnz/yii2-scheduler/src/migrations'
        ]);
    }

}