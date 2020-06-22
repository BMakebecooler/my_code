<?php

namespace common\models\generated\query;

/**
* This is the ActiveQuery class for [[\common\models\generated\models\SsShopDiscountValues]].
*
* @see \common\models\generated\models\SsShopDiscountValues
*/
class SsShopDiscountValuesQuery extends \common\ActiveQuery
{
/*public function active()
{
return $this->andWhere('[[status]]=1');
}*/

/**
* @inheritdoc
* @return \common\models\generated\models\SsShopDiscountValues[]|array
*/
public function all($db = null)
{
return parent::all($db);
}

/**
* @inheritdoc
* @return \common\models\generated\models\SsShopDiscountValues|array|null
*/
public function one($db = null)
{
return parent::one($db);
}
}
