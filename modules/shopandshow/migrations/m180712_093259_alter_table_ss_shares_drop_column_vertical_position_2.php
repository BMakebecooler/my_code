<?php

use yii\db\Migration;

class m180712_093259_alter_table_ss_shares_drop_column_vertical_position_2 extends Migration
{
    private $tableName = 'ss_shares';

    private $columnName = 'vertical_position';

    public function safeUp()
    {
        $this->dropColumn($this->tableName, $this->columnName);
    }

    public function safeDown()
    {
        $this->addColumn(
            $this->tableName,
            $this->columnName,
            $this->integer()
                ->after('image_id')
                ->defaultValue(500)
                ->comment('Defines target block if the page is set to multiple blocks with one type')
        );
    }
}
