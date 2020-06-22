<?php
namespace modules\shopandshow\controllers;

use modules\shopandshow\models\import\ManDay;
use modules\shopandshow\models\import\NewyearUploadForm;
use modules\shopandshow\models\shares\SsShare;
use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use skeeks\cms\modules\admin\traits\AdminModelEditorStandartControllerTrait;
use Yii;

/**
 * Class AdminImportController
 * @package modules\shopandshow\controllers
 */
class AdminImportController extends AdminModelEditorController
{
    use AdminModelEditorStandartControllerTrait;

    public function init()
    {
        $this->modelClassName = SsShare::className();
        $this->name = 'Импорт';
        $this->modelShowAttribute = "id";

        parent::init();
    }

    /**
     * Импорт товаров для  нового года
     * @return string
     */
    public function actionNewyear2018()
    {
        return $this->importCsvByModel(new NewyearUploadForm(), NewyearUploadForm::getTrees());
    }

    /**
     * Импорт товаров для  нового года
     * @return string
     */
    public function actionManday()
    {
        return $this->importCsvByModel(new ManDay(), ManDay::getTrees());
    }

    /**
     * Импорт товаров из csv
     * @return string
     */
    public function actionProductsFromCsv()
    {
        return $this->importCsvByModel(new ManDay(), ManDay::getTrees());
    }

    /**
     * @param $model
     * @param $categories
     * @return string
     */
    protected function importCsvByModel($model, $categories)
    {
        $message = '';
        if (\Yii::$app->request->isPost) {
            $rr = new RequestResponse();
            if (!\Yii::$app->request->post('ajax')) {

                if ($model->load(\Yii::$app->request->post())) {
                    $model->file = \yii\web\UploadedFile::getInstance($model, 'file');

                    if ($model->file && $model->validate()) {
                        $message = $model->import();
                    } else {
                        $message = 'Файл не загружен: ' . print_r($model->getErrors(), 1);
                    }
                } else {
                    $message = print_r($model->getErrors(), true);
                }
            }
        }

        return $this->render('csv', [
            'message' => $message,
            'model' => $model,
            'categories' => $categories,
        ]);
    }

}
