<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 21/01/2019
 * Time: 17:02
 */

namespace console\controllers;


use Yii;
use yii\console\Controller;

class InitTestDbController extends Controller
{

    public function actionIndex()
    {
        $this->stdout('Start init test DB' . PHP_EOL);
        $tables = \Yii::$app->db->createCommand("SELECT TABLE_NAME FROM information_schema.tables WHERE TABLE_SCHEMA='yii2-starter-kit'")
            ->queryAll();
        \Yii::$app->db->createCommand('DROP DATABASE IF EXISTS `test_db`')->execute();
        \Yii::$app->db->createCommand('CREATE DATABASE `test_db`')->execute();
        foreach ($tables as $table) {
            $this->stdout("Create table " . $table['TABLE_NAME'] . PHP_EOL);
            \Yii::$app->db->createCommand("CREATE TABLE `test_db`.`{$table['TABLE_NAME']}` LIKE `{$table['TABLE_NAME']}`")->execute();
        }
    }


    public function actionRestore(){
        $file = Yii::getAlias('@console/../data/backup.sql');


        Yii::$app->db->createCommand(file_get_contents($file))->execute();
    }

}