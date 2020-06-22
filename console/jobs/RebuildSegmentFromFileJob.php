<?php


namespace console\jobs;


use common\helpers\Segment as SegmentHelper;

class RebuildSegmentFromFileJob extends \yii\base\Object implements \yii\queue\Job
{
    public $segmentId;

    public function execute($queue)
    {
//        echo 'Start UpdateQuantityJob id' . $this->segmentId . PHP_EOL;
        \Yii::error('Start rebuild Segment Split ' . $this->segmentId, "\console\jobs\RebuildSegmentFromFileJob");
        SegmentHelper::rebuildSegmentSplit($this->segmentId);
    }

}