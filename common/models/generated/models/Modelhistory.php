<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "modelhistory".
 *
 * @property integer $id ID
 * @property string $date Date
 * @property string $table Table
 * @property string $field_name Field Name
 * @property string $field_id Field ID
 * @property string $old_value Old Value
 * @property string $new_value New Value
 * @property integer $type Type
 * @property integer $user_id User ID
*/
class Modelhistory extends \common\ActiveRecord
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
        return 'modelhistory';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['date', 'table', 'field_name', 'field_id', 'type'], 'required'],
            [['date'], 'safe'],
            [['old_value', 'new_value'], 'string'],
            [['type', 'user_id'], 'integer'],
            [['table', 'field_name', 'field_id'], 'string', 'max' => 255],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'date' => 'Date',
            'table' => 'Table',
            'field_name' => 'Field Name',
            'field_id' => 'Field ID',
            'old_value' => 'Old Value',
            'new_value' => 'New Value',
            'type' => 'Type',
            'user_id' => 'User ID',
            ];
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\ModelhistoryQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\ModelhistoryQuery(get_called_class());
    }
}
