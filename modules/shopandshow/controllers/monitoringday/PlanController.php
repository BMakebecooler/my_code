<?php
namespace modules\shopandshow\controllers\monitoringday;

use modules\shopandshow\components\task\MonitoringWeeklyTaskHandler;
use modules\shopandshow\models\monitoringday\Marginality;
use modules\shopandshow\models\monitoringday\Plan;
use modules\shopandshow\models\monitoringday\PlanImport;
use modules\shopandshow\models\monitoringday\PlanTables;
use modules\shopandshow\models\monitoringday\PlanTablesXlsx;
use modules\shopandshow\models\monitoringday\PlanWeekly;
use modules\shopandshow\models\monitoringday\SalesEfir;
use modules\shopandshow\models\shop\stock\forms\SalesStock;
use modules\shopandshow\models\task\SsTask;
use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use skeeks\cms\modules\admin\traits\AdminModelEditorStandartControllerTrait;
use yii\helpers\ArrayHelper;

/**
 * Class PlanController
 * @package modules\shopandshow\controllers
 */
class PlanController extends AdminModelEditorController
{
    use AdminModelEditorStandartControllerTrait;

    public function init()
    {
        $this->modelClassName = \modules\shopandshow\models\monitoringday\Plan::className();
        $this->name = 'План';
        $this->modelShowAttribute = "id";

        parent::init();
    }

    public function actions()
    {
        $actions = parent::actions();
        ArrayHelper::remove($actions, 'create');
        ArrayHelper::remove($actions, 'delete');

        return $actions;
    }

    public function actionImport()
    {
        $model = new PlanImport();
        $message = '';
        if (\Yii::$app->request->isPost) {
            $rr = new RequestResponse();
            if (!\Yii::$app->request->post('ajax')) {
                if ($model->load(\Yii::$app->request->post())) {
                    $model->file = \yii\web\UploadedFile::getInstance($model, 'file');

                    if ($model->file && $model->validate()) {
                        $message = $model->import();
                    } else {
                        $message = 'Файл не загружен: ' . print_r($model->getErrors(), true);
                    }
                } else {
                    $message = print_r($model->getErrors(), true);
                }
            }
        }

        return $this->render('/monitoringday/import', [
            'message' => $message,
            'model' => $model,
        ]);
    }

    public function actionShow()
    {
        $model = new Plan;

        if (\Yii::$app->request->isPost) {
            $data = \Yii::$app->request->post('Plan');
            $attributes = ['date' => $data['date']];
            $model->setAttributes($attributes);
        }

        return $this->render('/monitoringday/show', [
            'model' => $model,
        ]);
    }

    /**
     * Отчет по маржинальности
     * @return string
     */
    public function actionMarginality()
    {
        $marginality = new Marginality();

        if (\Yii::$app->request->isPost) {
            $data = \Yii::$app->request->post('Marginality');
            $attributes = ['date' => $data['date']];
            $marginality->setAttributes($attributes);
        }

        return $this->render('/monitoringday/marginality', [
            'model' => $marginality,
        ]);
    }

    public function actionShowWeekly()
    {
        $model = new PlanWeekly;

        if (\Yii::$app->request->isPost) {
            $rr = new RequestResponse();
            if (!\Yii::$app->request->post('ajax')) {
                if (!$model->load(\Yii::$app->request->post())) {
                    return print_r($model->getErrors(), true);
                }
            }
        }

        if ($model->submitType == $model::SUBMIT_HTML) {
            $model->initData();
        } elseif ($model->submitType == $model::SUBMIT_EMAIL) {
            if (!filter_var($model->email, FILTER_VALIDATE_EMAIL)) {
                $model->addError('email', 'Неверно указан email');
            } else {
                $taskResult = SsTask::createNewTask(
                    MonitoringWeeklyTaskHandler::className(),
                    ['date_from' => $model->date_from, 'date_to' => $model->date_to, 'email' => $model->email]
                );

                if ($taskResult) {
                    \Yii::$app->session->setFlash('success', 'Задание отправки на E-mail успешно сформировано');
                } else {
                    \Yii::$app->session->setFlash('error', 'Не удалось создать задание отправки на E-mail');
                }
            }
        }

        return $this->render('/monitoringday/show-weekly', [
            'model' => $model
        ]);
    }

    public function actionShowGraphs()
    {
        $model = new Plan;

        if (\Yii::$app->request->isGet && !empty(\Yii::$app->request->get('show'))) {
            $rr = new RequestResponse();
            if (!\Yii::$app->request->get('ajax')) {
                if (!$model->load(\Yii::$app->request->get())) {
                    return print_r($model->getErrors(), true);
                }
            }
        }

        return $this->render('/monitoringday/show-graphs', [
            'model' => $model,
        ]);
    }

    public function actionShowTables()
    {
        $model = new PlanTables();
        $submitType = \Yii::$app->request->get('submitType');

        if (\Yii::$app->request->isGet && !empty($submitType)) {
            $rr = new RequestResponse();
            if (!\Yii::$app->request->get('ajax')) {
                if (!$model->load(\Yii::$app->request->get())) {
                    return print_r($model->getErrors(), true);
                }

                if ($model->useOffset) {
                    $model->dateTimeOffset = HOUR_8;
                }
            }
        }

        if ($submitType == 'export') {
            $xlsx = new PlanTablesXlsx($model);

            return $xlsx->download();
        }

        return $this->render('/monitoringday/show-tables', [
            'model' => $model,
        ]);
    }

    public function actionSalesEfir()
    {
        $model = new SalesEfir();

        if (\Yii::$app->request->isPost) {
            $rr = new RequestResponse();
            if (!\Yii::$app->request->post('ajax')) {
                if (!$model->load(\Yii::$app->request->post())) {
                    return print_r($model->getErrors(), true);
                }
            }
        }

        return $this->render('/monitoringday/sales-efir', [
            'model' => $model,
        ]);
    }

    public function actionSalesStock()
    {
        $model = new SalesStock();

        if (\Yii::$app->request->isPost) {
            $rr = new RequestResponse();
            if (!\Yii::$app->request->post('ajax')) {
                if (!$model->load(\Yii::$app->request->post())) {
                    return print_r($model->getErrors(), true);
                }
            }
        }

        return $this->render('/monitoringday/sales-stock', [
            'model' => $model,
        ]);
    }
}
