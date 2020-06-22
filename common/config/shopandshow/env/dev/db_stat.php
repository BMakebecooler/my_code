<?php

return
    [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=db;dbname=yii2-starter-kit;port=3306;',
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
