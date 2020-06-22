<?php

use yii\db\Migration;

class m191023_095436_add_column_menu_promo_table extends Migration
{
    const TABLE_NAME = '{{%promo}}';
    const COLUMN_NAME = 'in_menu';

    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn(self::TABLE_NAME, self::COLUMN_NAME, $this->boolean()->defaultValue(0));
        $this->createIndex(self::COLUMN_NAME.'_i', self::TABLE_NAME, self::COLUMN_NAME);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropIndex(self::COLUMN_NAME.'_i', self::TABLE_NAME);
        $this->dropColumn(self::TABLE_NAME, self::COLUMN_NAME);
    }
}
