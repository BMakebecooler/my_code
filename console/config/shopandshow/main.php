<?php

return [
    'id' => 'app-console',
    'basePath' => dirname(dirname(__DIR__)),
    'bootstrap' => [
        'log',
        'cms',
        'shopandshow',
        'gii',
        'scheduler'
    ],

    'controllerNamespace' => 'console\controllers',

    'components' => [
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],

        'urlManager' => [
            'baseUrl' => 'https://shopandshow.ru/'
        ],

        'cache' => [
//            'class' => 'common\components\cache\Cache',
            'class' => 'common\components\cache\DummyCache',
//            'class' => \yii\redis\Cache::class,
//            'redis' => 'redisCache'
        ],

        'frontendCache' => [
//            'class' => 'common\components\cache\Cache',
            'class' => \yii\redis\Cache::class,
            'redis' => 'redisCache'
        ],

        'queueDaemons' => [
            'class' => '\console\controllers\queues\QueueDaemons',
            'queues' => include_once __DIR__ . '/_queues.php',
        ],
//                'user' => [
//                    'class' => 'yii\web\User',
//                    'identityClass' => 'common\models\user\User',
//                    //'enableAutoLogin' => true,
//                ],

    ],

    'modules' => [
        'shopandshow' => [
            'class' => 'modules\shopandshow\ShopAndShowModule',
        ],
        'gii' => [
            'class' => \yii\gii\Module::class,
            'allowedIPs' => ['*'],
            'generators' => [
                'model' => [
                    'class' => \yii\gii\generators\model\Generator::class,
                    'templates' => [
                        'myModel' => '@vendor/ignatenkovnikita/yii2-gii-addons/model/default',
                    ]
                ]
            ],
        ],

        'scheduler' => [
            'class' => 'webtoolsnz\scheduler\Module',
            'taskPath' => '@console/tasks',
            'taskNameSpace' => 'console\tasks'
        ],

        /*        'authclient' => [
                    'class' => 'skeeks\cms\authclient\CmsAuthClientModule',
                ]*/
    ],

    'params' => include_once __DIR__ . '/params.php',
    'aliases' => [
        '@webroot' => \Yii::getAlias("@frontend/web"),
    ],
];

