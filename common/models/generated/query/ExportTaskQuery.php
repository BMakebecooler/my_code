<?php

namespace common\models\generated\query;

/**
* This is the ActiveQuery class for [[\common\models\generated\models\ExportTask]].
*
* @see \common\models\generated\models\ExportTask
*/
class ExportTaskQuery extends \common\ActiveQuery
{
/*public function active()
{
return $this->andWhere('[[status]]=1');
}*/

/**
* @inheritdoc
* @return \common\models\generated\models\ExportTask[]|array
*/
public function all($db = null)
{
return parent::all($db);
}

/**
* @inheritdoc
* @return \common\models\generated\models\ExportTask|array|null
*/
public function one($db = null)
{
return parent::one($db);
}
}
