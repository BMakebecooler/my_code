<?php

namespace modules\shopandshow\controllers\stock;

use modules\shopandshow\models\shop\stock\forms\SalesStock;
use modules\shopandshow\models\shop\stock\forms\SalesStockPeriod;
use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use skeeks\cms\modules\admin\traits\AdminModelEditorStandartControllerTrait;

/**
 * Class StatisticsController
 * @package modules\shopandshow\controllers
 */
class StatisticsController extends AdminModelEditorController
{

    use AdminModelEditorStandartControllerTrait;

    public function init()
    {
        $this->modelClassName = SalesStock::className();
        $this->name = 'Сток';
        $this->modelShowAttribute = "id";

        parent::init();
    }

    /**
     * Статистика продаж стока в разрезе дней
     * @return mixed|string
     */
    public function actionByDay()
    {
        $model = new SalesStock();

        if (\Yii::$app->request->isPost) {
            if (!\Yii::$app->request->post('ajax')) {
                if (!$model->load(\Yii::$app->request->post())) {
                    return print_r($model->getErrors(), true);
                }
            }
        }

        return $this->render('by-day', [
            'model' => $model,
        ]);
    }

    /**
     * Статистика продаж стока за периоды
     * @return mixed|string
     */
    public function actionByPeriod()
    {
        $model = new SalesStockPeriod();

        if ($data = \Yii::$app->request->get('SalesStockPeriod')) {
            $model->load(\Yii::$app->request->get());
            if (!$model->validate()) {
                return print_r($model->getErrors(), true);
            }
        }

        return $this->render('by-period', [
            'model' => $model,
        ]);
    }
}
