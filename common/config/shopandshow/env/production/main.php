<?php

use modules\shopandshow\components\amqp\SSMessageBus;
use yii\queue\serializers\JsonSerializer;

return [
    'params' => include_once __DIR__ . '/params.php',
    'components' =>
        [

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

            'sphinx' => [
                'class' => 'yii\sphinx\Connection',
                'dsn' => 'mysql:host=10.1.0.5;port=9306;',
            ],

            'redis' => [
                'class' => 'yii\redis\Connection',
                'hostname' => '10.1.0.4',
                'port' => 6379,
                'database' => 0,
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
                'targets' => [
                    [
                        'class' => 'yii\log\EmailTarget',
                        'mailer' => 'mailerError',
                        'levels' => ['error'],
                        'categories' => [
                            'yii\db\*',
                            'yii\web\HttpException:*',
                            'yii\base\*',
                        ],
                        'except' => [
                            'yii\web\HttpException:405',
                            'yii\web\HttpException:404',
                            'yii\web\HttpException:403',
                            'yii\web\HttpException:400',
                        ],
                        'message' => [
                            'from' => ['site-dev@shopandshow.ru' => 'Ошибочки SS'],
                            'to' => [
                                'soskov_da@shopandshow.ru',
                                'ryabov_yn@shopandshow.ru',
                            ],
                            'subject' => '[PRODUCTION] ERRORS at kfss.shopandshow.ru',
                        ],
                    ],
                    /*[
                        'class' => 'yii\log\FileTarget',
                        'levels' => ['warning'],
                        'categories' => ['bitrixUserApiAuth'],
                        'logFile' => '@app/logs/Api/bitrixUserApiAuth.log',
                        'maxFileSize' => 1024 * 2,
                        'maxLogFiles' => 50,
                    ],
                    */
                    [
                        'class' => common\components\log\TelegramTarget::class,
                        'levels' => ['error'],
                        'categories' => [
                            'yii\db\*',
                            'yii\web\HttpException:*',
                        ],
                        'except' => [
                            'yii\web\HttpException:405',
                            'yii\web\HttpException:404',
                            'yii\web\HttpException:403',
                            'yii\web\HttpException:400',
                        ],
                        'prefixMessage' => 'kfssapp__',
                    ],

                ],
            ],

            'cache' => [
                'class' => 'common\components\cache\Cache',
                'cachePath' => '/mnt/ramcache',
                //'class' => 'yii\redis\Cache',
                //'redis' => 'redis',
            ],

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
                    'favorite' => '5bd2cc5b97a52531b875090a',
                    'welcome_coupon500' => '5b35c30597a528853858debb',
                ],
            ],

            'kfssApi' => [
                'class' => 'common\components\KfssApi',
                'httpClient' => [
                    'class' => 'yii\httpclient\Client',
                    'transport' => 'yii\httpclient\CurlTransport',
                ],
                //'baseUrl' => 'https://kfsssiteapi.sands.local/api/',
                'baseUrl' => 'https://kfsssiteapi.shopandshow.ru/api',
                'username' => 'SAS_SITE',
                'password' => 'Site3815!',
            ],

            'sberApi' => [
                'class' => 'common\components\SberApi',
                'httpClient'    => [
                    'class' => 'yii\httpclient\Client',
                    'transport' => 'yii\httpclient\CurlTransport',
                ],
                'baseUrl' => 'https://3dsec.sberbank.ru/payment/rest/',
                'username' => 'shopandshow1-api',
                'password' => 'shopandshow1',
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

            'mailerError' => [
                'class' => '\yii\swiftmailer\Mailer',
                'messageConfig' => [
                    //'from' => ['no-reply@shopandshow.ru' => 'Shop & Show'] // sender address goes here
                    'from' => ['site-dev@shopandshow.ru' => 'Ошибочки SS'] // sender address goes here
                ],
                'transport' => [
                    'class' => 'Swift_SmtpTransport',
//                'host' => 'smtp.gmail.com',
//                'username' => 'newsite@shopandshow.ru',
//                'password' => 'vELtcz4z',
//                'port' => '465',
//                'encryption' => 'ssl',

                    'host' => 'mail2.shopandshow.ru',
                    'username' => 'site-dev@shopandshow.ru',
                    'password' => 'EWR4}k195zJdeEp0',
                    'port' => '25',
                    //'encryption' => 'ssl',
                ],
                'viewPath' => '@templates/mail'
            ],

            'mongodb' => [
                'class' => '\yii\mongodb\Connection',
                'dsn' => 'mongodb://192.168.0.76:27017/admin',
                'options' => [
                    "username" => "admin",
                    "password" => "K64MGDsQRA1T5HoZ9iqS"
                ]
            ],

        ]
];
