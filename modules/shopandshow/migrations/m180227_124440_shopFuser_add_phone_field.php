<?php

use yii\db\Migration;

class m180227_124440_shopFuser_add_phone_field extends Migration
{

    public function safeUp()
    {
        try {
            $this->addColumn('shop_fuser', 'phone', $this->string(64));
        } catch (Exception $exception) {
            return true;
        }
    }

    public function safeDown()
    {
        try {
            $this->dropColumn('shop_fuser', 'phone');
        } catch (Exception $exception) {
            return true;
        }
    }
}
