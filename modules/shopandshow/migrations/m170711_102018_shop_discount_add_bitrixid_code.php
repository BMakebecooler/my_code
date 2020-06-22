<?php

use yii\db\Migration;

class m170711_102018_shop_discount_add_bitrixid_code extends Migration
{
    public function safeUp()
    {

        $this->addColumn('shop_discount', 'code', $this->string());
        $this->addColumn('shop_discount', 'bitrix_id', $this->integer());

    }

    public function safeDown()
    {
        echo "m170711_102018_shop_discount_add_bitrixid_code cannot be reverted.\n";

        return false;
    }

}
