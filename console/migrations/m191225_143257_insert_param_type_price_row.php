<?php

use yii\db\Migration;

class m191225_143257_insert_param_type_price_row extends Migration
{

    const TABLE_NAME = '{{%product_param_type}}';

    public function safeUp()
    {
        $this->insert(self::TABLE_NAME, [
            'name' => 'Price',
            'guid' => 'KFSS_PRICE',
            'code' => 'KFSS_PRICE',
            'sort' => 1,
            'active' => 1
        ]);
    }

    public function safeDown()
    {
        echo "m191225_143257_insert_param_type_price_row cannot be reverted.\n";

        return false;
    }
}
