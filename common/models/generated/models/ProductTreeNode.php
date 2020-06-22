<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "product_tree_node".
 *
 * @property integer $id
 * @property integer $element_id
 * @property integer $content_id
 * @property integer $tree_id
 * @property integer $node_id
 */
class ProductTreeNode extends \common\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'product_tree_node';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['element_id'], 'required'],
            [['element_id', 'content_id', 'tree_id', 'node_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'element_id' => 'Element ID',
            'content_id' => 'Content ID',
            'tree_id' => 'Tree ID',
            'node_id' => 'Node ID',
        ];
    }

    /**
     * @inheritdoc
     * @return \common\models\generated\query\ProductTreeNodeQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\generated\query\ProductTreeNodeQuery(get_called_class());
    }
}
