<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "cms_event".
 *
 * @property integer $id ID
 * @property string $event_name Event Name
 * @property string $name Name
 * @property string $description Description
 * @property integer $priority Priority
*/
class CmsEvent extends \common\ActiveRecord
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
        return 'cms_event';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['event_name'], 'required'],
            [['description'], 'string'],
            [['priority'], 'integer'],
            [['event_name'], 'string', 'max' => 255],
            [['name'], 'string', 'max' => 100],
            [['event_name'], 'unique'],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'event_name' => 'Event Name',
            'name' => 'Name',
            'description' => 'Description',
            'priority' => 'Priority',
            ];
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\CmsEventQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\CmsEventQuery(get_called_class());
    }
}
