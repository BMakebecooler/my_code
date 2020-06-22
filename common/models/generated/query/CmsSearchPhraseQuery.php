<?php

namespace common\models\generated\query;

/**
* This is the ActiveQuery class for [[\common\models\generated\models\CmsSearchPhrase]].
*
* @see \common\models\generated\models\CmsSearchPhrase
*/
class CmsSearchPhraseQuery extends \common\ActiveQuery
{
/*public function active()
{
return $this->andWhere('[[status]]=1');
}*/

/**
* @inheritdoc
* @return \common\models\generated\models\CmsSearchPhrase[]|array
*/
public function all($db = null)
{
return parent::all($db);
}

/**
* @inheritdoc
* @return \common\models\generated\models\CmsSearchPhrase|array|null
*/
public function one($db = null)
{
return parent::one($db);
}
}
