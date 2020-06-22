<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 2019-06-14
 * Time: 12:57
 */

namespace tests\codeception\common;


use Codeception\Util\Debug;
use common\models\generated\models\Modelhistory;
use common\models\Product;

class ModelHistoryTest extends \Codeception\Test\Unit
{


    public function testSave(){
        $product = Product::findOne(1);
        $r = $product->save();
        Debug::debug($product->errors);
        $this->assertTrue($r);

        echo 'count';
        print_r(Modelhistory::find()->count());

        $this->assertEquals(1, Modelhistory::find()->count());

        $product->new_price  = 1;
        $product->new_lot_name  = 'asdfasdf';
        $this->assertTrue($product->save());

        $this->assertEquals(2, Modelhistory::find()->count());


    }
}