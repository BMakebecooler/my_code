<?php

namespace common\components;

use Yii;

class Request extends \yii\web\Request
{
    public $noCsrfRoutes = [];

    public function validateCsrfToken($token = null)
    {
        if (
            $this->enableCsrfValidation &&
            in_array(Yii::$app->getUrlManager()->parseRequest($this)[0], $this->noCsrfRoutes)
        ) {
            return true;
        }

        return parent::validateCsrfToken();
    }
}


?>