<?php

namespace modules\shopandshow\controllers\mail;

use common\models\cmsContent\CmsContentElement;
use modules\shopandshow\models\shares\SsShare;
use modules\shopandshow\models\shares\SsMailSchedule;
use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use skeeks\cms\modules\admin\traits\AdminModelEditorStandartControllerTrait;
use Yii;

/**
 * Class SharesController
 *
 * @package modules\shopandshow\controllers
 */
class AdminScheduleController extends AdminModelEditorController
{
    use AdminModelEditorStandartControllerTrait;

    public function init()
    {
        $this->modelClassName = SsShare::className();
        $this->name = 'Конструктор рассылки';
        $this->modelShowAttribute = "id";

        parent::init();
    }

    public function actionGrid()
    {
        $this->name = 'Конструктор блоков';

        $searchDate = time();
        $message = '';

        if (\Yii::$app->request->isPost) {
            $rr = new RequestResponse();
            if (\Yii::$app->request->post('ajax')) {
                return true;
            }

            $searchDate = \Yii::$app->request->post('searchdate') ? : time();

            $SsMailSchedules = SsMailSchedule::findByDate($searchDate)->all();
            $SsMailSchedules['new'] = new SsMailSchedule();

            if (SsMailSchedule::loadMultiple($SsMailSchedules, Yii::$app->request->post())
                && SsMailSchedule::validateMultiple($SsMailSchedules)) {
                $count = 0;
                foreach ($SsMailSchedules as $SsMailSchedule) {
                    if ($SsMailSchedule->block_type && $SsMailSchedule->block_position) {

                        if ($SsMailSchedule->isNewRecord) {
                            $blockExists = array_filter($SsMailSchedules, function ($item) use ($SsMailSchedule) {
                                return (!$item->isNewRecord && $SsMailSchedule->block_type == $item->block_type && $SsMailSchedule->block_position == $item->block_position);
                            });

                            if ($blockExists) {
                                $message .= 'Такой блок уже есть.';
                                continue;
                            }
                        }

                        if ($SsMailSchedule->save(false)) {
                            $count++;
                            $searchDate = $SsMailSchedule->begin_datetime;
                        } else {
                            $message .= print_r($SsMailSchedule->getErrors(), 1);
                        }
                    }
                    elseif(!$SsMailSchedule->block_type && !$SsMailSchedule->isNewRecord) {
                        $searchDate = $SsMailSchedule->begin_datetime;
                        $SsMailSchedule->delete();
                    }
                }

                if($count > 0) {
                    $message .= 'Изменения сохранены';
                    unset($SsMailSchedules['new']);
                }
            }

        }

        $SsMailSchedules = SsMailSchedule::findByDate($searchDate)->all();

        return $this->render('grid', ['message' => $message, 'searchDate' => $searchDate, 'models' => $SsMailSchedules]);

    }

    public function actionGridPreview()
    {
        $block = \Yii::$app->request->post('block');
        //$searchDate = \Yii::$app->request->post('searchdate') ? : time();
        if(!$block) return 'Блок не указан';

        return $this->renderPartial('grid-preview', ['block' => $block]);
    }

    public function actionProductSearch($q = null)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $out = ['results' => []];

        $contentElement = CmsContentElement::find()
            ->active()
            ->where(['content_id' => PRODUCT_CONTENT_ID])
            ->andFilterWhere([
                'OR',
                ['like', 'name', $q],
                //['like', 'code', $q.'%', false],
                ['like', 'code', $q],
            ])
            ->select(['id', 'name', 'code'])
            ->orderBy('name');

        $out['results'] = $contentElement->asArray()->all();

        return $out;
    }
}
