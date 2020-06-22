<?php

namespace tests\codeception\common;

use common\components\queue\Factory;
use common\components\queue\HandlerInterface;
use common\models\Product;

/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 2019-03-26
 * Time: 16:56
 */
class NewQuantityTest extends \Codeception\Test\Unit
{

    public function testHandlerCreate()
    {
        $json = '{"Info":{"Type":"ReserveMod","Version":"2.0","Source":"KFSS","SourceDetail":"KSAS_DCC.OFFCNT.CL_RESERV_OFFCNT_UPDATE","Date":"2019-04-01T12:05:46+03:00"},"Data":{"Guid":"4D0B2A6F6B40432FB6C66620CF6EBFFC","ChannelGuid":"5DF1A9608C3C1BB4E0538201090A83D8","DateStart":"2019-04-01T12:05:46+03:00","DateEnd":"9999-12-31T12:05:46+03:00","Stop":75,"ReserveValue":0,"Remainder":0,"CanSell":75}}';
        $handler = Factory::factory($json);
        $this->assertTrue($handler  instanceof  HandlerInterface);

    }
    public function testHandlerExecute()
    {
        $json = '{"Info":{"Type":"ReserveMod","Version":"2.0","Source":"KFSS","SourceDetail":"KSAS_DCC.OFFCNT.CL_RESERV_OFFCNT_UPDATE","Date":"2019-04-01T12:05:46+03:00"},"Data":{"Guid":"4D0B2A6F6B40432FB6C66620CF6EBFFC","ChannelGuid":"5DF1A9608C3C1BB4E0538201090A83D8","DateStart":"2019-04-01T12:05:46+03:00","DateEnd":"9999-12-31T12:05:46+03:00","Stop":75,"ReserveValue":0,"Remainder":0,"CanSell":75}}';
        $handler = Factory::factory($json);
        $this->assertTrue($handler->execute());


        $product = Product::getModelByGuid('4D0B2A6F6B40432FB6C66620CF6EBFFC');
        $this->assertEquals(75, $product->new_quantity);
    }
}