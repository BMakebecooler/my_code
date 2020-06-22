<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 18.09.2015
 */
return
    [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=db;dbname=yii2-starter-kit;port=3306;',
//        'dsn' => 'mysql:host=db;dbname=testDB;port=3306;',
        'username' => 'root',
        'password' => 'root',


        'charset' => 'utf8',
        'enableSchemaCache' => true,
        'schemaCacheDuration' => 3600,

        'enableLogging' => true,
        'enableProfiling' => true,

        'on afterOpen' => function ($event) {
            $event->sender->createCommand("SET sql_mode = '';")->execute();
        }
    ];
