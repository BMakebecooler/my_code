<?php

return
    [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=192.168.0.81;dbname=kfss_web;port=3306;',
        'username' => 'kfss_user',
        'password' => 'UOdjqbgMme5Yls26p8yz',

//        'slaveConfig' => [
//            'username' => 'kfss_user',
//            'password' => 'UOdjqbgMme5Yls26p8yz',
//            'attributes' => [
//                PDO::ATTR_TIMEOUT => 10,
//            ],
//        ],
//
//        'slaves' => [
//            ['dsn' => 'mysql:host=192.168.0.34;dbname=kfss_web;port=3306;'],
//            ['dsn' => 'mysql:host=192.168.0.35;dbname=kfss_web;port=3306;'],
//        ],

        'charset' => 'utf8',
        'enableSchemaCache' => true,
        'schemaCacheDuration' => 3600,

        'enableLogging' => false,
        'enableProfiling' => false,

        'on afterOpen' => function ($event) {
            $event->sender->createCommand("SET sql_mode = '';")->execute();
        },
        'serverRetryInterval' => 60
    ];
