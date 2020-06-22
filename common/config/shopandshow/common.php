<?php
return [
    'timeZone' => 'Europe/Moscow',
    'components' => [
        'queue' => [
            'class' => \yii\queue\redis\Queue::class,
            'as log' => \yii\queue\LogBehavior::class,
            // Other driver options,
            'redis' => 'redisQueueSegment',
            'ttr' => 60 * 60, // Max time for job execution
            'attempts' => 1, // Max number of attempts
            'channel' => 'segment'
        ],
        'redisQueueSegment' => [
            'class' => 'yii\redis\Connection',
//            'hostname' => 'redis',
            'hostname' => '192.168.0.71',
            'port' => 6379,
            'database' => 5,
        ],
//        'redisQueue' => [
//            'class' => 'yii\redis\Connection',
//            'hostname' => 'redis',
//            'port' => 6379,
//            'database' => 2,
//        ],
        'urlManagerFrontend' => [
            'class' => 'yii\web\UrlManager',
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
//            'baseUrl' => 'http://yii2-starter-kit.example',
//            'baseUrl' =>  'http://dev.shopandshow.ru',
            'baseUrl' => 'https://shopandshow.ru',
            'rules' => [
                ['pattern' => '/images', 'route' => 'images/view'],
                ['pattern' => '/support/<id>-<slug>', 'route' => 'content-element/index'],
                ['pattern' => '/promo', 'route' => 'promo/index'],
                ['pattern' => '/catalog/<slug:.+>', 'route' => 'category/view','defaults' => ['slug' => \common\helpers\Url::$catalogSlug]],
                ['pattern' => '/promo/page/<slug>/<category:.*>', 'route' => 'promo/view','defaults' => ['category' => '']],
                ['pattern' => '/promo/sales', 'route' => 'promo/view', 'defaults' => ['slug' => \common\helpers\Url::$saleSlug,'category' => '']],
            ],
        ],
    ]
];