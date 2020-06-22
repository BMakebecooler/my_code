<?php


namespace console\jobs;

use common\helpers\StatisticProductsImagesHelper;

class StatisticProductsImagesFileJob extends \yii\base\Object implements \yii\queue\Job
{
    public function execute($queue)
    {
        StatisticProductsImagesHelper::setProductsWithoutImagesCount();
    }

}