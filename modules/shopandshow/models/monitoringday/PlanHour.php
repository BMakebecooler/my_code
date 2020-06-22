<?php
namespace modules\shopandshow\models\monitoringday;

/**
 * Class Plan
 *
 * @property int    $id
 * @property int    $plan_id
 * @property int    $hour
 * @property int    $percent
 *
 * @package modules\shopandshow\models\plan
 */
class PlanHour extends \yii\db\ActiveRecord
{

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
            [['plan_id', 'hour', 'percent'], 'required'],
            [['plan_id', 'hour', 'percent'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'plan_id' => 'План',
            'hour' => 'Час',
            'percent' => 'Процент плана',
        ];
    }
}