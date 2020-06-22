<?php

return
    [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=127.0.0.1;dbname=front2;port=3306;',
        'username' => 'ss_web_sync',
        'password' => 'xnbvSGnhjDAS]',
        'charset' => 'utf8',
        'enableSchemaCache' => true,
        'schemaCacheDuration' => 3600,

        'on afterOpen' => function ($event) {
            $event->sender->createCommand("SET sql_mode = '';")->execute();
        }
    ];
