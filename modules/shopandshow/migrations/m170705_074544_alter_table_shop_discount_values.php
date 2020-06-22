<?php

use yii\db\Migration;

class m170705_074544_alter_table_shop_discount_values extends Migration
{
    private $tableNameValues = "{{%shop_discount_values}}";

    public function safeUp()
    {
        $this->alterColumn($this->tableNameValues, 'value', 'integer NOT NULL');

        $this->createIndex('I_shop_discount_values_value', $this->tableNameValues, 'value');
    }

    public function safeDown()
    {
        $this->dropIndex('I_shop_discount_values_value', $this->tableNameValues);

        $this->alterColumn($this->tableNameValues, 'value', 'string(255) NOT NULL');
    }
}
