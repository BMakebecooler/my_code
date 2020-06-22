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
        'dsn' => 'mysql:host=10.1.0.2;dbname=kfss_web;port=3306;',
        'username' => 'kfss_user',
        'password' => 'UOdjqbgMme5Yls26p8yz',
        'charset' => 'utf8',
        'enableSchemaCache' => true,
        'schemaCacheDuration' => 3600,

        'enableSlaves' => false,

        'on afterOpen' => function ($event) {
            $event->sender->createCommand("SET sql_mode = '';")->execute();
        }
    ];
