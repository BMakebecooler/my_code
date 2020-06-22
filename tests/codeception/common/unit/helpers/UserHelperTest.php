<?php namespace tests\codeception\common;

use PHPUnit\Framework\TestResult;
use tests\codeception\common\fixtures\CmsUserFixture;

//use tests\codeception\common\unit\DbTestCase;

class UserHelperTest extends \Codeception\Test\Unit //extends DbTestCase
{
    /**
     * @var \tests\codeception\common\UnitTester
     */
    protected $tester;
    protected $userHelper;

    protected function _before()
    {
        $this->userHelper = new \common\helpers\User();
//        $this->tester->haveFixtures([
//            'user' => [
//                'class' => CmsUserFixture::className(),
//                'dataFile' => '/app/tests/codeception/common/fixtures/data/CmsUser.php'
//            ]
//        ]);
    }

    protected function _after()
    {

    }

    public function testIsClassExist()
    {
        $this->assertTrue($this->userHelper instanceof \common\helpers\User);
    }

    public function testIsValidEmail()
    {
        $user = \common\models\user\User::findOne(['username' => 'bayer.hudson']);
        $this->assertTrue($this->userHelper::isValidEmail($user->email));
    }
}