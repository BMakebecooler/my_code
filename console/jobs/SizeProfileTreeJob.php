<?php


namespace console\jobs;


use common\models\SizeProfile;

class SizeProfileTreeJob extends \yii\base\Object implements \yii\queue\Job
{
    public $size_profile_id;

    public function execute($queue)
    {
        $model = SizeProfile::findOne($this->size_profile_id);
        if($model){
            $tree_ids = $model->buildTreeIds();
            $model->tree_ids = serialize($tree_ids);
            $model->save();
        }
    }
}