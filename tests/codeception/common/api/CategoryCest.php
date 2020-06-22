<?php namespace tests\codeception\common;
use tests\codeception\common\ApiTester;

class CategoryCest
{
    public function _before(ApiTester $I)
    {

    }

    // tests
    public function testGetCategory(ApiTester $I)
    {
        $I->sendGet('/category');
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); //200
        $I->seeResponseContainsJson(['id' => 1626]); //id directory moda
    }
}
