<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "ss_task".
 *
 * @property integer $id ID
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property integer $status Status
 * @property string $component Component
 * @property string $component_settings Component Settings
*/
class SsTask extends \common\ActiveRecord
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
        return 'ss_task';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'status'], 'integer'],
            [['component'], 'required'],
            [['component_settings'], 'string'],
            [['component'], 'string', 'max' => 255],
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
            'status' => 'Status',
            'component' => 'Component',
            'component_settings' => 'Component Settings',
            ];
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\SsTaskQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\SsTaskQuery(get_called_class());
    }
}
