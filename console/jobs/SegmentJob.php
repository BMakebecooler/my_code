<?php


namespace console\jobs;

use common\helpers\Segment as SegmentHelper;
use common\models\Segment;

class SegmentJob extends \yii\base\Object implements \yii\queue\Job
{
    public $segmentId;

    public function execute($queue)
    {
//        echo 'Start Update segment products ' . $this->segmentId . ' ' . PHP_EOL;

        \Yii::error('Start Update segment products ' . $this->segmentId, "\console\jobs\SegmentJob");

        //TODO Отключить когда разбор очередей нормализуется
        if (false) {
            echo 'TEMPORARY SKIP PROCESSING ' . PHP_EOL;
            return true;
        }

        $segment = Segment::findOne($this->segmentId);
        if ($segment) {
            $check = SegmentHelper::setPromoProducts($segment);
            if (!$check) {
                \Yii::error("SegmentJob Error set Promo Products, Id segment" . $this->segmentId, "\console\jobs\SegmentJob");
            }
        } else {
            \Yii::error("SegmentJob Error find Segment object, Id segment" . $this->segmentId, "\console\jobs\SegmentJob");
        }
    }
}