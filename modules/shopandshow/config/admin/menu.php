<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 15.04.2016
 */
return
[
    'exportImport' =>
    [
        'items' =>
            [
                'import' =>
                    [
                        'label'     => \Yii::t('skeeks/export', 'Импорт'),
                        'img'       => ['\skeeks\cms\export\assets\ExportAsset', 'icons/export.png'],

                        'items' =>
                            [
/*                                [
                                    'label'     => \Yii::t('skeeks/export', 'Импорт товаров на НГ 2018'),
                                    'img'       => ['\skeeks\cms\export\assets\ExportAsset', 'icons/export.png'],
                                    'url'       => ['shopandshow/admin-import/newyear2018'],
                                ],*/
                                [
                                    'label'     => \Yii::t('skeeks/export', '8 Марта'),
                                    'img'       => ['\skeeks\cms\export\assets\ExportAsset', 'icons/export.png'],
                                    'url'       => ['shopandshow/admin-import/products-from-csv'],
                                ],
                            ],
                    ],
            ]
    ],
    'shopandshow' =>
    [
        'priority'  => 400,
        'label'     => 'Shop & show',
        'img'       => ['\modules\shopandshow\assets\ShopAndShowAsset', 'icons/logo.png'],
        'items' =>
        [
            [
                'label'     => 'Рассылки',
                //'url'       => ['shopandshow/main'],
                'items'     => [
                    [
                        'label'     => 'Выполненные рассылки',
                        'url'       => ['shopandshow/mail/admin-mail-dispatch'],
                        //'img'       => ['\skeeks\cms\shop\assets\Asset', 'icons/orders.png'],
                    ],
                    [
                        'label'     => 'Шаблоны рассылок',
                        'url'       => ['shopandshow/mail/admin-mail-template'],
                        //'img'       => ['\skeeks\cms\shop\assets\Asset', 'icons/orders.png'],
                    ],
                    [
                        'label'     => 'Темы рассылок',
                        'url'       => ['shopandshow/mail/admin-mail-subject'],
                        //'img'       => ['\skeeks\cms\shop\assets\Asset', 'icons/orders.png'],
                    ],
                    [
                        'label'     => 'Конструктор рассылок',
                        'url'       => ['shopandshow/mail/admin-schedule/grid'],
                        //'img'       => ['\skeeks\cms\shop\assets\Asset', 'icons/orders.png'],
                    ],

                ]
            ],
            [
                'label'     => 'Баннеры',
                'img'       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/content.png'],

                'items'     => [
                    [
                        'label'     => 'Баннеры',
                        'url'       => ['shopandshow/shares/admin-shares/index'],
                    ],
                    [
                        'label'     => 'Баннерная сетка',
                        'url'       => ['shopandshow/shares/admin-shares/grid'],
                    ],
                    [
                        'label'     => 'Типы баннеров',
                        'url'       => ['shopandshow/shares/admin-shares-types/index'],
                    ],
                    [
                        'label'     => 'Плашки',
                        'url'       => ['shopandshow/shares/admin-badges/index'],
                    ]
                    /*[
                        'label'     => 'Перезалить баннеры',
                        'url'       => ['shopandshow/shares/admin-shares/reload-banners'],
                    ],*/
                ]
            ],

            [
                'label'     => 'Вопрос - ответ',
                'items'     => [
                    [
                        'label'     => 'Вопрос - ответ',
                        'url'       => ['shopandshow/questions/questions'],
                    ],
                    [
                        'label'     => 'Адресаты',
                        'url'       => ['shopandshow/questions/questions-email'],
                    ],
                ]
            ],

            [
                'label'     => 'Статистика',
                'img'       => ['\skeeks\cms\shop\assets\Asset', 'icons/reports.png'],
                'items'     => [
                    [
                        'label'     => 'Топ-50 продаж за вчерашний день',
                        'url'       => ['shopandshow/statistics/yesterday-top'],
                        //'img'       => ['\skeeks\cms\shop\assets\Asset', 'icons/orders.png'],
                    ],
                    [
                        'label'     => 'RealTime аналитика эфира',
                        'url'       => ['shopandshow/statistics/realtime-efir'],
                        //'img'       => ['\skeeks\cms\shop\assets\Asset', 'icons/orders.png'],
                    ],
                    [
                        'label'     => 'Брошенные корзины',
                        'url'       => ['shopandshow/statistics/abandoned-baskets'],
                        //'img'       => ['\skeeks\cms\shop\assets\Asset', 'icons/orders.png'],
                    ],
                    [
                        'label'     => 'Статиситка по промокодам',
                        'url'       => ['shopandshow/statistics/coupons-promos'],
                    ],
                    [
                        'label'     => 'Статиситка по баннерам',
                        'items'       => [
                            [
                                'label'     => 'Ежедневная',
                                'url'       => ['shopandshow/statistics/banners/'],
                            ],
                            [
                                'label'     => 'По типам',
                                'url'       => ['shopandshow/statistics/banners-by-type/'],
                            ],
                        ]
                    ],
                    [
                        'label'     => 'Мониторинг подписчиков',
                        'items'       => [
                            [
                                'label'         => 'Отчет',
                                'url'       => ['shopandshow/statistics/subscribers-report'],
                            ],
                            [
                                'label'         => 'Список',
                                'url'       => ['shopandshow/statistics/subscribers-monitoring'],
                            ]
                        ],
                    ],
                    [
                        'label'     => 'Сток',
                        'items'       => [
                            [
                                'label'     => 'Продажи стока по дням',
                                'url'       => ['shopandshow/stock/statistics/by-day'],
                            ],
                            [
                                'label'     => 'Продажи стока по периодам',
                                'url'       => ['shopandshow/stock/statistics/by-period'],
                            ],
                            [
                                'label'     => 'Файлы стоков',
                                'url'       => ['shopandshow/stock/files'],
                            ],
                        ]
                    ],
                    [
                        'label'     => 'Время авторизации',
                        'url'       => ['shopandshow/statistics/auth-pass-time'],
                    ],

                    [
                        'label'     => 'Воронка продаж по статусам',
                        'url'       => ['shopandshow/statistics/sales-funnel-by-status'],
                    ],

                    [
                        'label'     => 'Выкуп/не выкуп заказов',
                        'url'       => ['shopandshow/statistics/user-complete-orders'],
                    ],

                    [
                        'label'     => 'Ранжирование товаров',
                        'url'       => ['shopandshow/statistics/product-range'],
                    ],

                ]
            ],

            [
                'label'     => 'Мониторинг дня',
                'items'     => [
                    [
                        'label'     => 'Мониторинг продаж с сайта',
                        'url'       => ['shopandshow/monitoringday/plan/show'],
                        //'img'       => ['\skeeks\cms\shop\assets\Asset', 'icons/orders.png'],
                    ],
                    [
                        'label'     => 'Еженедельный отчет',
                        'url'       => ['shopandshow/monitoringday/plan/show-weekly'],
                        //'img'       => ['\skeeks\cms\shop\assets\Asset', 'icons/orders.png'],
                    ],
                    [
                        'label'     => 'Импорт плана на месяц',
                        'url'       => ['shopandshow/monitoringday/plan/import'],
                        //'img'       => ['\skeeks\cms\shop\assets\Asset', 'icons/orders.png'],
                    ],
                    [
                        'label'     => 'Основные графики',
                        'url'       => ['shopandshow/monitoringday/plan/show-graphs'],
                        //'img'       => ['\skeeks\cms\shop\assets\Asset', 'icons/orders.png'],
                    ],
                    [
                        'label'     => 'Продажи по рубрикам и ПЭ',
                        'url'       => ['shopandshow/monitoringday/plan/show-tables'],
                        //'img'       => ['\skeeks\cms\shop\assets\Asset', 'icons/orders.png'],
                    ],
                    [
                        'label'     => 'Продажи ПЭЧ',
                        'url'       => ['shopandshow/monitoringday/plan/sales-efir'],
                        //'img'       => ['\skeeks\cms\shop\assets\Asset', 'icons/orders.png'],
                    ],
                    [
                        'label'     => 'Продажи стока',
                        'url'       => ['shopandshow/monitoringday/plan/sales-stock'],
                        //'img'       => ['\skeeks\cms\shop\assets\Asset', 'icons/orders.png'],
                    ],
                ]
            ],

            [
                'label'     => 'Внешние сервисы',
                'items'     => [
                    [
                        'label'     => 'Отчеты Gt-metrix',
                        'url'       => ['shopandshow/services/gtmetrix'],
                        //'img'       => ['\skeeks\cms\shop\assets\Asset', 'icons/orders.png'],
                    ],
                    [
                        'label'     => 'Отправленные смс',
                        'url'       => ['shopandshow/services/sms'],
                        //'img'       => ['\skeeks\cms\shop\assets\Asset', 'icons/orders.png'],
                    ]
                ]
            ],

            [
                'label'     => 'Инструменты',
                'img'       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/tools.png'],
                'items'     => [
                    [
                        'label'     => 'Редиректы',
                        'img'       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/www.png'],
                        'url'       => ['shopandshow/tools/redirects/index'],
                    ],

                ]
            ],

/*            [
                'label'     => 'Сбросить OPcache',
                'url'       => ['shopandshow/services/cache-clear'],
            ],*/

            /*[
                'label'     => 'Настройки',
                'img'       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/settings-big.png'],

                'items' =>
                [
                    [
                        'label'     => 'Статусы заказов',
                        'url'       => ['shopandshow/admin-order-status'],
                    ],

                    [
                        'label'     => 'Настройки проекта',
                        'url'       => ['cms/admin-settings', 'component' => 'shopandshow\components\ShopAndShowSettings'],
                        'img'       => ['\skeeks\cms\modules\admin\assets\AdminAsset', 'images/icons/settings-big.png'],
                        'activeCallback'       => function(\skeeks\cms\modules\admin\helpers\AdminMenuItem $adminMenuItem)
                        {
                            return (bool) (\Yii::$app->request->getUrl() == $adminMenuItem->getUrl());
                        },
                    ],

                ]
            ],*/
        ]
    ],
];