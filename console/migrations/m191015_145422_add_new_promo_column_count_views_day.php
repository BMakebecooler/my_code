<?php

use yii\db\Migration;

class m191015_145422_add_new_promo_column_count_views_day extends Migration
{
    const TABLE_NAME = '{{%promo}}';
    const COLUMN_NAME = 'count_views_day';

    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn(self::TABLE_NAME, self::COLUMN_NAME, $this->integer()->defaultValue(0));
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
