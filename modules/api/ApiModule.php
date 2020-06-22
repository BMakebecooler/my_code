<?php

namespace modules\api;

class ApiModule extends \yii\base\Module
{

    public $defaultController = 'products';

    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'modules\api\controllers';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

//        \Yii::configure(\Yii::$app, require(__DIR__ . '/config/main.php'));
    }
}