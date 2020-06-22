<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "cms_agent".
 *
 * @property integer $id ID
 * @property integer $last_exec_at Last Exec At
 * @property integer $next_exec_at Next Exec At
 * @property string $name Name
 * @property string $description Description
 * @property integer $agent_interval Agent Interval
 * @property integer $priority Priority
 * @property string $active Active
 * @property string $is_period Is Period
 * @property string $is_running Is Running
*/
class CmsAgent extends \common\ActiveRecord
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
        return 'cms_agent';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['last_exec_at', 'next_exec_at', 'agent_interval', 'priority'], 'integer'],
            [['next_exec_at', 'name'], 'required'],
            [['name', 'description'], 'string'],
            [['active', 'is_period', 'is_running'], 'string', 'max' => 1],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'last_exec_at' => 'Last Exec At',
            'next_exec_at' => 'Next Exec At',
            'name' => 'Name',
            'description' => 'Description',
            'agent_interval' => 'Agent Interval',
            'priority' => 'Priority',
            'active' => 'Active',
            'is_period' => 'Is Period',
            'is_running' => 'Is Running',
            ];
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\CmsAgentQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\CmsAgentQuery(get_called_class());
    }
}
