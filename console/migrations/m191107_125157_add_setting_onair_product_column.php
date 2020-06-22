<?php

use yii\db\Migration;

class m191107_125157_add_setting_onair_product_column extends Migration
{
    const TABLE_NAME = '{{%setting}}';
    const COLUMN_NAME = 'onair_product_id';

    public function safeUp()
    {
        $this->addColumn(self::TABLE_NAME, self::COLUMN_NAME, $this->integer()->unsigned());
    }

    public function safeDown()
    {
        $this->dropColumn(self::TABLE_NAME, self::COLUMN_NAME);
        return true;
    }
}
