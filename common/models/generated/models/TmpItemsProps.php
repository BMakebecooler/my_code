<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "tmp_items_props".
 *
 * @property integer $id ID
 * @property string $code Code
 * @property string $value Value
*/
class TmpItemsProps extends \common\ActiveRecord
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
        return 'tmp_items_props';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['value'], 'string'],
            [['code'], 'string', 'max' => 45],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Code',
            'value' => 'Value',
            ];
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\TmpItemsPropsQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\TmpItemsPropsQuery(get_called_class());
    }
}
