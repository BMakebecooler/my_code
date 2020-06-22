<?php

/**
 * php yii segment/update-hidden-products
 * php yii segment/sort-by-qty
 * php yii segment/scheduled-generation
 * php yii segment/sort-by-qty
 * php yii segment/generate-lots-file
 * php yii segment/rebuild-all-actions-segment-split
 * php yii segment/set-disabled-off-all-segments
 *
 */

namespace console\controllers;

use common\models\Promo;
use console\jobs\SegmentJob;
use Yii;
use yii\console\Controller;
use common\models\Segment;
use common\helpers\Segment as SegmentHelper;
use yii\db\Exception;


class SegmentController extends Controller
{
    public $segmentId;

    public function optionAliases()
    {
        return ['id' => 'segment_id'];
    }

    public function options($actionID)
    {
        return ['segment_id'];
    }


    /**
     * метод генерации определенгных сборок строго по расписанию автоматически по шедулеру
     *
     */
    public function actionScheduledGeneration()
    {
        $segments = Segment::getSegmentIdsForSchedule();
        if (count($segments)) {
            foreach ($segments as $segmentId) {
                $segment = Segment::findOne($segmentId);
                if ($segment) {
                    //для очистки кеша
                    $segment->updated_at = time();
                    $segment->save();

                    $this->actionSetPromoProducts($segmentId);
                }
            }
        }
    }


    public function actionReset()
    {
        $data = Segment::find()
            ->andWhere(['active' => \common\helpers\Common::BOOL_Y_INT])
            ->andWhere(['generated' => \common\helpers\Common::BOOL_Y_INT])
            ->all();

        foreach ($data as $segment) {
            SegmentHelper::deleteProducts($segment->id);
            $products = unserialize($segment->products);
            foreach ($products as $id_product) {
                SegmentHelper::addProductSegment([
                    'product_id' => $id_product,
                    'segment_id' => $segment->id,
                    'sort' => 0,
                    'first' => 0
                ]);
            }

        }
    }

    public function actionSetPromoProductsQueue()
    {
        $data = Segment::find()
            ->andWhere(['active' => \common\helpers\Common::BOOL_Y_INT])
            ->andWhere(['generated' => \common\helpers\Common::BOOL_N_INT])
            ->andWhere(['regenerate' => \common\helpers\Common::BOOL_Y_INT])
            ->orderBy('id DESC')
            ->limit(20)
            ->all();

        foreach ($data as $modelSegment) {
            Yii::$app->queue->push(new SegmentJob([
                'segment_id' => $modelSegment->id,
            ]));
        }
    }

    /**
     * Пересобрать все автоматические сегменты или один отдельно взятый сегмент
     * @param $segmentId
     */
    public function actionSetPromoProducts($segmentId = null)
    {
        if ($segmentId) {
            $data = Segment::find()
                ->andWhere(['id' => $segmentId])
                ->orderBy('id DESC')
                ->all();
        } else {
            $data = Segment::find()
                ->andWhere(['active' => \common\helpers\Common::BOOL_Y_INT])
                ->andWhere(['generated' => \common\helpers\Common::BOOL_N_INT])
                ->notDisabled()
//                ->andWhere(['regenerate' => \common\helpers\Common::BOOL_Y_INT])
                ->orderBy('updated_at DESC')
                ->limit(SegmentHelper::$countRegenerateSegmentsLimit)
                ->all();
        }

        foreach ($data as $segment) {
            $check = SegmentHelper::setPromoProducts($segment);
            if (!$check) {
                throw  new Exception('SegmentJob Error set Promo Products');
            }
        }
    }

    /**
     * Обновить товары в сегментах, подлежащие скрытию по какой то причине
     *
     */
    public function actionUpdateCatalogHiddenProducts()
    {
        return SegmentHelper::updateCatalogHiddenProducts();
    }

    /**
     * Пересчитать доступные размеры цветомаделий для сортировки по доступному количеству размеров
     *
     */
    public function actionSortByQty($id = null)
    {
        if ($id) {
            $segments = Segment::find()
                ->andWhere(['active' => \common\helpers\Common::BOOL_Y_INT])
                ->andWhere(['sort' => SegmentHelper::$qtySort])
                ->andWhere(['id' => $id]);
        } else {
            $segments = Segment::find()
                ->andWhere(['active' => \common\helpers\Common::BOOL_Y_INT])
                ->andWhere(['sort' => SegmentHelper::$qtySort])
                ->addOrderBy(['id' => SORT_DESC]);
        }

        foreach ($segments->each() as $segment) {
            $this->stdout('Обновляем сегмент ' . $segment->id . ' ' . $segment->name, PHP_EOL);
            SegmentHelper::sortByQty($segment->id);
        }
    }

    /**
     * сгенерировать файл списка лотов
     * @param $segmentId
     *
     */
    public function actionGenerateLotsFile($segmentId)
    {
        $this->stdout('Генерируем файл список лотов  для сегмента  ' . $segmentId . PHP_EOL);
        $file = SegmentHelper::generateLotsFile($segmentId);
        if ($file) {
            $this->stdout('Сгенерирован файл ' . $file . PHP_EOL);
        } else {
            $this->stdout('Ошибка генерации  ' . PHP_EOL);
        }
    }

    /**
     * Пересобрать сегмент добавить карточки
     *
     */
    public function actionRebuildSegmentSplit($id = null)
    {
        if ($id) {
            $segment = Segment::findOne($id);
            if ($segment) {
                $this->stdout('Обновляем сегмент для карточек: ' . $segment->id . ' ' . $segment->name, PHP_EOL);
                SegmentHelper::rebuildSegmentSplit($id);
            }
        } else {
            $query = Promo::findPromosActionQuery();
            foreach ($query->each() as $promo) {
                if ($promo->segment_id) {
                    $segment = Segment::findOne($promo->segment_id);
                    if ($segment) {
                        $this->stdout('Обновляем сегмент для карточек: ' . $segment->id . ' ' . $segment->name, PHP_EOL);
                        $count = SegmentHelper::rebuildSegmentSplit($promo->segment_id);
                        $this->stdout('Обновлен сегмент : ' . $segment->id . ' ' . $segment->name . ' товаров: ' . $count, PHP_EOL);
                    }
                }
            }
        }
    }

    /**
     * Все сегменты отключить Desibled, на случай если генерация какого либо сегмента зависла а персохранять его невозможно
     *
     */
    public function actionSetDisabledOffAllSegments()
    {
        \common\models\Segment::updateAll(['disabled' => \common\helpers\Common::BOOL_N_INT], ['disabled' => \common\helpers\Common::BOOL_Y_INT]);
    }


}