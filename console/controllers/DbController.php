<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 21/01/2019
 * Time: 17:02
 */

namespace console\controllers;


use yii\console\Controller;

class DbController extends Controller
{

    public function actionTest()
    {

        $this->stdout('Db Connection open ' . \Yii::$app->db->open() . PHP_EOL);
        $this->stdout('Db Connection is ' . \Yii::$app->db->isActive . PHP_EOL);
        $this->stdout('Db dsn ' . \Yii::$app->db->dsn . PHP_EOL);
        $this->stdout('Db username ' . \Yii::$app->db->username . PHP_EOL);
        $this->stdout('Db password ' . \Yii::$app->db->password . PHP_EOL);

        $this->stdout('Db Connection open ' . \Yii::$app->db->slave->open() . PHP_EOL);
        $this->stdout('Db Connection is ' . \Yii::$app->db->slave->isActive . PHP_EOL);
        $this->stdout('Db dsn ' . \Yii::$app->db->slave->dsn . PHP_EOL);
        $this->stdout('Db username ' . \Yii::$app->db->slave->username . PHP_EOL);
        $this->stdout('Db password ' . \Yii::$app->db->slave->password . PHP_EOL);




    }

}