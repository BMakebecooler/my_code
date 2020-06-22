<?php
return [
    'params' => include_once __DIR__ . '/params.php',
    'components' => [
        'urlManager' => [
            'baseUrl' => 'http://newdev.shopandshow.ru/'
        ]
    ],
// Загрузка фикстур из кастомного неймспейса
//    'controllerMap' => [
//        'fixture' => [
//            'class' => 'yii\console\controllers\FixtureController',
//            'namespace' => 'tests\codeception\common\fixtures',
//        ],
//    ],
//    'aliases' => [
//        '@tests/codeception/common/fixtures' => 'tests/codeception/common/fixtures/',
//    ],
];