<?php

namespace tests\codeception\common;


use Yii;


class SMSTest extends \Codeception\Test\Unit
{
    public function testHandlerSend()
    {
        $this->markTestSkipped('skipped');

        $sends = [
//            ['phone' => '79777701766','text' => 'Test'],
//            ['phone' => '79777701766','text' => 'Кириллический текст'],
//            ['phone' => '79055370383','text' => 'Кириллический текст'],
//                ['phone' => '79055370383','text' => 'Новый товар Test!!! && 8**** *(&(&&(*!!)#_)(_)*#'],
            ['phone' => '79055370383','text' => 'Новый товар Test!!! && 8**** *(&(&&(*!!)#_)(_)*#']
//              ['phone' => '79055370383','text' => 'В чащах юга жил бы цитрус? Да, но фальшивый экземпляръ!']
        ];

        foreach ($sends as $data){
            $send = Yii::$app->sms->sendSms($data['phone'],$data['text']);
            $this->assertTrue($send);
        }
    }
}