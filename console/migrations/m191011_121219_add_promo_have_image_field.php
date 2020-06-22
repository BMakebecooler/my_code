<?php

use yii\db\Migration;

class m191011_121219_add_promo_have_image_field extends Migration
{

    const TABLE_NAME = 'promo';

    public function safeUp()
    {
        $this->addColumn(self::TABLE_NAME, 'have_image', $this->boolean()->defaultValue(0));
    }

    public function safeDown()
    {
        echo "m191011_121219_add_promo_have_image_field cannot be reverted.\n";

        $this->dropColumn(self::TABLE_NAME, 'have_image', $this->float());

        return true;
    }
}
