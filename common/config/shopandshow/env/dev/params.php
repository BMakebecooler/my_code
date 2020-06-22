<?php
return [
    'storage' => [

        /** Связки ClusterId для хранения картинок */
        'clusters' => [
            'element_images' => 'element_images',
            'property_images' => 'property_images',
        ],

        /** Путь до картинок. Локальная папка или url */
        'vendorImagesPath' => '/upload'

    ],
    'hosts' => [
        'cdn' => [
            'schema' => 'http',
            'prefix' => 'static',
            'domain' => 'shopandshow.ru',
            'counter' => ''
        ]
    ]

];
