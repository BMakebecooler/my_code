<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "log_db_target".
 *
 * @property integer $id ID
 * @property integer $level Level
 * @property string $category Category
 * @property integer $log_time Log Time
 * @property string $prefix Prefix
 * @property string $message Message
*/
class LogDbTarget extends \common\ActiveRecord
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
    public static function tableName()
    {
        return 'log_db_target';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['level', 'log_time'], 'integer'],
            [['prefix', 'message'], 'string'],
            [['category'], 'string', 'max' => 255],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'level' => 'Level',
            'category' => 'Category',
            'log_time' => 'Log Time',
            'prefix' => 'Prefix',
            'message' => 'Message',
            ];
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\LogDbTargetQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\LogDbTargetQuery(get_called_class());
    }
}
