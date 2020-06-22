<?php

namespace common\models\generated\query;

/**
* This is the ActiveQuery class for [[\common\models\generated\models\CmsStorageFile]].
*
* @see \common\models\generated\models\CmsStorageFile
*/
class CmsStorageFileQuery extends \common\ActiveQuery
{
/*public function active()
{
return $this->andWhere('[[status]]=1');
}*/

/**
* @inheritdoc
* @return \common\models\generated\models\CmsStorageFile[]|array
*/
public function all($db = null)
{
return parent::all($db);
}

/**
* @inheritdoc
* @return \common\models\generated\models\CmsStorageFile|array|null
*/
public function one($db = null)
{
return parent::one($db);
}
}
