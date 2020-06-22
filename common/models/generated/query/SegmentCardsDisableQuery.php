<?php

namespace common\models\generated\query;

/**
* This is the ActiveQuery class for [[\common\models\generated\models\SegmentCardsDisable]].
*
* @see \common\models\generated\models\SegmentCardsDisable
*/
class SegmentCardsDisableQuery extends \common\ActiveQuery
{
/*public function active()
{
return $this->andWhere('[[status]]=1');
}*/

/**
* @inheritdoc
* @return \common\models\generated\models\SegmentCardsDisable[]|array
*/
public function all($db = null)
{
return parent::all($db);
}

/**
* @inheritdoc
* @return \common\models\generated\models\SegmentCardsDisable|array|null
*/
public function one($db = null)
{
return parent::one($db);
}
}
