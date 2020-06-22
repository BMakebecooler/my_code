<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "brand_popularity_by_tree".
 *
 * @property integer $id
 * @property integer $brand_id
 * @property integer $tree_id
 * @property integer $popularity
 */
class BrandPopularityByTree extends \common\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'brand_popularity_by_tree';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['brand_id', 'tree_id'], 'required'],
            [['brand_id', 'tree_id', 'popularity'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'brand_id' => 'Brand ID',
            'tree_id' => 'Tree ID',
            'popularity' => 'Popularity',
        ];
    }

    /**
     * @inheritdoc
     * @return \common\models\generated\query\BrandPopularityByTreeQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\generated\query\BrandPopularityByTreeQuery(get_called_class());
    }
}
