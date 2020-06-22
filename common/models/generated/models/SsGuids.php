<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "ss_guids".
 *
 * @property integer $id ID
 * @property string $guid Guid
 * @property integer $entity_type Entity Type
*/
class SsGuids extends \common\ActiveRecord
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
        return 'ss_guids';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['entity_type'], 'required'],
            [['entity_type'], 'integer'],
            [['guid'], 'string', 'max' => 64],
            [['guid', 'entity_type'], 'unique', 'targetAttribute' => ['guid', 'entity_type'], 'message' => 'The combination of Guid and Entity Type has already been taken.'],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'guid' => 'Guid',
            'entity_type' => 'Entity Type',
            ];
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\SsGuidsQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\SsGuidsQuery(get_called_class());
    }
}
