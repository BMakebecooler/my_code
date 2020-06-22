<?php

use modules\shopandshow\components\amqp\SSMessageBus;
use yii\queue\serializers\JsonSerializer;

return [
    'params' => include_once __DIR__ . '/params.php',
    'components' =>
        [
            'dbStat' => include_once __DIR__ . '/db_stat.php',

            //'front_db' => include_once __DIR__ . '/front_db.php',

            'assetManager' => [
                'assetMap' => [
//                    'jquery.min.js' => 'https://code.jquery.com/jquery-2.2.4.min.js',
//                    'bootstrap.min.css' => 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css',
//                    'bootstrap.min.js' => 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js',
//                    'underscore-min.js' => 'https://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.8.3/underscore-min.js',
                    'metrika.js' => 'https://mc.yandex.ru/metrika/watch.js',
                ],
            ],

            'cache' => [
//                'class' => \yii\redis\Cache::class,
//                'redis' => 'redisCache',

                'class' => 'common\components\cache\Cache',
                'cachePath' => '/mnt/ramcache',
            ],

            'sphinx' => [
                'class' => 'yii\sphinx\Connection',
                'dsn' => 'mysql:host=192.168.0.71;port=9306;',
            ],

            'redis' => [
                'class' => 'yii\redis\Connection',
//                'hostname' => '192.168.0.71',
//                'hostname' => '10.1.0.2,
                'hostname' => '127.0.0.1',
                'port' => 6379,
                'database' => 0,
            ],
            'redisCache' => [
                'class' => 'yii\redis\Connection',
//                'hostname' => '192.168.0.71',
//                'hostname' => '127.0.0.1',
                'hostname' => '10.1.0.2',
                'port' => 6379,
                'database' => 1,
            ],
            'redisQueue' => [
                'class' => 'yii\redis\Connection',
                'hostname' => '192.168.0.71',
//                'hostname' => '127.0.0.1',
                'port' => 6379,
                'database' => 2,
            ],
            'redisQueueFeed' => [
                'class' => 'yii\redis\Connection',
                'hostname' => '192.168.0.71',
//                'hostname' => '127.0.0.1',
                'port' => 6379,
                'database' => 3,
            ],
            'redisQueueProduct' => [
                'class' => 'yii\redis\Connection',
                'hostname' => '192.168.0.71',
//                'hostname' => '127.0.0.1',
                'port' => 6379,
                'database' => 4,
            ],
            'redisQueueSegment' => [
                'class' => 'yii\redis\Connection',
                'hostname' => '192.168.0.71',
//                'hostname' => '127.0.0.1',
                'port' => 6379,
                'database' => 5,
            ],

            'storage' => [
                'class' => 'common\components\Storage',
                'components' => [

                    /** Хранилища
                     *
                     * local - картинки для cms_content_element
                     * element_images - картинки для cms_content_element
                     * property_images - картинки для cms_content_property
                     * docs - различные документы (doc,txt,etc)
                     *
                     */
                    'local' => [
                        'class' => 'common\components\images\clusters\ClusterLocal',
                        'priority' => 100,
                        'rootBasePath' => '/mnt/static_content/uploads/all',
                        'publicBaseUrl' => '/uploads/all'
                    ],
                    'element_images' => [
                        'class' => 'common\components\images\clusters\ClusterLocal',
                        'priority' => 100,
                        'rootBasePath' => '/mnt/static_content/uploads/images/element',
                        'publicBaseUrl' => '/uploads/images/element'
                    ],
                    'property_images' => [
                        'class' => 'skeeks\cms\components\storage\ClusterLocal',
                        'priority' => 200,
                        'rootBasePath' => '/mnt/static_content/uploads/images/property',
                        'publicBaseUrl' => '/uploads/images/property'
                    ],
                    'docs' => [
                        'class' => 'skeeks\cms\components\storage\ClusterLocal',
                        'priority' => 300,
                        'rootBasePath' => '/mnt/static_content/uploads/docs',
                        'publicBaseUrl' => '/uploads/docs'
                    ],
                ],
            ],

//            'session' => [
//                'class' => 'yii\redis\Session',
//                'redis' => [
//                    'class' => 'yii\redis\Connection',
//                    'hostname' => 'localhost',
//                    'port' => 6379,
//                    'database' => 0,
//                ],
//                'cookieParams' => ['lifetime' => 7 * 24 * 60 * 60],
//
//            ],

            /** Отдельная точка обмена для Фронта */
            'frontExchange' => [
                'class' => SSMessageBus::class,
                'host' => '185.179.124.35', // 10.9.1.203
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
                'host' => '185.179.124.35', // 10.9.1.203
                'port' => 5672,
                'user' => 'MarketPlaceSite',
                'vhost' => 'production',
                'password' => 'BJ%GFy7*A?lI',
                'exchangeName' => 'NEWSITE',
                'exchangeType' => 'topic',
                'serializer' => JsonSerializer::class,
            ],

            'log' => [
                'traceLevel' => YII_DEBUG ? 3 : 0,
                'targets' => [
//                    'file' => [
//                        'class' => 'yii\log\FileTarget',
//                        'levels' => ['error', 'warning'],
//                    ],
                    'graylog' => [
                        'class' => 'nex\graylog\GraylogTarget',
                        'levels' => ['error', 'warning'],
//                        'categories' => ['application'],
                        'logVars' => [], // This prevent yii2-debug from crashing ;)
                        'host' => '192.168.0.3', // 89.108.86.188:9001
                        'port' => 9001,
//                        'facility' => 'prod-env',
                        'additionalFields' => [
                            'user-ip' => function ($yii) {
                                if (Yii::$app instanceof \yii\web\Application) {
                                    return $yii->request->getUserIP();
                                }
                            },
                            'requestPath' => function ($yii) {
                                if (Yii::$app instanceof \yii\web\Application) {
                                    return $yii->request->pathInfo;
                                }
                            },
                            'response-code' => function ($yii) {
                                if (Yii::$app instanceof \yii\web\Application) {
                                    return $yii->response->statusCode;
                                }
                            },
                            'tag' => 'tag-name'
                        ],
                        'addUsername' => true,
                        'exportInterval' => 500
                    ],
                ],
            ],

//            'log' => [
//                'targets' => [
//                    [
//                        'class' => 'yii\log\EmailTarget',
//                        'mailer' => 'mailerError',
//                        'levels' => ['error'],
//                        'categories' => [
//                            'yii\db\*',
//                            'yii\web\HttpException:*',
//                            'yii\base\*',
//                        ],
//                        'except' => [
//                            'yii\web\HttpException:405',
//                            'yii\web\HttpException:404',
//                            'yii\web\HttpException:403',
//                            'yii\web\HttpException:400',
//                        ],
//                        'message' => [
//                            'from' => ['site-dev@shopandshow.ru' => 'Ошибочки SS'],
//                            'to' => [
//                                'soskov_da@shopandshow.ru',
//                                'ryabov_yn@shopandshow.ru',
//                            ],
//                            'subject' => '[PRODUCTION] ERRORS at kfss.shopandshow.ru',
//                        ],
//                    ],
//                    /*[
//                        'class' => 'yii\log\FileTarget',
//                        'levels' => ['warning'],
//                        'categories' => ['bitrixUserApiAuth'],
//                        'logFile' => '@app/logs/Api/bitrixUserApiAuth.log',
//                        'maxFileSize' => 1024 * 2,
//                        'maxLogFiles' => 50,
//                    ],
//                    */
//                    [
//                        'class' => common\components\log\TelegramTarget::class,
//                        'levels' => ['error'],
//                        'categories' => [
//                            'yii\db\*',
//                            'yii\web\HttpException:*',
//                            'yii\base\*',
//                        ],
//                        'except' => [
//                            'yii\web\HttpException:405',
//                            'yii\web\HttpException:404',
//                            'yii\web\HttpException:403',
//                            'yii\web\HttpException:400',
//                            'yii\base\ViewNotFoundException'
//                        ],
//                        'prefixMessage' => 'kfssapp__',
//                    ],
//
//                ],
//            ],


            'getResponseService' => [
                'class' => 'common\components\email\services\GRClient',
                'baseUrl' => 'https://api3.getresponse360.pl/v3',
                'token' => '95ae8af1a1e04df6933d5b569cbb6244',
                'domain' => 'email.shopandshow.ru',
                // h (старая 9) - реальная рассылка по базе всех клиентов, C - тестовая рассылка для разработчиков и контентщиков
                'campaignToken' => 'C'
            ],

            'retailRocketService' => [
                'class' => 'common\components\email\services\RetailRocket',
                'httpClient' => [
                    'class' => 'yii\httpclient\Client',
                    'transport' => 'yii\httpclient\CurlTransport',
                ],
                'baseUrl' => 'https://api.retailrocket.net/api/1.0/partner/',
                'partnerToken' => '564d72a46636b420a06cf01a',
                'apiKey' => '564d72a46636b420a06cf01b',
                'mailTemplatesTokens' => [
                    'welcome' => '5b35c2df97a528853858deb3',
                ],
            ],


            'kfssApi' => [
                'class' => 'common\components\KfssApi',
                'httpClient' => [
                    'class' => 'yii\httpclient\Client',
                    'transport' => 'yii\httpclient\CurlTransport',
                ],
                'baseUrl' => 'https://kfsssiteapi.sands.local/api',
//                'baseUrl' => 'https://kfsssiteapi.shopandshow.ru/api',
                'username' => 'SAS_SITE',
                'password' => 'Site3815!',
            ],

            //prod
            'sberApi' => [
                'class' => 'common\components\SberApi',
                'httpClient'    => [
                    'class' => 'yii\httpclient\Client',
                    'transport' => 'yii\httpclient\CurlTransport',
                ],
                'baseUrl' => 'https://securepayments.sberbank.ru/payment/rest/',
                'username' => 'shopandshow1-api',
                'password' => 'Shopandshow.ru2019!api',
            ],

            //dev
//            'sberApi' => [
//                'class' => 'common\components\SberApi',
//                'httpClient'    => [
//                    'class' => 'yii\httpclient\Client',
//                    'transport' => 'yii\httpclient\CurlTransport',
//                ],
//                'baseUrl' => 'https://3dsec.sberbank.ru/payment/rest/',
//                'username' => 'shopandshow1-api',
//                'password' => 'shopandshow1',
//            ],

            'kfssApiV2' => [
                'class' => \common\components\KfssApiV2::class,
                'httpClient' => [
                    'class' => 'yii\httpclient\Client',
                    'transport' => 'yii\httpclient\CurlTransport',
                ],
                'baseUrl' => 'https://kfsssiteapi.sands.local/v2.1/api/',
                'username' => 'SAS_SITE',
                'password' => 'Site3815!',
            ],

            'kfssApiV3' => [
                'class' => \common\components\KfssApiV3::class,
                'httpClient' => [
                    'class' => 'yii\httpclient\Client',
                    'transport' => 'yii\httpclient\CurlTransport',
                ],
                'baseUrl' => 'https://kfsssiteapi.sands.local/v2.1/api/',
                'username' => 'SAS_SITE',
                'password' => 'Site3815!',
            ],

            'kfssLkApiV2' => [
                'class' => \common\components\KfssLkApiV2::class,
                'httpClient' => [
                    'class' => 'yii\httpclient\Client',
                    'transport' => 'yii\httpclient\CurlTransport',
                ],
                'baseUrl' => 'https://kfsssiteapi.sands.local/v2.1/lk/api',
                'username' => 'SAS_SITE',
                'password' => 'Site3815!',
                'isDisable' => false
            ],

            'kfssAlfaApiV1' => [
                'class' => \common\components\KfssAlfaApiV1::class,
                'httpClient' => [
                    'class' => 'yii\httpclient\Client',
                    'transport' => 'yii\httpclient\CurlTransport',
                ],
                'baseUrl' => 'https://kfssbankservice.sands.local/v1/api',
                'username' => 'SAS_SITE',
                'password' => 'Site3815!',
            ],

            'mailer' => [
                'transport' => [
                    'class' => 'Swift_SmtpTransport',
//                'host' => 'smtp.gmail.com',
//                'username' => 'newsite@shopandshow.ru',
//                'password' => 'vELtcz4z',
//                'port' => '465',
//                'encryption' => 'ssl',

                    'host' => 'smtp.mailgun.org',
                    'username' => 'postmaster@mailgun.shopandshow.ru',
                    'password' => '21d749317756b5eba84ef060d2af83ec',
                    'port' => '465',
                    'encryption' => 'ssl',
                ],
                'viewPath' => '@templates/mail'
            ],

//            'mailerError' => [
//                'class' => '\yii\swiftmailer\Mailer',
//                'transport' => [
//                    'class' => 'Swift_SmtpTransport',
////                'host' => 'smtp.gmail.com',
////                'username' => 'newsite@shopandshow.ru',
////                'password' => 'vELtcz4z',
////                'port' => '465',
////                'encryption' => 'ssl',
//
//                    'host' => 'mail2.shopandshow.ru',
//                    'username' => 'site-dev@shopandshow.ru',
//                    'password' => 'EWR4}k195zJdeEp0',
//                    'port' => '25',
//                    //'encryption' => 'ssl',
//                ],
//                'viewPath' => '@templates/mail'
//            ],

            'mongodb' => [
                'class' => '\yii\mongodb\Connection',
                'dsn' => 'mongodb://192.168.0.76:27017/admin',
                'options' => [
                    'username' => 'admin',
                    'password' => 'K64MGDsQRA1T5HoZ9iqS',
                ]
            ],
            'admitad' => [
                'class' => '\common\components\sale\channels\AdmitadChannel',
                'campaign_code' => 'b77fa81450',
                'secret' => '04A22B59CCAb5Ac836150a0bc2b1a148',
                'endpoint' => 'https://ad.admitad.com/r'
            ]
        ]
];
