<?php

namespace common\models\generated\query;

/**
* This is the ActiveQuery class for [[\common\models\generated\models\CmsContentPropertyEnum]].
*
* @see \common\models\generated\models\CmsContentPropertyEnum
*/
class CmsContentPropertyEnumQuery extends \common\ActiveQuery
{
/*public function active()
{
return $this->andWhere('[[status]]=1');
}*/

/**
* @inheritdoc
* @return \common\models\generated\models\CmsContentPropertyEnum[]|array
*/
public function all($db = null)
{
return parent::all($db);
}

/**
* @inheritdoc
* @return \common\models\generated\models\CmsContentPropertyEnum|array|null
*/
public function one($db = null)
{
return parent::one($db);
}
}
