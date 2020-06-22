<?php

use yii\db\Migration;

class m191112_111235_add_promo_columns_flags extends Migration
{
    const TABLE_NAME = '{{%promo}}';
    const COLUMN_NAMES = ['in_main','in_actions'];

    /**
     * @inheritdoc
     */
    public function up()
    {
        foreach (self::COLUMN_NAMES as $column_name) {
            $this->addColumn(self::TABLE_NAME, $column_name, $this->boolean()->defaultValue(1));
            $this->createIndex($column_name . '_i', self::TABLE_NAME, $column_name);
        }
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        foreach (self::COLUMN_NAMES as $column_name) {
            $this->dropIndex($column_name.'_i', self::TABLE_NAME);
            $this->dropColumn(self::TABLE_NAME, $column_name);
        }
    }

}
