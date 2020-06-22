<?php

use yii\db\Migration;

class m200323_080316_add_segment_disable_column extends Migration
{
    const TABLE_NAME = '{{%segment}}';
    const COLUMN_NAME = '{{%disabled}}';

    public function safeUp()
    {
        $this->addColumn(self::TABLE_NAME, self::COLUMN_NAME, $this->boolean()->defaultValue(0));
    }

    public function safeDown()
    {
//        echo "m200323_080316_add_segment_disable_column cannot be reverted.\n";
        $this->dropColumn(self::TABLE_NAME, self::COLUMN_NAME);

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200323_080316_add_segment_disable_column cannot be reverted.\n";

        return false;
    }
    */
}
