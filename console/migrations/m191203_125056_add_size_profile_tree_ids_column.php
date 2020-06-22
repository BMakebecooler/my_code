<?php

use yii\db\Migration;

class m191203_125056_add_size_profile_tree_ids_column extends Migration
{
    const TABLE_NAME = '{{%size_profile}}';
    const COLUMN_NAME = 'tree_ids';

    public function safeUp()
    {
        $this->addColumn(self::TABLE_NAME, self::COLUMN_NAME, $this->text());
        return true;
    }

    public function safeDown()
    {
        $this->dropColumn(self::TABLE_NAME, self::COLUMN_NAME);
        return true;
    }

}
