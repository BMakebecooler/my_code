<?php

namespace console\jobs;

/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 07/02/2019
 * Time: 23:28
 */

class ClearUnUsedGuidJob extends \yii\base\Object implements \yii\queue\Job
{

    public function execute($queue)
    {
        \Yii::$app->runAction('guid/export/run-one', ['id' => $this->id]);
    }
}