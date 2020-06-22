<?php

return [
    'params' => include_once __DIR__ . '/params.php',
    'components' => [

//        'db' => include_once __DIR__ . '/db.php',

        'urlManager' => [
            'baseUrl' => 'https://shopandshow.ru/'
        ],

        'queueDaemons' => [
            'class' => '\console\controllers\queues\QueueDaemons',
            'queues' => include_once __DIR__ . '/../../_queues.php',
        ],
    ]
];
