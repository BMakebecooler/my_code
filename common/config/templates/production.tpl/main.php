<?php
return [
    'params' => include_once __DIR__.'/params.php',
    'components' => [
        'db' => include_once __DIR__.'/db.php',
        'front_db' => include_once __DIR__.'/front_db.php',
        'urlManager' => [
            'baseUrl' => 'https://shopandshow.ru/'
        ]
    ]
];
