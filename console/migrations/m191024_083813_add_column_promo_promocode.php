<?php

use yii\db\Migration;

class m191024_083813_add_column_promo_promocode extends Migration
{
    const TABLE_NAME = '{{%promo}}';
    const COLUMN_NAME = 'promocode';

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
