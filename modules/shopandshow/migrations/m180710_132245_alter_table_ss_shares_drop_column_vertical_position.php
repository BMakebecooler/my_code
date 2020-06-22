<?php

use yii\db\Migration;

class m180710_132245_alter_table_ss_shares_drop_column_vertical_position extends Migration
{
    private $tableName = 'ss_shares';

    private $columnName = 'vertical_position';

    public function safeUp()
    {
        //$this->dropColumn($this->tableName, $this->columnName);

        $this->addColumn($this->tableName, 'share_schedule_id', $this->integer());

        $this->addForeignKey('ss_share_schedule_fk',
            $this->tableName, 'share_schedule_id',
            'ss_shares_schedule', 'id',
            'SET NULL', 'SET NULL'
        );
    }

    public function safeDown()
    {
        /*$this->addColumn(
            $this->tableName,
            $this->columnName,
            $this->integer()
                ->after('image_id')
                ->defaultValue(500)
                ->comment('Defines target block if the page is set to multiple blocks with one type')
        );
*/
        $this->dropForeignKey('ss_share_schedule_fk', $this->tableName);

        $this->dropColumn($this->tableName, 'share_schedule_id');
    }
}
