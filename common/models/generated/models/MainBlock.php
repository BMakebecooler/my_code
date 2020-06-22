<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "main_block".
 *
 * @property integer $id ID
 * @property integer $active Active
 * @property string $name Name
 * @property string $type Type
 * @property string $subtype Subtype
 * @property string $lot_num Lot Num
 * @property string $description Description
 * @property string $image Image
 * @property integer $sort Sort
 * @property integer $start_timestamp Start Timestamp
 * @property integer $end_timestamp End Timestamp
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $constant Constant
 *
     * @property MainBanner[] $mainBanners
     * @property MainTemplateBlock[] $mainTemplateBlocks
    */
class MainBlock extends \common\ActiveRecord
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
        return 'main_block';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['active', 'sort', 'start_timestamp', 'end_timestamp', 'created_at', 'updated_at', 'created_by', 'updated_by', 'constant'], 'integer'],
            [['name', 'type'], 'required'],
            [['description'], 'string'],
            [['name', 'type', 'subtype', 'lot_num', 'image'], 'string', 'max' => 255],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'active' => 'Active',
            'name' => 'Name',
            'type' => 'Type',
            'subtype' => 'Subtype',
            'lot_num' => 'Lot Num',
            'description' => 'Description',
            'image' => 'Image',
            'sort' => 'Sort',
            'start_timestamp' => 'Start Timestamp',
            'end_timestamp' => 'End Timestamp',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'constant' => 'Constant',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getMainBanners()
    {
        return $this->hasMany($this->called_class_namespace . '\MainBanner', ['block_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getMainTemplateBlocks()
    {
        return $this->hasMany($this->called_class_namespace . '\MainTemplateBlock', ['block_id' => 'id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\MainBlockQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\MainBlockQuery(get_called_class());
    }
}
