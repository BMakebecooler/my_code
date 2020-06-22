<?php

use yii\db\Migration;

/**
 * Class m180118_084944_alter_table_sshare_updated_at
 */
class m180118_084944_alter_table_sshare_updated_at extends Migration
{
    private $tableName = 'ss_shares';
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn($this->tableName, 'updated_at', $this->integer(11)->after('created_at'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn($this->tableName, 'updated_at');
    }
}
