<?php

use yii\db\Migration;

class m191015_103706_add_promo_column_image_banner extends Migration
{
    const TABLE_NAME = '{{%promo}}';
    const COLUMN_NAME = 'image_banner';

    public function safeUp()
    {
        $this->addColumn(self::TABLE_NAME, self::COLUMN_NAME, $this->string());
    }

    public function safeDown()
    {
        echo "m191015_103706_add_promo_column_image_banner cannot be reverted.\n";

        $this->dropIndex(self::COLUMN_NAME.'_i', self::TABLE_NAME);
    }

}
