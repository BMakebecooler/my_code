<?php


namespace console\jobs;


use common\helpers\Segment as SegmentHelper;
use common\models\Segment;

class SegmentSortJob extends \yii\base\Object implements \yii\queue\Job
{
    public $segmentId;

    public function execute($queue)
    {
        echo 'Start Update segment sort products '.$this->segmentId .' '. PHP_EOL;

        $segment = Segment::findOne($this->segmentId);
        if($segment) {
            SegmentHelper::sortByQty($segment->id);
        }
    }
}