<?php

use common\components\queue\Factory;
use common\components\queue\HandlerInterface;

/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 2019-05-08
 * Time: 15:49
 */
class OffcntPriceTest extends \Codeception\Test\Unit
{


    public function testHandlerCreate()
    {
        $json_lot = '{"Info":{"Type":"OFFCNT_PRICE","Version":"2.0","Source":"KFSS","SourceDetail":"KSAS_DCC.OFFCNT.OFFCNT_PRICE_INSERT","Date":"2019-05-08T14:52:01+03:00"},"Data":{"OffcntGuid":"DFD767AD3B8D438D878051E765F0E7CF","PricesVary":true,"Price":[{"TypeGuid":"563E116BD321D870E0534301090AFABA","DateEnd":"9999-12-31 00:00:00","DateStart":"2019-05-08 14:49:54","PriceLoc":472.5,"Note":null}],"PriceMainGuid":null}}';
        $handler = Factory::factory($json_lot);
        $this->assertTrue($handler instanceof HandlerInterface);

    }

    public function testHandlerExecute()
    {
        $json_lot = '{"Info":{"Type":"OFFCNT_PRICE","Version":"2.0","Source":"KFSS","SourceDetail":"KSAS_DCC.OFFCNT.OFFCNT_PRICE_INSERT","Date":"2019-05-08T14:52:01+03:00"},"Data":{"OffcntGuid":"DFD767AD3B8D438D878051E765F0E7CF","PricesVary":true,"Price":[{"TypeGuid":"563E116BD321D870E0534301090AFABA","DateEnd":"9999-12-31 00:00:00","DateStart":"2019-05-08 14:49:54","PriceLoc":472.5,"Note":null}],"PriceMainGuid":null}}';
        $handler = Factory::factory($json_lot);
        $this->assertTrue($handler->execute());

    }
}