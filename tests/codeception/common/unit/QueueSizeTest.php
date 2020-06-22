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
class QueueSizeTest extends \Codeception\Test\Unit
{


    public function testHandlerSizeImport()
    {

        $this->markTestSkipped(
            'Not configure'
        );


        $jsonSize = '
        {
            "Info": 
            { 
                "Type": "MERCH_SIZE",
                "Version": "2.0", 
                "Source": "KFSS",
                "SourceDetail": "KSAS_DCC.GENERAL_DICTIONARY.MERCH_SIZE_INSERT",
                "Date": "2017-07-21T17:44:04+03:00"
            },
            "Data": {
                "Guid": "5665BBAE98402E0FE0534301090AABA6",
                "SizeCode": "4",
                "SizeName": "1",
                "SizeDescr": "1",
                "SizeScaleGuid": "5665BBAE98492E0FE0534301090AABA6",
                "RelatedSizeGuid": ["55EDB97686B0219EE0534301090A35B7", "57D21F433F92A03DE0534301090A5A44", "57E4C48A1395C7E6E0534301090A4F4A"] ,
                "RelatedSizeScaleGuid": ["55EDB97686B0219EE0534301090A35B7", "57D21F433F92A03DE0534301090A5A44", "57E4C48A1395C7E6E0534301090A4F4A"] ,
                "SizeInScaleOrder": 1
         
            }
        }
        ';

        $jsonSize = '{ "Info": { "Type": "MERCH_SIZE", "Version": "2.0", "Source": "KFSS", "SourceDetail": "KSAS_DCC.GENERAL_DICTIONARY.MERCH_SIZE_INSERT", "Date": "2017-07-21T17:44:04+03:00" }, "Data": { "Guid": "5665BBAE98402E0FE0534301090AABA6", "SizeCode": "4", "SizeName": "1", "SizeDescr": "1", "SizeScaleGuid": "5665BBAE98492E0FE0534301090AABA6", "RelatedSizeGuid": ["55EDB97686B0219EE0534301090A35B7", "57D21F433F92A03DE0534301090A5A44", "57E4C48A1395C7E6E0534301090A4F4A"] , "RelatedSizeSaleGuid": ["55EDB97686B0219EE0534301090A35B7", "57D21F433F92A03DE0534301090A5A44", "57E4C48A1395C7E6E0534301090A4F4A"] , "SizeInScaleOrder": 1 } }';


        $handler = Factory::factory( $jsonSize);
        $handler->execute();

    }

}