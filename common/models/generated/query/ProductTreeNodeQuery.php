<?php

namespace common\models\generated\query;

/**
 * This is the ActiveQuery class for [[\common\models\generated\models\ProductTreeNode]].
 *
 * @see \common\models\generated\models\ProductTreeNode
 */
class ProductTreeNodeQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return \common\models\generated\models\ProductTreeNode[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \common\models\generated\models\ProductTreeNode|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
