<?php

use yii\db\Migration;

class m170814_120740_alter_table_ss_shop_discount_entity_insert_row extends Migration
{
    public function safeUp()
    {
        return $this->execute('INSERT INTO ss_shop_discount_entity(name, class) VALUES("Лукбук","ForLookbook")');
    }

    public function safeDown()
    {
        return $this->execute('DELETE FROM ss_shop_discount_entity WHERE class = "ForLookbook"');
    }
}
