<?php

use yii\db\Migration;

class m180621_094223_alter_table_shop_basket extends Migration
{
    public function safeUp()
    {
        $this->execute("ALTER TABLE shop_basket CHANGE type type int(11) DEFAULT 1");

        $this->createIndex('I_type', 'shop_basket', 'type');

        $this->execute(
            <<<SQL
UPDATE shop_basket SET type = 1 WHERE type IS NULL;
SQL
        );
    }

    public function safeDown()
    {
        $this->execute("ALTER TABLE shop_basket CHANGE type type int(11) DEFAULT null");

        $this->dropIndex('I_type','shop_basket');

        $this->execute(
            <<<SQL
UPDATE shop_basket SET type = null;
SQL
        );
    }
}
