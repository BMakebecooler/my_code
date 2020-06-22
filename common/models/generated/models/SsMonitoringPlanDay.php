<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "ss_monitoring_plan_day".
 *
 * @property integer $id ID
 * @property string $date Date
 * @property integer $sum_plan Sum Plan
 * @property string $type_plan Type Plan
 *
     * @property SsMonitoringPlanHour[] $ssMonitoringPlanHours
    */
class SsMonitoringPlanDay extends \common\ActiveRecord
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
        return 'ss_monitoring_plan_day';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['date'], 'required'],
            [['date'], 'safe'],
            [['sum_plan'], 'integer'],
            [['type_plan'], 'string', 'max' => 10],
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
            'sum_plan' => 'Sum Plan',
            'type_plan' => 'Type Plan',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getSsMonitoringPlanHours()
    {
        return $this->hasMany($this->called_class_namespace . '\SsMonitoringPlanHour', ['plan_id' => 'id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\SsMonitoringPlanDayQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\SsMonitoringPlanDayQuery(get_called_class());
    }
}
