<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 2019-06-26
 * Time: 15:38
 */

namespace tests\codeception\common;


use common\models\Product;

class ProductHelperTest extends \Codeception\Test\Unit
{

    public function testUpdateLot()
    {
        $this->assertEquals(100, Product::findOne(1)->new_quantity);
        \common\helpers\Product::updateQuantity(1);
        $this->assertEquals(77, Product::findOne(1)->new_quantity);

    }


    public function testUpdateCard()
    {
        $this->assertEquals(77, Product::findOne(9)->new_quantity);
        \common\helpers\Product::updateQuantity(9);
        $this->assertEquals(80, Product::findOne(9)->new_quantity);

    }

    //Есть остатки на базовой модификации и нет на обычных, но все сущности есть
    public function testUpdateCardWoNormalModQuantity()
    {
        $this->assertEquals(33, Product::findOne(14)->new_quantity);
        \common\helpers\Product::updateQuantity(14);
        $this->assertEquals(0, Product::findOne(14)->new_quantity);
    }

    //Есть только базовые карточка/модификация, обычных модификаций нет
    public function testUpdateCardWithBaseChildsOnlyQuantity()
    {
        $this->assertEquals(33, Product::findOne(19)->new_quantity);
        \common\helpers\Product::updateQuantity(19);
        $this->assertEquals(10, Product::findOne(19)->new_quantity);
    }

}