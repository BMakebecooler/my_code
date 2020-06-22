<?php

use yii\db\Migration;

class m191018_081405_add_column_rating_promo_table extends Migration
{
    const TABLE_NAME = '{{%promo}}';
    const COLUMN_NAME = 'rating';

    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn(self::TABLE_NAME, self::COLUMN_NAME, $this->integer()->defaultValue(0));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn(self::TABLE_NAME, self::COLUMN_NAME);
    }

}
