<?php
return [

    'components' => [

        'shopAndShowSettings' => [
            'class' => '\modules\shopandshow\components\ShopAndShowSettings'
        ],

        'shopAndShow' => [
            'class' => '\modules\shopandshow\components\ShopAndShowComponent'
        ],

        'mediaPlanApi' => [
            'class' => '\modules\shopandshow\components\api\MediaPlanApi'
        ],

        'shares' => [ //Акции
            'class' => '\modules\shopandshow\components\shares\Share'
        ],

        'front' => [ //Битриксовый фронт
            'class' => '\modules\shopandshow\components\front\Front'
        ],

        'metrics' => [
            'class' => '\modules\shopandshow\components\api\MetricsApi'
        ],

        'recoApi' => [
            'class' => '\modules\shopandshow\components\api\RecoApi'
        ],



        /*'urlManager' => [
            'rules' => [
                '~child-<_a:(checkout|finish)>' => 'shopandshow/cart/<_a>',
                '~child-order/<_a>' => 'shopandshow/order/<_a>',
            ]
        ],*/
        ],
];