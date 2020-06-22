<?php

use yii\db\Migration;

/**
 * Class m180213_164500_create_table_ss_plan
 */
class m180213_164500_create_table_ss_plan extends Migration
{
    private $planTableName = 'ss_monitoring_plan_day';
    private $planHoursTableName = 'ss_monitoring_plan_hour';

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable($this->planTableName, [
            'id' => $this->primaryKey(),
            'date' => $this->date()->notNull(),
            'sum_plan' => $this->integer()->comment('План за день')
        ], $tableOptions);

        $this->createIndex('I_date', $this->planTableName, 'date');

        $this->createTable($this->planHoursTableName, [
            'id' => $this->bigPrimaryKey(),
            'plan_id' => $this->integer(),
            'hour' => $this->smallInteger(1),
            'percent' => $this->smallInteger(1)
        ], $tableOptions);

        $this->createIndex('I_plan_id', $this->planHoursTableName, 'plan_id');
        $this->createIndex('I_hour', $this->planHoursTableName, 'hour');

        $this->addForeignKey('FK_plan', $this->planHoursTableName, 'plan_id', $this->planTableName, 'id', 'CASCADE', 'CASCADE');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable($this->planHoursTableName);
        $this->dropTable($this->planTableName);
    }
}
