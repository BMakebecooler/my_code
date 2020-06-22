<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "ss_monitoring_plan_hour".
 *
 * @property integer $id ID
 * @property integer $plan_id Plan ID
 * @property integer $hour Hour
 * @property integer $percent Percent
 *
     * @property SsMonitoringPlanDay $plan
    */
class SsMonitoringPlanHour extends \common\ActiveRecord
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
        return 'ss_monitoring_plan_hour';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['plan_id', 'hour', 'percent'], 'integer'],
            [['plan_id'], 'exist', 'skipOnError' => true, 'targetClass' => SsMonitoringPlanDay::className(), 'targetAttribute' => ['plan_id' => 'id']],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'plan_id' => 'Plan ID',
            'hour' => 'Hour',
            'percent' => 'Percent',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getPlan()
    {
        return $this->hasOne($this->called_class_namespace . '\SsMonitoringPlanDay', ['id' => 'plan_id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\SsMonitoringPlanHourQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\SsMonitoringPlanHourQuery(get_called_class());
    }
}
