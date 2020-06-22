<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 2019-05-30
 * Time: 18:34
 */

namespace tests\codeception\common;


use common\helpers\Strings;

class StringHelperTest extends \Codeception\Test\Unit
{


    public function testParse()
    {
        $lotData = Strings::parsProductName('[007-043-138] Сахарница \"Маков цвет\" (007043138)');

        $this->assertCount(3, $lotData);
        $this->assertEquals('007-043-138', $lotData['NUM']);
        $this->assertEquals('Сахарница \"Маков цвет\"', $lotData['NAME']);
        $this->assertEquals('007043138', $lotData['ID']);
    }

    public function testParse1()
    {
        $lotData = Strings::parsProductName('[006-217-335] Средство для чистки рук «Марго для дачи»  (006217335) Бесцветный Бесцветный');

        $this->assertCount(3, $lotData);
        $this->assertEquals('006-217-335', $lotData['NUM']);
        $this->assertEquals('Средство для чистки рук «Марго для дачи»', $lotData['NAME']);
        $this->assertEquals('006217335', $lotData['ID']);
    }
}