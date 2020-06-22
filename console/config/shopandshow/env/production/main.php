<?php

return [
    'params' => include_once __DIR__ . '/params.php',
    'components' => [

//        'db' => include_once __DIR__ . '/db.php',

        'queueDaemons' => [
            'class' => '\console\controllers\queues\QueueDaemons',
            'queues' => include_once __DIR__ . '/../../_queues.php',
        ],

        'mongodb' => [ // Перенести в common после тестов
            'class' => '\yii\mongodb\Connection',
            'dsn' => 'mongodb://192.168.0.76:27017/admin',
            'options' => [
                "username" => "admin",
                "password" => "K64MGDsQRA1T5HoZ9iqS"
            ]
        ],
    ]
];
