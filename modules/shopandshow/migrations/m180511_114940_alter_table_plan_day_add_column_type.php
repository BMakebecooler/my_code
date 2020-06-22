<?php

use yii\db\Migration;

class m180511_114940_alter_table_plan_day_add_column_type extends Migration
{
    private $tableName = 'ss_monitoring_plan_day';

    public function safeUp()
    {
        $this->addColumn($this->tableName, 'type_plan', $this->char(10)->defaultValue(\modules\shopandshow\models\monitoringday\PlanDay::TYPE_SITE));
        $this->createIndex('I_type_plan', $this->tableName, 'type_plan');
    }

    public function safeDown()
    {
        $this->dropIndex('I_type_plan', $this->tableName);
        $this->dropColumn($this->tableName, 'type_plan');
    }
}
