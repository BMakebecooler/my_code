<?php

use yii\db\Migration;

class m180508_091710_add_promo_entity_cts_plus_one extends Migration
{
    public function safeUp()
    {
        $this->insert('ss_shop_discount_entity', [
            'name' => 'Цтс+лот',
            'class' => 'ForCtsPlusOne'
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->delete('ss_shop_discount_entity', ['class' => 'ForCtsPlusOne']);
    }

}
