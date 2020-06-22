<?php

use yii\db\Migration;

class m191017_075513_add_column_link_promo_table extends Migration
{
    const TABLE_NAME = '{{%promo}}';
    const COLUMN_NAME = 'url_link';

    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn(self::TABLE_NAME, self::COLUMN_NAME, $this->string());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn(self::TABLE_NAME, self::COLUMN_NAME);
    }
}
