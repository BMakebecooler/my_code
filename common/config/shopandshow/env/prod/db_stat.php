<?php

return
    [
        'class' => 'yii\db\Connection',
        //local
//        'dsn' => 'mysql:host=192.168.0.58;dbname=test;port=3306;',
//        'username' => 'newsite',
//        'password' => 'LDeTPjvZmTWdX51',

        //remote cluster
        'dsn' => 'mysql:host=89.108.84.85;dbname=analytics;port=3306;',
        'username' => 'site',
        'password' => 'PMgfyaPIR2qYi0rAtGDk',


        'charset' => 'utf8',
        'enableSchemaCache' => true,
        'schemaCacheDuration' => 3600,

        'enableLogging' => true,
        'enableProfiling' => true,

        'on afterOpen' => function ($event) {
            $event->sender->createCommand("SET sql_mode = '';")->execute();
        }
    ];
