<?php

namespace common\models\generated\query;

/**
* This is the ActiveQuery class for [[\common\models\generated\models\ShopExtra]].
*
* @see \common\models\generated\models\ShopExtra
*/
class ShopExtraQuery extends \common\ActiveQuery
{
/*public function active()
{
return $this->andWhere('[[status]]=1');
}*/

/**
* @inheritdoc
* @return \common\models\generated\models\ShopExtra[]|array
*/
public function all($db = null)
{
return parent::all($db);
}

/**
* @inheritdoc
* @return \common\models\generated\models\ShopExtra|array|null
*/
public function one($db = null)
{
return parent::one($db);
}
}
