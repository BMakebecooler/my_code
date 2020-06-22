<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "product_param_category".
 *
 * @property integer $id ID
 * @property integer $type_id Type ID
 * @property integer $tree_id Tree ID
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 *
     * @property CmsTree $tree
     * @property ProductParamType $type
    */
class ProductParamCategory extends \common\ActiveRecord
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
            'timestamp' => \yii\behaviors\TimestampBehavior::class,
            'author' => \yii\behaviors\BlameableBehavior::class,
        ];
    }

    /**
    * @inheritdoc
    */
    public static function tableName()
    {
        return 'product_param_category';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['type_id', 'tree_id'], 'required'],
            [['type_id', 'tree_id', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['type_id', 'tree_id'], 'unique', 'targetAttribute' => ['type_id', 'tree_id'], 'message' => 'The combination of Type ID and Tree ID has already been taken.'],
            [['tree_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsTree::className(), 'targetAttribute' => ['tree_id' => 'id']],
            [['type_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProductParamType::className(), 'targetAttribute' => ['type_id' => 'id']],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type_id' => 'Type ID',
            'tree_id' => 'Tree ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getTree()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsTree', ['id' => 'tree_id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getType()
    {
        return $this->hasOne($this->called_class_namespace . '\ProductParamType', ['id' => 'type_id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\ProductParamCategoryQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\ProductParamCategoryQuery(get_called_class());
    }
}
