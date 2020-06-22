<?php

use yii\db\Migration;

/**
 * Class m180205_100211_add_promo_logic_basket_elements_qty
 */
class m180205_100211_add_promo_logic_basket_elements_qty extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->insert('ss_shop_discount_entity', [
            'name' => 'Количество позиций в корзине',
            'class' => 'ForBasketElementsQty'
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->delete('ss_shop_discount_entity', ['class' => 'ForBasketElementsQty']);
    }
}
