<?php

namespace common\models\generated\query;

/**
* This is the ActiveQuery class for [[\common\models\generated\models\CmsContentElement]].
*
* @see \common\models\generated\models\CmsContentElement
*/
class CmsContentElementQuery extends \common\ActiveQuery
{
/*public function active()
{
return $this->andWhere('[[status]]=1');
}*/

/**
* @inheritdoc
* @return \common\models\generated\models\CmsContentElement[]|array
*/
public function all($db = null)
{
return parent::all($db);
}

/**
* @inheritdoc
* @return \common\models\generated\models\CmsContentElement|array|null
*/
public function one($db = null)
{
return parent::one($db);
}
}
