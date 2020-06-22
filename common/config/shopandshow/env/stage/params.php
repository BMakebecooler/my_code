<?php
return [

    'storage' => [

        /** Связки ClusterId для хранения картинок */
        'clusters' => [
            'element_images' => 'element_images',
            'property_images' => 'property_images',
        ],

        /** Путь до картинок. Локальная папка или url */
        'vendorImagesPath' => '/mnt/upload',
        'vendorImagesGifPath' => '/mnt',

        /** Путь до картинок kfss. Локальная папка или url */
        'kfssImagesPath' => '/mnt/Images',
    ],

    'hosts' => [

        'cdn' => [
            'schema' => 'http',
            'prefix' => 'kfss',
            'domain' => 'shopandshow.ru',
            'counter' => false
        ],

    ],

//    'webSocketUrl' => 'wss://new.shopandshow.ru:8080',
	'webSocketUrl' => 'wss://ws.shopandshow.ru',
    'getresponse' => [
        'tokens' => [
            'subscription' => '9',
            'orders' => 'N'
        ]
    ]
];
