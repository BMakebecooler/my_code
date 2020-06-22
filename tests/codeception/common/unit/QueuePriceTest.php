<?php

namespace tests\codeception\common;

use common\components\queue\Factory;
use common\components\queue\HandlerInterface;

/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 2019-03-26
 * Time: 16:56
 */
class QueuePriceTest extends \Codeception\Test\Unit
{


    public function testHandlerCreate()
    {
        $json = '{"Info":{"Type":"OFFCNT_PRICE","Version":"2.0","Source":"KFSS","SourceDetail":"KSAS_DCC.OFFCNT.OFFCNT_PRICE_INSERT","Date":"2019-03-26T16:32:00+03:00"},"Data":{"OffcntGuid":"31578B5C40714762A112FDC8D0F5E582","PricesVary":false,"Price":[{"TypeGuid":"563E116BD321D870E0534301090AFABA","DateEnd":"9999-12-31 00:00:00","DateStart":"2019-03-26 16:29:53","PriceLoc":620,"Note":null},{"TypeGuid":"563E116BD31BD870E0534301090AFABA","DateEnd":"9999-12-31 00:00:00","DateStart":"2019-03-26 16:29:53","PriceLoc":999,"Note":null},{"TypeGuid":"563E116BD313D870E0534301090AFABA","DateEnd":"9999-12-31 00:00:00","DateStart":"2019-03-26 16:29:53","PriceLoc":999,"Note":null},{"TypeGuid":"563E116BD315D870E0534301090AFABA","DateEnd":"9999-12-31 00:00:00","DateStart":"2019-03-26 16:29:53","PriceLoc":999,"Note":null},{"TypeGuid":"563E116BD311D870E0534301090AFABA","DateEnd":"9999-12-31 00:00:00","DateStart":"2019-03-26 16:29:53","PriceLoc":4399,"Note":null}],"PriceMainGuid":"563E116BD311D870E0534301090AFABA"}}';


        $handler = Factory::factory($json);

        $this->assertTrue($handler  instanceof  HandlerInterface);

    }
    public function testHandlerExecute()
    {
        $json = '{"Info":{"Type":"OFFCNT_PRICE","Version":"2.0","Source":"KFSS","SourceDetail":"KSAS_DCC.OFFCNT.OFFCNT_PRICE_INSERT","Date":"2019-03-26T16:32:00+03:00"},"Data":{"OffcntGuid":"31578B5C40714762A112FDC8D0F5E582","PricesVary":false,"Price":[{"TypeGuid":"563E116BD321D870E0534301090AFABA","DateEnd":"9999-12-31 00:00:00","DateStart":"2019-03-26 16:29:53","PriceLoc":620,"Note":null},{"TypeGuid":"563E116BD31BD870E0534301090AFABA","DateEnd":"9999-12-31 00:00:00","DateStart":"2019-03-26 16:29:53","PriceLoc":999,"Note":null},{"TypeGuid":"563E116BD313D870E0534301090AFABA","DateEnd":"9999-12-31 00:00:00","DateStart":"2019-03-26 16:29:53","PriceLoc":999,"Note":null},{"TypeGuid":"563E116BD315D870E0534301090AFABA","DateEnd":"9999-12-31 00:00:00","DateStart":"2019-03-26 16:29:53","PriceLoc":999,"Note":null},{"TypeGuid":"563E116BD311D870E0534301090AFABA","DateEnd":"9999-12-31 00:00:00","DateStart":"2019-03-26 16:29:53","PriceLoc":4399,"Note":null}],"PriceMainGuid":"563E116BD311D870E0534301090AFABA"}}';


        $handler = Factory::factory($json);

        $this->assertTrue($handler  instanceof  HandlerInterface);
        $this->assertTrue($handler->execute());

    }
}