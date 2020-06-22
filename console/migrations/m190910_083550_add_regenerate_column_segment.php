<?php

use yii\db\Migration;

class m190910_083550_add_regenerate_column_segment extends Migration
{
    const TABLE_NAME = '{{%segment}}';
    const COLUMN_NAME = 'regenerate';

    public function safeUp()
    {
        $this->addColumn(self::TABLE_NAME, self::COLUMN_NAME, $this->boolean()->defaultValue(0));
        $this->createIndex(self::COLUMN_NAME.'_i', self::TABLE_NAME, self::COLUMN_NAME);
    }

    public function safeDown()
    {
        $this->dropIndex(self::COLUMN_NAME.'_i', self::TABLE_NAME);
        $this->dropColumn(self::TABLE_NAME, self::COLUMN_NAME);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190910_083550_add_regenerate_column_segment cannot be reverted.\n";

        return false;
    }
    */
}
