<?php


use modules\shopandshow\components\amqp\SSMessageBus;
use yii\queue\serializers\JsonSerializer;

return [
    'params' => include_once __DIR__ . '/params.php',
    'components' =>
        [

            'mailer' => [
                'transport' => [
                    'class' => 'Swift_SmtpTransport',
                    'host' => 'mailcatcher',
                    'port' => '1025',
                ],
                'viewPath' => '@templates/mail'
            ],
//            'sms' => [
//                'class' => '\common\components\sendSms\SmsDanytechService',
//                'classLogger' => '\common\components\sendSms\SmsLogger',
//                'serviceId' => '1270',
//                'pass' => 'tu6w4X8V',
//                'source' => 'ShopAndShow',
//                'flash' => 0
//            ],

//            'sms' => [
//                'class' => \common\components\sendSms\SendSmsFaker::class,
//            ],

        //При работе в консоли ругается на это. Раскоментировать по необходимости
//            'response' => [
//                'formatters' => [
//                    'json' => [
//                        'class' => 'yii\web\JsonResponseFormatter',
//                        'prettyPrint' => YII_DEBUG,
//                        'encodeOptions' => JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
//                    ],
//                ],
//            ],
            'log' => [
//                'traceLevel' => YII_DEBUG ? 3 : 0,
                'targets' => [
                    'file' => [
                        'class' => 'yii\log\FileTarget',
                        'levels' => ['error', 'warning'],
                    ],
                    'graylog' => [
                        'class' => 'nex\graylog\GraylogTarget',
                        'levels' => ['error', 'warning'],
//                        'categories' => ['application'],
                        'logVars' => [], // This prevent yii2-debug from crashing ;)
                        'host' => '89.108.86.188', // 89.108.86.188:9001
                        'port' => 9001,
//                        'facility' => 'facility-name',
                        'additionalFields' => [
                            'user-ip' => function ($yii) {
                                if (Yii::$app instanceof \yii\web\Application) {
                                    return $yii->request->getUserIP();
                                }
                            },
//                            'tag' => 'tag-name',
                            'app-id' => function ($yii) {
                                return $yii->id;
                            }
                        ]
                    ],
                ],
            ],
            'kfssApi' => [
                'class' => 'common\components\KfssApi',
                'httpClient' => [
                    'class' => 'yii\httpclient\Client',
                    'transport' => 'yii\httpclient\CurlTransport',
                ],
//                'baseUrl' => 'https://kfsssiteapi.sands.local/api/',
                'baseUrl' => 'http://testkfsssiteapi.sands.local/api/',
                'username' => 'SAS_SITE',
                'password' => 'Site3815!',
                'isDisable' => YII_ENV_DEV
            ],

            'kfssApiV2' => [
                'class' => \common\components\KfssApiV2::class,
                'httpClient' => [
                    'class' => 'yii\httpclient\Client',
                    'transport' => 'yii\httpclient\CurlTransport',
                ],
                'baseUrl' => 'http://testkfsssiteapi.sands.local/v2.1/api/',
                'username' => 'SAS_SITE',
                'password' => 'Site3815!',
                'isDisable' => YII_ENV_DEV
            ],

            'kfssApiV3' => [
                'class' => \common\components\KfssApiV3::class,
                'httpClient' => [
                    'class' => 'yii\httpclient\Client',
                    'transport' => 'yii\httpclient\CurlTransport',
                ],
                'baseUrl' => 'http://testkfsssiteapi.sands.local/v2.1/api/',
                'username' => 'SAS_SITE',
                'password' => 'Site3815!',
                'isDisable' => YII_ENV_DEV
            ],

            'kfssLkApiV2' => [
                'class' => \common\components\KfssLkApiV2::class,
                'httpClient' => [
                    'class' => 'yii\httpclient\Client',
                    'transport' => 'yii\httpclient\CurlTransport',
                ],
                'baseUrl' => 'https://testkfsssiteapi.sands.local/v2.1/lk/api',
                'username' => 'SAS_SITE',
                'password' => 'Site3815!',
                'isDisable' => YII_ENV_DEV
            ],

            'kfssAlfaApiV1' => [
                'class' => \common\components\KfssAlfaApiV1::class,
                'httpClient' => [
                    'class' => 'yii\httpclient\Client',
                    'transport' => 'yii\httpclient\CurlTransport',
                ],
                'baseUrl' => 'https://testkfssbankservice.sands.local/v1/api',
                'username' => 'SAS_SITE',
                'password' => 'Site3815!',
            ],

            //prod
//            'sberApi' => [
//                'class' => 'common\components\SberApi',
//                'httpClient'    => [
//                    'class' => 'yii\httpclient\Client',
//                    'transport' => 'yii\httpclient\CurlTransport',
//                ],
//                'baseUrl' => 'https://securepayments.sberbank.ru/payment/rest/',
//                'username' => 'shopandshow1-api',
//                'password' => 'Shopandshow.ru2019!api',
//            ],
//
            //dev
            'sberApi' => [
                'class' => 'common\components\SberApi',
                'httpClient' => [
                    'class' => 'yii\httpclient\Client',
                    'transport' => 'yii\httpclient\CurlTransport',
                ],
                'baseUrl' => 'https://3dsec.sberbank.ru/payment/rest/',
                'username' => 'shopandshow1-api',
                'password' => 'shopandshow1',
            ],

            'mongodb' => [
                'class' => '\yii\mongodb\Connection',
                'dsn' => 'mongodb://mongo:27017/api',
//                'options' => [
//                    "username" => "user",
//                    "password" => "pass"
//                ]
            ],

            'sphinx' => [
                'class' => 'yii\sphinx\Connection',
                'dsn' => 'mysql:host=sphinx;port=9306;',
            ],

            'dbStat' => include_once __DIR__ . '/db_stat.php',

            'redis' => [
                'class' => 'yii\redis\Connection',
                'hostname' => 'redis',
                'port' => 6379,
                'database' => 0,
            ],
            'redisCache' => [
                'class' => 'yii\redis\Connection',
                'hostname' => 'redis',
                'port' => 6379,
                'database' => 1,
            ],
            'redisQueue' => [
                'class' => 'yii\redis\Connection',
                'hostname' => 'redis',
                'port' => 6379,
                'database' => 2,
            ],
            'redisQueueFeed' => [
                'class' => 'yii\redis\Connection',
                'hostname' => 'redis',
                'port' => 6379,
                'database' => 3,
            ],
            'redisQueueProduct' => [
                'class' => 'yii\redis\Connection',
                'hostname' => 'redis',
                'port' => 6379,
                'database' => 4,
            ],
            'redisQueueSegment' => [
                'class' => 'yii\redis\Connection',
                'hostname' => 'redis',
                'port' => 6379,
                'database' => 5,
            ],
            'cache' => [
                'class' => \yii\caching\DummyCache::class,
            ],

            'storage' => [
                'class' => 'common\components\Storage',
                'components' => [

                    /** Хранилища
                     *
                     * element_images - картинки для cms_content_element
                     * property_images - картинки для cms_content_property
                     * docs - различные документы (doc,txt,etc)
                     *
                     */
                    'element_images' => [
                        'class' => 'common\components\images\clusters\ClusterLocal',
                        'priority' => 100,
                        'rootBasePath' => '/mnt/disk/site_images/uploads/images/element',
                        'publicBaseUrl' => '/uploads/images/element'
                    ],
                    'property_images' => [
                        'class' => 'skeeks\cms\components\storage\ClusterLocal',
                        'priority' => 200,
                        'rootBasePath' => '/mnt/disk/site_images/uploads/images/property',
                        'publicBaseUrl' => '/uploads/images/property'
                    ],
                    'docs' => [
                        'class' => 'skeeks\cms\components\storage\ClusterLocal',
                        'priority' => 300,
                        'rootBasePath' => '/mnt/disk/site_images/uploads/docs',
                        'publicBaseUrl' => '/uploads/docs'
                    ],
                ],
            ],

            /** Отдельная точка обмена для Фронта */
            'frontExchange' => [
                'class' => SSMessageBus::class,
                //'host' => '89.108.84.106', // 10.9.1.203
                //'port' => 5672,
                //'host' => '192.168.0.57', // 10.9.1.203
                //'port' => 5672,
                'host' => '10.9.1.177',
                'port' => 5672,
                'user' => 'MarketPlaceSite',
                'vhost' => 'production',
                'password' => 'BJ%GFy7*A?lI',
                'exchangeName' => 'FRONT',
                'exchangeType' => 'topic',
                'serializer' => JsonSerializer::class,
            ],

            /** Отдельная точка обмена для сайта */
            'siteExchange' => [
                'class' => SSMessageBus::class,
                //'host' => '89.108.84.106', // 10.9.1.203
                //'port' => 5672,
                //'host' => '192.168.0.57', // 10.9.1.203
                //'port' => 5672,
                'host' => '10.9.1.177',
                'port' => 5672,
                'user' => 'MarketPlaceSite',
                'vhost' => 'production',
                'password' => 'BJ%GFy7*A?lI',
                'exchangeName' => 'NEWSITE',
                'exchangeType' => 'topic',
                'serializer' => JsonSerializer::class,
            ],
            'admitad' => [
                'class' => '\common\components\sale\channels\AdmitadChannel',
                'campaign_code' => 'b77fa81450',
                'secret' => '04A22B59CCAb5Ac836150a0bc2b1a148',
                'endpoint' => 'https://ad.admitad.com/r'
            ]

        ]
];
