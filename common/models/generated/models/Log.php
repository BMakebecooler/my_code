<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "log".
 *
 * @property integer $id ID
 * @property string $type Type
 * @property string $model_class Model Class
 * @property integer $model_id Model ID
 * @property string $text Text
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
*/
class Log extends \common\ActiveRecord
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
        return 'log';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['model_id', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['type', 'model_class', 'text'], 'string', 'max' => 255],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'model_class' => 'Model Class',
            'model_id' => 'Model ID',
            'text' => 'Text',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            ];
    }

    /**
     * @inheritdoc
     * @return \common\models\query\LogQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\LogQuery(get_called_class());
    }
}
