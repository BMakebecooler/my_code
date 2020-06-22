<?php
namespace tests\codeception\common;

class CmsTreeTest extends \Codeception\Test\Unit
{
    /**
     * @var \tests\codeception\common\UnitTester
     */
    protected $tester;
    protected $userHelper;

    protected function _before()
    {

    }

    protected function _after()
    {

    }

    public function testFindOneTreeNode()
    {
        $treeNode = \common\models\Tree::findOne(['id' => 9]);
        $this->assertTrue($treeNode->code == 'catalog');
    }
}