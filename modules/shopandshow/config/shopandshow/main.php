<?php

return \yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/../main.php',
    [
        'components' => [
            'mediaPlanApi' => [
                'class' => '\modules\shopandshow\components\api\MediaPlanApi',
                'host' => 'mp2.shopandshow.ru/api/'
            ],
        ],
    ]
);