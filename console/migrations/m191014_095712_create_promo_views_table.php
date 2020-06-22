<?php

use yii\db\Migration;

/**
 * Handles the creation of table `promo_views`.
 */
class m191014_095712_create_promo_views_table extends Migration
{
    const TABLE_NAME = '{{%promo}}';
    const COLUMN_NAME = 'count_views';

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
