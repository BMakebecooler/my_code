<?php


namespace console\jobs;


use common\helpers\Segment as SegmentHelper;

class ExportSegmentLotsJob extends \yii\base\Object implements \yii\queue\Job
{
    public $segmentId;

    public function execute($queue)
    {
        SegmentHelper::generateLotsFile($this->segmentId);
    }
}