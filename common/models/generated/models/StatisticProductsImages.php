<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "statistic_products_images".
 *
 * @property integer $id ID
 * @property integer $count_all Count All
 * @property integer $count_all_stock Count All Stock
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
*/
class StatisticProductsImages extends \common\ActiveRecord
{
    private $called_class_namespace;

    public function __construct()
    {
        $this->called_class_namespace = substr(get_called_class(), 0, strrpos(get_called_class(), '\\'));
        parent::__construct();
    }

                            
    /**
     * @inheritdoc
    */
    public function behaviors()
    {
        return [
            'author' => \yii\behaviors\BlameableBehavior::class,
            'timestamp' => \yii\behaviors\TimestampBehavior::class,
        ];
    }

    /**
    * @inheritdoc
    */
    public static function tableName()
    {
        return 'statistic_products_images';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['count_all', 'count_all_stock', 'created_by', 'updated_by', 'created_at', 'updated_at'], 'integer'],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'count_all' => 'Count All',
            'count_all_stock' => 'Count All Stock',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            ];
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\StatisticProductsImagesQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\StatisticProductsImagesQuery(get_called_class());
    }
}
