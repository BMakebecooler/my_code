<?php
return [

    'storage' => [

        /** Связки ClusterId для хранения картинок */
        'clusters' => [
            'element_images' => 'images',
            'property_images' => 'images',
        ],

        /** Путь до картинок. Локальная папка или url */
        'vendorImagesPath' => '/upload',

        /** Путь до картинок kfss. Локальная папка или url */
        'kfssImagesPath' => '/mnt/Images',

    ],
    'hosts' => [

        'cdn' => [
            'schema' => 'https',
            'prefix' => 'img',
            'domain' => 'shopandshow.ru',
            'counter' => true
        ],

    ],

    'emails' => [
        'hr' => 'hr@shopandshow.ru',
    ],
    'phone' => [
        'code' => '8 (800)',
        'number' => '301-60-10',
    ],

    'phone_2' => [
        'code' => '8 (800)',
        'number' => '775-11-33',
    ],

    'getresponse' => [
        'tokens' => [
            'subscription' => 'C',
            'orders' => 'R'
        ]
    ]

];
