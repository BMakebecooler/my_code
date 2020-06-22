<?php

namespace common\models\generated\query;

/**
* This is the ActiveQuery class for [[\common\models\generated\models\SizeProfile]].
*
* @see \common\models\generated\models\SizeProfile
*/
class SizeProfileQuery extends \common\ActiveQuery
{
/*public function active()
{
return $this->andWhere('[[status]]=1');
}*/

/**
* @inheritdoc
* @return \common\models\generated\models\SizeProfile[]|array
*/
public function all($db = null)
{
return parent::all($db);
}

/**
* @inheritdoc
* @return \common\models\generated\models\SizeProfile|array|null
*/
public function one($db = null)
{
return parent::one($db);
}
}
