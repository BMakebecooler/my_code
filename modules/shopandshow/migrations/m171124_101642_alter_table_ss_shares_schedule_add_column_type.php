<?php

use yii\db\Migration;

/**
 * Class m171124_101642_alter_table_ss_shares_schedule_add_column_type
 */
class m171124_101642_alter_table_ss_shares_schedule_add_column_type extends Migration
{
    private $tableName = '{{%ss_shares_schedule}}';

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn($this->tableName, 'type', $this->string(2)->defaultValue('G'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn($this->tableName, 'type');
    }
}
