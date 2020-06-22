<?php

use yii\db\Migration;

/**
 * Class m171220_100733_alter_table_ss_shares_products_add_priority
 */
class m171220_100733_alter_table_ss_shares_products_add_priority extends Migration
{
    private $tableName = 'ss_shares_products';
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn($this->tableName, 'priority', $this->integer());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn($this->tableName, 'priority');
    }
}
