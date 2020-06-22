<?php

namespace modules\shopandshow\controllers;

use modules\shopandshow\models\services\GtMetrix;
use modules\shopandshow\models\services\Sms;
use skeeks\cms\modules\admin\controllers\AdminController;

/**
 * Class ServicesController
 * @package modules\shopandshow\controllers
 */
class ServicesController extends AdminController
{

    public function actionCacheClear()
    {
        if (opcache_reset()) {
            var_dump('ok');
        } else {
            var_dump('no');
        }
    }

    public function actionGtmetrix()
    {
        $model = new GtMetrix();

        if (\Yii::$app->request->isGet && $model->load(\Yii::$app->request->get())) {

        }

        return $this->render('gtmetrix', [
            'model' => $model
        ]);
    }

    public function actionSms()
    {
        $model = new Sms();

        if (\Yii::$app->request->isGet && $model->load(\Yii::$app->request->get())) {

        }

        return $this->render('sms-report', [
            'model' => $model
        ]);
    }
}
