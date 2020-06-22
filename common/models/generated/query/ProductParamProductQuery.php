<?php

namespace common\models\generated\query;

/**
* This is the ActiveQuery class for [[\common\models\generated\models\ProductParamProduct]].
*
* @see \common\models\generated\models\ProductParamProduct
*/
class ProductParamProductQuery extends \common\ActiveQuery
{
/*public function active()
{
return $this->andWhere('[[status]]=1');
}*/

/**
* @inheritdoc
* @return \common\models\generated\models\ProductParamProduct[]|array
*/
public function all($db = null)
{
return parent::all($db);
}

/**
* @inheritdoc
* @return \common\models\generated\models\ProductParamProduct|array|null
*/
public function one($db = null)
{
return parent::one($db);
}
}
