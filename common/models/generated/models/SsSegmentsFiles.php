<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "ss_segments_files".
 *
 * @property integer $id ID
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property integer $file_id File ID
 * @property integer $begin_datetime Begin Datetime
 * @property integer $end_datetime End Datetime
 * @property string $name Name
 *
     * @property SsProductsSegments[] $ssProductsSegments
    */
class SsSegmentsFiles extends \common\ActiveRecord
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
        return 'ss_segments_files';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'file_id', 'begin_datetime', 'end_datetime'], 'integer'],
            [['begin_datetime', 'end_datetime'], 'required'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'file_id' => 'File ID',
            'begin_datetime' => 'Begin Datetime',
            'end_datetime' => 'End Datetime',
            'name' => 'Name',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getSsProductsSegments()
    {
        return $this->hasMany($this->called_class_namespace . '\SsProductsSegments', ['file_id' => 'id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\SsSegmentsFilesQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\SsSegmentsFilesQuery(get_called_class());
    }
}
