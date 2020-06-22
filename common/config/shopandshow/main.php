<?php
/**
 * Общий конфиг для всего приложения
 */

use \modules\shopandshow\components\export\YandexTurboPageHandler;

$config = [
    'name' => 'shop-and-show',
    'vendorPath' => ROOT_DIR . '/vendor',
    'timeZone' => 'Europe/Moscow',
    'on beforeRequest' => function ($event) {
    },

    'bootstrap' => [
        'appComponent',
//        'queue',
        'shopandshow',
        'redirect',
        'promoListener',
        'maintenanceMode',
        'queue',
        'queueFeed',
        'queueProduct',
        'queueSegment',
    ],

    'controllerMap' => [
        'migrate' => [
            'class' => 'console\commands\MigrateController',
            'migrationLookup' => [
//                '@console/migrations/backup',
//                '@modules/shopandshow/migrations',
            ]
        ]
    ],

    'params' => include_once __DIR__ . '/params.php',

    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => env('DB_DSN'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),

            'charset' => 'utf8',
            'enableSchemaCache' => true,
            'schemaCacheDuration' => 3600,

            'enableLogging' => false,
            'enableProfiling' => false,

            'on afterOpen' => function ($event) {
                $event->sender->createCommand("SET sql_mode = '';")->execute();
            },
            'serverRetryInterval' => 60
        ],
        'response' => [
            'formatters' => [
                'json' => [
                    'class' => 'yii\web\JsonResponseFormatter',
                    'prettyPrint' => YII_DEBUG,
                    'encodeOptions' => JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
                ],
            ],
        ],
        'queue' => [
            'class' => \yii\queue\redis\Queue::class,
            'as log' => \yii\queue\LogBehavior::class,
            // Other driver options,
            'redis' => 'redisQueue',
            'ttr' => 60 * 60, // Max time for job execution
            'attempts' => 1, // Max number of attempts
            'channel' => 'price'
        ],
        'queueFeed' => [
            'class' => \yii\queue\redis\Queue::class,
            'as log' => \yii\queue\LogBehavior::class,
            // Other driver options,
            'redis' => 'redisQueueFeed',
            'ttr' => 60 * 60, // Max time for job execution
//            'attempts' => 1, // Max number of attempts
            'channel' => 'feed'
        ],
        'queueProduct' => [
            'class' => \yii\queue\redis\Queue::class,
            'as log' => \yii\queue\LogBehavior::class,
            // Other driver options,
            'redis' => 'redisQueueProduct',
            'ttr' => 60 * 60, // Max time for job execution
            'attempts' => 1, // Max number of attempts
            'channel' => 'product'
        ],

        'queueSegment' => [
            'class' => \yii\queue\redis\Queue::class,
            'as log' => \yii\queue\LogBehavior::class,
            // Other driver options,
            'redis' => 'redisQueueSegment',
            'ttr' => 60 * 60, // Max time for job execution
            'attempts' => 1, // Max number of attempts
            'channel' => 'segment'
        ],

        'maintenanceMode' => [
            'class' => 'brussens\maintenance\MaintenanceMode',
            'enabled' => false,
            'title' => 'Временно не работает',
            'message' => 'Извините, ведуться технические работы! Бесплатно и круглостуточно 8 (800) 301-60-10',

        ],
        'cmsToolbar' =>
            [
                'class' => 'skeeks\cms\components\CmsToolbar',
                'enabled' => false,
                'isOpen' => false
            ],

        'urlManager' => [
            'suffix' => '/',

            'enablePrettyUrl' => true,
//            'enableStrictParsing' => false,
            'showScriptName' => false,


            'rules' => [
                ['pattern' => '/brands/<code>/<category:.*>', 'route' => 'brand/view', 'defaults' => ['category' => '']],
                ['pattern' => '/size-profile-report', 'route' => 'size-profile/report'],
                ['pattern' => '/size-profile', 'route' => 'size-profile/index'],
                ['pattern' => '/size-profile/<id>/<category:.*>', 'route' => 'size-profile/view', 'defaults' => ['category' => '']],
                ['pattern' => '/images', 'route' => 'images/view'],
                ['pattern' => '/support/<id>-<slug>', 'route' => 'content-element/index'],
                ['pattern' => '/promo', 'route' => 'promo/index'],
                ['pattern' => '/catalog/<slug:.+>', 'route' => 'category/view', 'defaults' => ['slug' => \common\helpers\Url::$catalogSlug]],
                ['pattern' => '/promo/page/<slug>/<category:.*>', 'route' => 'promo/view', 'defaults' => ['category' => '']],
                ['pattern' => '/promo/sales', 'route' => 'promo/view', 'defaults' => ['slug' => \common\helpers\Url::$saleSlug, 'category' => '']],

                'products/<id:\d+>-<code>' => 'products/view',

                'search' => 'search/index',
                'profile' => '/profile/index',
                'favorites' => '/profile/favorites',
                'profile/dialog/<id>-<theme>' => '/profile/dialog',

                '<_a:(login|signup|menu|developing)>' => 'site/<_a>',
                // переопределяем стандартные skeeks/cms/auth actions (пока просто назад, потом переделать на наш попап, когда будут ссылки)
                '~<_a:(login|logout|register|forget|reset-password)>' => 'site/back',
                [
                    'class' => 'common\components\urlRules\UrlRuleContentElement',
                ],
                [
                    'class' => 'common\components\urlRules\UrlRuleTree',
                ],
//                [
//                    'class' => 'skeeks\cms\savedFilters\SavedFiltersUrlRule',
//                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'api/v1/product',
                        'api/v2/product',
                        'api/lapa/v2/product',
                    ],
                    'pluralize' => false,
                    'extraPatterns' => [
                        'GET <id:\d+>/variations' => 'variations',
                        'GET <id:\d+>' => 'index',
                        'GET <id:\d+>/reviews' => 'reviews',
                    ]
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'api/v2/cart',
                    ],
                    'pluralize' => false,
//                    'extraPatterns' => [
//                    ]
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'api/v2/user',
                    ],
                    'pluralize' => false,
//                    'patterns' => [
//                        'GET,HEAD' => 'index'
//                    ]
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'api/v2/promo',
                    ],
                    'pluralize' => false,
                    'extraPatterns' => [
                        'item/<slug>' => 'item',
                        'item/<slug>/<action>' => 'item',
                    ]
                ],
            ],
            'normalizer' => [
                'class' => 'yii\web\UrlNormalizer',
                // use temporary redirection instead of permanent for debugging
                'action' => \yii\web\UrlNormalizer::ACTION_REDIRECT_PERMANENT,
//                'normalizeTrailingSlash' => false
            ],
        ],

        'breadcrumbs' => [
            'class' => 'common\components\Breadcrumbs',
        ],

        'appComponent' => [
            'class' => '\common\components\AppComponent',
        ],

        'abtest' => [
            'class' => '\common\components\AbTestComponent',
        ],

        'slider' => [
            'class' => '\common\components\slider\Slider',
        ],

//        'redis' => [
//            'class' => 'yii\redis\Connection',
//            'hostname' => 'localhost',
//            'port' => 6379,
//            'database' => 0,
//        ],


        'eventManager' => [
            'class' => 'bariew\eventManager\EventManager',
            'events' => [
                /*                'skeeks\cms\shop\models\ShopCmsContentElement' => [
                                    'afterInsert' => [
                                        ['modules\shopandshow\models\shop\SsShopProductPrice', 'changePrice']
                                    ],
                                    'afterUpdate' => [
                                        ['modules\shopandshow\models\shop\SsShopProductPrice', 'changePrice']
                                    ],
                                ],*/
                'skeeks\cms\models\Tree' => [
                    /*'afterUpdate' => [ //Сброс кэша по тегу tree
                    ],
                    'afterInsert' => [
                    ],*/
                ],
            ]
        ],

        'storage' => [
            'class' => 'common\components\Storage',
            'components' => [

                /** TODO: Разнести кластеры картинок: productImages, properties, etc */
                'images' => [
                    'class' => 'skeeks\cms\components\storage\ClusterLocal',
                    'priority' => 100,
                    'rootBasePath' => '/mnt/disk/site_images/uploads/all',
                    'publicBaseUrl' => '/uploads/all'
                ],
            ],
        ],

        'session' => [
            'class' => 'yii\redis\Session',
            'keyPrefix' => 'ss_ses_',
        ],

        'money' => [
            'class' => 'common\components\CommonMoney',
        ],


        'assetManager' => [
            'linkAssets' => true,
//            'appendTimestamp' => true,
            'converter' => [
                'class' => 'yii\web\AssetConverter',
                'commands' => [
                    'scss' => ['css', 'sass --compass -C -E utf-8 {from} {to}'],
                ],
            ],
        ],

        'settings' => [
            'class' => 'common\components\SettingsComponent',
        ],

        'cmsAgent' => [
            'onHitsEnabled' => false
        ],

        'cmsSearch' => [
            'class' => 'common\components\Search',
        ],

        /*        'savedFilters' => [
                    'handlers' =>
                        [
                            'common\components\SavedFiltersHandler' =>
                                [
                                    'class' => 'common\components\SavedFiltersHandler'
                                ]
                        ]
                ],*/

        'cms' => [
            'relatedHandlers' => [
                'skeeks\cms\rhExtra\RelatedHandlerExtra' =>
                    [
                        'class' => 'skeeks\cms\rhExtra\RelatedHandlerExtra'
                    ],
//                'skeeks\cms\savedFilters\RelatedHandlerSavedFilter' =>
//                    [
//                        'class' => 'skeeks\cms\savedFilters\RelatedHandlerSavedFilter'
//                    ]
            ],
        ],

        'redirect' => [
            'class' => 'common\components\SaSRedirect',
        ],

        'promoListener' => [
            'class' => 'common\components\promo\PromoListener',
        ],

        'authClientCollection' => [
            'class' => 'skeeks\cms\authclient\CmsAuthClientCollection',
            'clients' => [
                //clients configs
            ]
        ],

        'authClientSettings' => [
            'class' => 'skeeks\cms\authclient\CmsAuthClientSettings',
        ],

        'mobileDetect' => [
            'class' => '\skeeks\yii2\mobiledetect\MobileDetect'
        ],


        'imaging' => [
            'class' => '\common\components\images\Imaging',
        ],

        'shop' => [
            'class' => 'modules\shopandshow\components\shop\ShopComponent',
        ],

        'cmsExport' => [
            'handlers' =>
                [
                    'modules\shopandshow\components\export\ExportSitemapHandler' => [
                        'class' => 'modules\shopandshow\components\export\ExportSitemapHandler'
                    ],
                    'modules\shopandshow\components\export\ExportShopYandexMarketNewHandler' => [
                        'class' => 'modules\shopandshow\components\export\ExportShopYandexMarketNewHandler'
                    ],
                    'modules\shopandshow\components\export\ExportShopYandexMarketHandler' => [
                        'class' => 'modules\shopandshow\components\export\ExportShopYandexMarketHandler'
                    ],
                    'modules\shopandshow\components\export\ExportShopYandexMarketFlashPriceHandler' => [
                        'class' => 'modules\shopandshow\components\export\ExportShopYandexMarketFlashPriceHandler'
                    ],
                    'modules\shopandshow\components\export\ExportShopGoogleMerchantCenterHandler' => [
                        'class' => 'modules\shopandshow\components\export\ExportShopGoogleMerchantCenterHandler'
                    ],
                    'modules\shopandshow\components\export\ExportShopPriceRuHandler' => [
                        'class' => 'modules\shopandshow\components\export\ExportShopPriceRuHandler'
                    ],
                    'modules\shopandshow\components\export\ExportShopYandex2Handler' => [
                        'class' => 'modules\shopandshow\components\export\ExportShopYandex2Handler'
                    ],
                    'modules\shopandshow\components\export\ExportShopBlizkoHandler' => [
                        'class' => 'modules\shopandshow\components\export\ExportShopBlizkoHandler'
                    ],
                    'modules\shopandshow\components\export\ExportShopAdmitadHandler' => [
                        'class' => 'modules\shopandshow\components\export\ExportShopAdmitadHandler'
                    ],
                    \modules\shopandshow\components\export\CriteoHandler::class => [
                        'class' => \modules\shopandshow\components\export\CriteoHandler::class
                    ],
                    \modules\shopandshow\components\export\FlocktoryHandler::class => [
                        'class' => \modules\shopandshow\components\export\FlocktoryHandler::class
                    ],
                    \modules\shopandshow\components\export\RetailRocketHandler::class => [
                        'class' => \modules\shopandshow\components\export\RetailRocketHandler::class
                    ],
                    \modules\shopandshow\components\export\AdmitadCompareHandler::class => [
                        'class' => \modules\shopandshow\components\export\AdmitadCompareHandler::class
                    ],
                    YandexTurboPageHandler::class => [
                        'class' => YandexTurboPageHandler::class
                    ],
                    'modules\shopandshow\components\export\ExportShopYandexBlagofHandler' => [
                        'class' => 'modules\shopandshow\components\export\ExportShopYandexBlagofHandler'
                    ],
                    'modules\shopandshow\components\export\ExportShopGoogleMerchantFlashPriceHandler' => [
                        'class' => 'modules\shopandshow\components\export\ExportShopGoogleMerchantFlashPriceHandler'
                    ]
                ]
        ],

//        'sms' => [
//            'class' => 'common\components\sms\Sms',
//            'services' => [
//                // https://smsinfostat.zagruzka.com/partner/
//                'zagruzka' => [
//                    'class' => 'common\components\sms\services\ZagruzkaComService',
//                    'login' => 'shopandshow_mob',
//                    'password' => 'LFT2AX23', //989GHCP1
//                    'source' => 'ShopAndShow',
//                    'order' => 1,
//                ],
//            ]
//        ],

        'sms' => [
            'class' => '\common\components\sendSms\SmsDanytechService',
            'classLogger' => '\common\components\sendSms\SmsLogger',
            'serviceId' => '1270',
            'pass' => 'tu6w4X8V',
            'source' => 'ShopAndShow',
            'flash' => 0
        ],

        'captcha' => [
            'class' => '\common\components\captcha\GoogleCaptcha',
            'publicKey' => '6LfHoNUUAAAAAM3Ozz64SBD1aQq29lH6c1v74g1w',
            'secretKey' => '6LfHoNUUAAAAANx9U_i1fun9MVTyXo2Cg2cNi6WT'
        ],


        'sendSms' => [
            'class' => '\common\components\sendSms\SmsDanytechService',
            'serviceId' => '23423423',
            'pass' => 'reverrefvre',
            'source' => 'ShopAndShow',
            'flash' => 0
        ],

        'telegramBot' => [
            'class' => 'SonkoDmitry\Yii\TelegramBot\Component',
            'apiToken' => '465657657:AAESGXlOtB010iwhrs9g9jDkJutlJx5Hg7c',
        ],
        'telegramBotEcom' => [
            'class' => 'SonkoDmitry\Yii\TelegramBot\Component',
            'apiToken' => '641634820:AAEH5pUoW0A4lYTKKoqThiUMyLGS95E7FaQ',
        ],
        'dataLayer' => [
            'class' => \common\components\dataLayer\DataLayer::class,
        ],

        /*'dataLayer' => [
            'class' => 'dekar91\datalayer\DataLayer',
            'options' => [
                'autoPublish' => true,
                'observers' => ['ec' => ['class' => \dekar91\datalayer\DataLayerEc::class]],
                'customEvents' => [
                    ['.btn-checkout', 'click' , ['event' => 'checkoutEvent']],
                ]
            ]
        ],*/
        'dadataSuggest' => [
            'class' => 'modules\shopandshow\components\dadata\DadataSuggestComponent',
            'authorization_token' => '0ec153fc7dee9a6bb9e3387d3d65819f2d5b3b47'
        ],
        'dadataSuggestApi' => [
            'class' => 'modules\shopandshow\components\dadata\DadataSuggestApi',
        ],
        'morpherAz' => [
            'class' => \common\components\MorpherAz::class,
            'token' => '9TMqUQp0g5'
        ],

        'smscHlr' => [
            'class' => 'common\components\SmscHlr',
            'httpClient' => [
                'class' => 'yii\httpclient\Client',
                'transport' => 'yii\httpclient\CurlTransport',
            ],
            'baseUrl' => 'https://smsc.ru/sys/',
            'username' => 'Shopandshow.ru',
            'password' => '7Dn2Da',
            'delayBeforeFirstCheck' => 1000, //micro-sec
            'delayBetweenStatusChecks' => 1000, //micro-sec
            'statusCheckAttemptsNum' => 2,
        ],
    ],

    'modules' => [
        'shopandshow' => [
            'class' => 'modules\shopandshow\ShopAndShowModule',
        ],

//        'savedFilters' => [
//            'class' => 'skeeks\cms\savedFilters\SavedFiltersModule',
//        ],

        'api' => [
            'basePath' => '@modules/api',
            'class' => 'modules\api\ApiModule',
        ],

//        'delivery' => [ // Выпилить! не заюзали!!
//            'class' => 'uranum\delivery\module\Module',
//            'params' => [
//                'locationFrom' => 'Москва',        // Город отправки
//                /** Параметры для postcalc (Почта России) */
//                'siteName' => 'shopandshow.ru',            // Название сайта (ОБЯЗАТЕЛЬНЫЙ)
//                'email' => '88kg@mail.ru',       // Контактный email. Самый принципиальный параметр для postcalc (ОБЯЗАТЕЛЬНЫЙ)
//                'contactName' => 'Alexandr_Kovalenko',   // Контактное лицо. Имя_фамилия, только латиница через подчеркивание (НЕобязательный)
//                'insurance' => 'f',                  // База страховки - полная f или частичная p (НЕобязательный)
//                'round' => 1,                    // Округление вверх. 0.01 - округление до копеек, 1 - до рублей (НЕобязательный)
//                'pr' => 0,                    // Наценка в рублях за обработку заказа (НЕобязательный)
//                'pk' => 0,                    // Наценка в рублях за упаковку одного отправления (НЕобязательный)
//                'encode' => 'utf-8',              // Кодировка - utf-8 или windows-1251 (НЕобязательный)
//                'sendDate' => 'now',                // Дата - в формате, который понимает strtotime(), например, '+7days','10.10.2020' (НЕобязательный)
//                'respFormat' => 'json',               // Формат ответа (html, php, arr, wddx, json, plain) (НЕобязательный)
//                'country' => 'Ru',                 // Страна (список стран: http://postcalc.ru/countries.php) (НЕобязательный)
//                'servers' => [
//                    'api.postcalc.ru',                // После тестовых запросов включить "боевой" сервер (ОБЯЗАТЕЛЬНО)
//                    'test.postcalc.ru',
//                ],                                      // Список серверов для беcплатной версии (ОБЯЗАТЕЛЬНЫЙ)
//                'httpOptions' => [
//                    'http' => [
//                        'header' => 'Accept-Encoding: gzip',
//                        'timeout' => 5,              // Время ожидания ответа сервера в секундах
//                        'user_agent' => 'PostcalcLight_1.04 ' . phpversion(),
//                    ],
//                ],                                      // Опции запроса (НЕобязательный)
//            ],
//
//            'components' => [
//                'post_naloj' => [
//                    'class' => 'uranum\delivery\services\PostNalojDelivery',
//                ],
//                'ss_courier' => [
//                    'class' => 'modules\shopandshow\services\CourierDelivery',
//                ],
//            ]
//        ],

        'dadataSuggest' => [
            'class' => 'skeeks\cms\dadataSuggest\CmsDadataSuggestModule',
        ]

        /*        'authclient' => [
                    'class' => 'skeeks\cms\authclient\CmsAuthClientModule',
                ]*/
    ],
];

//if (isset($_COOKIE[COOKIE_NAME_IS_SHOW_DEBUG_PANEL])) {
//    $config['bootstrap'][] = 'debug';
//    $config['modules']['debug'] = [
//        'class' => 'yii\debug\Module',
//        'allowedIPs' => ['*'],
//    ];
//}

return $config;
