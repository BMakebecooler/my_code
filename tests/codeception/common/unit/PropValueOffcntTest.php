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
class PropValueOffcntTest extends \Codeception\Test\Unit
{
    public function testHandlerCreate()
    {

        $json = '{"Info":{"Type":"PROP_VALUE_OFFCNT","Version":"2.0","Source":"KFSS","SourceDetail":"KSAS_DCC.OFFCNT.PROP_VALUE_OFFCNT_INSERT","Date":"2019-04-02T18:48:00+03:00"},"Data":{"OffcntGuid":"AFD3FF36841149E09C7BB034EFE73BCA","Property":[{"PropGuid":"62E18FAAAE931E5FE0538201090A587C","ItemGuid":null,"PropValue":"N","IsVisibleSite":false},{"PropGuid":"62E18FAAAE971E5FE0538201090A587C","ItemGuid":null,"PropValue":"N","IsVisibleSite":false},{"PropGuid":"62E18FAAAE9F1E5FE0538201090A587C","ItemGuid":null,"PropValue":"N","IsVisibleSite":false}]}}';

        $handler = Factory::factory($json);

        $this->assertTrue($handler  instanceof  HandlerInterface);

    }
    public function testHandlerExecute()
    {
        $json = '{"Info":{"Type":"PROP_VALUE_OFFCNT","Version":"2.0","Source":"KFSS","SourceDetail":"KSAS_DCC.OFFCNT.PROP_VALUE_OFFCNT_INSERT","Date":"2019-04-02T18:48:00+03:00"},"Data":{"OffcntGuid":"AFD3FF36841149E09C7BB034EFE73BCA","Property":[{"PropGuid":"62E18FAAAE931E5FE0538201090A587C","ItemGuid":null,"PropValue":"N","IsVisibleSite":false},{"PropGuid":"62E18FAAAE971E5FE0538201090A587C","ItemGuid":null,"PropValue":"N","IsVisibleSite":false},{"PropGuid":"62E18FAAAE9F1E5FE0538201090A587C","ItemGuid":null,"PropValue":"N","IsVisibleSite":false}]}}';


        $handler = Factory::factory($json);

        $this->assertTrue($handler->execute());
    }
}