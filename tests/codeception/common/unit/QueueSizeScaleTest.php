<?php

namespace tests\codeception\common;

use common\components\queue\Factory;

/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 2019-03-26
 * Time: 16:56
 */
class QueueSizeScaleTest extends \Codeception\Test\Unit
{


    public function testHandlerSizeImport()
    {
        $this->markTestSkipped(
            'Not configure'
        );

        $jsonSizeScale =
            '{
            "Info": 
            {
                "Type": "SIZE_SCALE",
                "Version": "2.0",
                "Source": "KFSS",
                "SourceDetail": "KSAS_DCC.GENERAL_DICTIONARY.SIZE_SCALE_INSERT",
                "Date": "2017-07-21T17:44:04+03:00"
            },
            "Data": 
            { 
                "Guid": "55EFBE82C24C2693E0534301090A15F9",
                "ScaleName": "Bitrix. Размер подушки",
                "ScaleDescr": "SIZE_PILLOWS",
                "IsDeleted" : true,
            }
        }';

        $handler = Factory::factory($jsonSizeScale);
        $handler->execute();


    }

}