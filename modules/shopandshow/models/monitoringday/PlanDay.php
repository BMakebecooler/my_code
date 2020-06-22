<?php

namespace modules\shopandshow\models\monitoringday;

/**
 * Class Plan
 *
 * @property int $id
 * @property string $date
 * @property int $sum_plan
 * @property string $type_plan
 *
 * @package modules\shopandshow\models\plan
 */
class PlanDay extends \yii\db\ActiveRecord
{

    const TYPE_EFIR = 'efir';
    const TYPE_SITE = 'site';

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
            [['date', 'sum_plan', 'type_plan'], 'required'],
            [['date', 'type_plan'], 'string'],
            [['sum_plan'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'date' => 'Дата',
            'sum_plan' => 'План',
            'type_plan' => 'Тип плана'
        ];
    }

    /**
     * @inheritdoc
     * @param string $type
     * @return $this|\yii\db\ActiveQuery
     */
    public static function find($type = self::TYPE_SITE)
    {
        return parent::find()->andWhere(['type_plan' => $type]);
    }

    public static function getTypeList()
    {
        return [
            self::TYPE_SITE => 'План для сайта',
            self::TYPE_EFIR => 'План для эфира',
        ];
    }
}