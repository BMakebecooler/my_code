<?php

use yii\db\Migration;

class m171031_125820_alter_table_shop_product_statistic_add_rating_columns extends Migration
{
    private $tableName = "{{%shop_product_statistic}}";

    public function safeUp()
    {
        $this->addColumn($this->tableName, 'k_rnd', $this->float());
        $this->addColumn($this->tableName, 'k_rating', $this->float());

        $this->createIndex('k_rating', $this->tableName, 'k_rating');
    }

    public function safeDown()
    {
        $this->dropIndex('k_rating', $this->tableName);

        $this->dropColumn($this->tableName, 'k_rnd');
        $this->dropColumn($this->tableName, 'k_rating');
    }
}
