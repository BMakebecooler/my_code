<?php
return [
    'components' => [
        'user' => [
            'class' => 'yii\web\User',
            'identityClass' => 'common\models\user\User',
            //'enableAutoLogin' => true,
        ],
    ]
];
