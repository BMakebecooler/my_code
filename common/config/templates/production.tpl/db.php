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
        'dsn' => 'mysql:host=192.168.0.61;dbname=ss_web;port=33066;',
        'username' => 'website',
        'password' => '',

        'charset' => 'utf8',
        'enableSchemaCache' => false,

        'slaves' => [],
        'slaveConfig' => [],

        'on afterOpen' => function ($event) {
            $event->sender->createCommand("SET sql_mode = '';")->execute();
        }
    ];
