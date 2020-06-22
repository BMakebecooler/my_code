<?php

return
    [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=localhost;dbname=kfss_web;port=3306;',
        'username' => 'kfss_user',
        'password' => 'UOdjqbgMme5Yls26p8yz',

/*        'slaveConfig' => [
            'username' => 'website',
            'password' => '',

            'attributes' => [
                PDO::ATTR_TIMEOUT => 3,
            ],
        ],

        'slaves' => [
            ['dsn' => 'mysql:host=192.168.0.59;dbname=ss_web;port=33066;'],
        ],*/

        'charset' => 'utf8',
        'enableSchemaCache' => false,
        'schemaCacheDuration' => 3600,

        'enableLogging' => true,
        'enableProfiling' => true,

        'on afterOpen' => function ($event) {
            $event->sender->createCommand("SET sql_mode = '';")->execute();
        }
    ];
