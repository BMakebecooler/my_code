<?php

namespace modules\shopandshow\controllers\shares;

use common\helpers\ArrayHelper;
use common\models\cmsContent\CmsContentElement;
use modules\shopandshow\models\shares\SsShare;
use modules\shopandshow\models\shares\SsShareSchedule;
use modules\shopandshow\models\searches\SsShareSearch;
use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use skeeks\cms\modules\admin\traits\AdminModelEditorStandartControllerTrait;
use Yii;
use yii\web\Response;

/**
 * Class AdminSharesController
 *
 * @package modules\shopandshow\controllers
 */
class AdminSharesController extends AdminModelEditorController
{
    use AdminModelEditorStandartControllerTrait;

    public function actions()
    {
        $actions = ArrayHelper::merge(parent::actions(),
            [

                "index" =>
                    [
                        'modelSearchClassName' => SsShareSearch::className()
                    ],

            ]
        );

        return $actions;
    }

    public function init()
    {
        $this->modelClassName = SsShare::className();
        $this->name = 'Баннеры';
        $this->modelShowAttribute = "id";

        parent::init();
    }

    public function actionReloadBanners()
    {
        $this->name = 'Перезаливка баннеров';
        $message = '';
        if (\Yii::$app->request->isPost) {
            if (!\Yii::$app->request->post('ajax')) {
                try {
                    $banners = \Yii::$app->shares->getAdvBannersInfoBlock();

                    \Yii::$app->shares->createCmsContentElement();

                    \Yii::$app->shares->bannersProducts();

                    \Yii::$app->shares->bannersProductsOriginal();

                    $message = 'Обработано баннеров: ' . sizeof($banners);
                } catch (\Exception $e) {
                    $message = $e->getMessage();
                }
            }
        }

        return $this->render('reload-banners', ['message' => $message]);
    }

    public function actionGrid()
    {
        $this->name = 'Баннерная сетка';

        $searchDate = time();
        $message = '';

        if (\Yii::$app->request->isPost) {
            if (\Yii::$app->request->post('ajax')) {
                return true;
            }

            ini_set("memory_limit","800M");

            //* BANNER TYPE AND VERTICAL POS SAVE *//
            if (!empty(Yii::$app->request->post('SsShare'))) {
                foreach (Yii::$app->request->post('SsShare') as $bannerId => $bannerData) {
                    $banner = SsShare::findOne($bannerId);
                    if ($banner) {
                        $banner->banner_type = !empty($bannerData['banner_type']) ? $bannerData['banner_type'] : $banner->banner_type;
                        $banner->save();
                    }
                }
            }
            //* /BANNER TYPE AND VERTICAL POS SAVE *//

            $searchDate = \Yii::$app->request->post('searchdate') ?: time();
            $ssShareSchedules = SsShareSchedule::findByDate($searchDate)->all();
            $ssShareSchedules['new'] = new SsShareSchedule();

            if (SsShareSchedule::loadMultiple($ssShareSchedules, Yii::$app->request->post())
                && SsShareSchedule::validateMultiple($ssShareSchedules)
            ) {
                $count = 0;
                foreach ($ssShareSchedules as $ssShareSchedule) {
                    if ($ssShareSchedule->block_type && $ssShareSchedule->block_position) {
                        if ($ssShareSchedule->save(false)) {
                            $count++;
                            //$searchDate = $ssShareSchedule->begin_datetime;
                        } else {
                            $message .= print_r($ssShareSchedule->getErrors(), 1);
                        }
                    } elseif (!$ssShareSchedule->block_type && !$ssShareSchedule->isNewRecord) {
                        //$searchDate = $ssShareSchedule->begin_datetime;
                        $ssShareSchedule->delete();
                    } elseif ($ssShareSchedule->block_type && !$ssShareSchedule->block_position) {
                        $message .= 'Порядок блока block_'.$ssShareSchedule->id.' не указан<br>';
                    }

                }

                if ($count > 0) {
                    $message .= 'Изменения сохранены';
                }
            }
        }

        $ssShareSchedules = SsShareSchedule::findByDate($searchDate)->all();

        return $this->render('grid', ['message' => $message, 'searchDate' => $searchDate, 'models' => $ssShareSchedules]);

    }

    public function actionGridPreview()
    {
        $block = \Yii::$app->request->post('block');
        if (!$block) return 'Блок не указан';

        $blockNumByType = \Yii::$app->request->post('block_num_by_type');
        $searchDate = \Yii::$app->request->post('searchdate', time());
        $blockId = \Yii::$app->request->post('block_id');
        if ($blockId) {
            $searchDate = null;
        }

        return $this->renderAjax('grid/' . $block, ['searchDate' => $searchDate, 'blockNumByType' => $blockNumByType, 'blockId' => $blockId]);
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
                ['like', 'bitrix_id', $q],
            ])
            ->select(['id', 'name', 'code'])
            ->orderBy('name, id');

        $out['results'] = $contentElement->asArray()->all();

        return $out;
    }

    public function actionExport()
    {
        Yii::$app->response->format = Response::FORMAT_RAW;

        $searchForm = new SsShareSearch();
        $dataProvider = $searchForm->search(\Yii::$app->request->get());
        $dataProvider->pagination = false;
        $dataProvider->query->limit(null);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="banners.csv"');

        echo $searchForm->getDataForExportCsv($dataProvider);
    }
}
