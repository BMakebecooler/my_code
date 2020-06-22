<?php

namespace console\jobs;

class UpdateSegmentCatalogHiddenProducts extends \yii\base\Object implements \yii\queue\Job
{
    public function execute($queue)
    {
        echo 'Start UpdateSegmentCatalogHiddenProducts'. PHP_EOL;
        \common\helpers\Segment::updateCatalogHiddenProducts();
    }
}