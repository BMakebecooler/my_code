<?php

namespace common\models\generated\query;

/**
* This is the ActiveQuery class for [[\common\models\generated\models\SsMailTemplate]].
*
* @see \common\models\generated\models\SsMailTemplate
*/
class SsMailTemplateQuery extends \common\ActiveQuery
{
/*public function active()
{
return $this->andWhere('[[status]]=1');
}*/

/**
* @inheritdoc
* @return \common\models\generated\models\SsMailTemplate[]|array
*/
public function all($db = null)
{
return parent::all($db);
}

/**
* @inheritdoc
* @return \common\models\generated\models\SsMailTemplate|array|null
*/
public function one($db = null)
{
return parent::one($db);
}
}
