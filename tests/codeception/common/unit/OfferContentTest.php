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
class OfferContentTest extends \Codeception\Test\Unit
{
    public function testHandlerCreate()
    {
        $json_lot = '{"Info":{"Type":"OFFER_CONTENT","Version":"2.0","Source":"KFSS","SourceDetail":"KSAS_DCC.OFFCNT.OFFER_CONTENT_INSERT","Date":"2019-03-29T08:08:00+03:00"},"Data":{"Guid":"2EB65634CFFC4CBB8231CED4416B1CD0","Id":2305624,"ParentGuid":null,"IdParent":null,"Type":"LOT","Name":"[006-322-502] Сабо женские «Марджи» (006322502)","Active":true,"IsSet":false,"IsFragile":false,"IsBase":null}}';
        $handler = Factory::factory($json_lot);
        $this->assertTrue($handler  instanceof  HandlerInterface);

    }

    public function testHandlerExecuteLot()
    {
        $json_lot = '{"Info":{"Type":"OFFER_CONTENT","Version":"2.0","Source":"KFSS","SourceDetail":"KSAS_DCC.OFFCNT.OFFER_CONTENT_INSERT","Date":"2019-03-29T08:08:00+03:00"},"Data":{"Guid":"2EB65634CFFC4CBB8231CED4416B1CD0","Id":2305624,"ParentGuid":null,"IdParent":null,"Type":"LOT","Name":"[006-322-502] Сабо женские «Марджи» (006322502)","Active":true,"IsSet":false,"IsFragile":false,"IsBase":null}}';
        $handler = Factory::factory($json_lot);
        $this->assertTrue($handler->execute());
    }

    public function testHandlerExecuteMod()
    {
        $json_mod = '{"Info":{"Type":"OFFER_CONTENT","Version":"2.0","Source":"KFSS","SourceDetail":"KSAS_DCC.OFFCNT.OFFER_CONTENT_INSERT","Date":"2019-03-29T08:08:00+03:00"},"Data":{"Guid":"9B8540E49A8B44E8A0C6953A296F438D","Id":2305634,"ParentGuid":"841D002AC2437575E0538201090AF587","IdParent":2305632,"Type":"MOD","Name":"[006-322-502] Сабо женские «Марджи» (006322502)","Active":true,"IsSet":false,"IsFragile":false,"IsBase":true}}';
        $handler = Factory::factory($json_mod);
        $this->assertFalse($handler->execute());
    }
}