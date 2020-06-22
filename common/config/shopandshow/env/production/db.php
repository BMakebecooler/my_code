<?php

/**
 * В корзине слейвы не нужны, из за проблемы задержки чтения, который вызваны синхронизацией
 */

$isSlaveEnable = false;

if ($isSlaveEnable && isset($_SERVER['REQUEST_URI'])) {
    foreach (['catalog', 'promo'] as $tree) {
        if (substr_count($_SERVER['REQUEST_URI'], $tree)) {
            $isSlaveEnable = false;
            break;
        }
    }
}

return
    [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=10.1.0.2;dbname=kfss_web;port=3306;',
        'username' => 'kfss_user',
        'password' => 'UOdjqbgMme5Yls26p8yz',

        'slaveConfig' => [
            'username' => 'website',
            'password' => '',

            'attributes' => [
                PDO::ATTR_TIMEOUT => 3,
            ],
        ],

        'slaves' => [
            ['dsn' => 'mysql:host=192.168.0.34;dbname=kfss_web;port=3306;'],
        ],

        'charset' => 'utf8',
        'enableSchemaCache' => true,
        'schemaCacheDuration' => 3600,

        'enableLogging' => true,
        'enableProfiling' => true,

        'on afterOpen' => function ($event) {
            $event->sender->createCommand("SET sql_mode = '';")->execute();
        }
    ];


