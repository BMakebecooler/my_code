<?php

use yii\db\Migration;

/**
 * Class m171128_125546_alter_table_shop_product_statistic_add_columns
 */
class m171128_125546_alter_table_shop_product_statistic_add_columns extends Migration
{
    private $tableName = 'shop_product_statistic';

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn($this->tableName, 'viewed', $this->integer());
        $this->addColumn($this->tableName, 'ordered', $this->integer());
        $this->addColumn($this->tableName, 'margin', $this->integer());
        $this->addColumn($this->tableName, 'pzp', $this->integer());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn($this->tableName, 'pzp');
        $this->dropColumn($this->tableName, 'margin');
        $this->dropColumn($this->tableName, 'ordered');
        $this->dropColumn($this->tableName, 'viewed');
    }
}
