<?php

namespace tests\codeception\common\_support;

use tests\codeception\common\fixtures\CmsContentFixture;
use tests\codeception\common\fixtures\CmsUserFixture;
use tests\codeception\common\fixtures\CmsTreeFixture;
use tests\codeception\common\fixtures\CmsContentElementFixture;
use Codeception\Module;
use tests\codeception\common\fixtures\ModelhistoryFixture;
use yii\test\FixtureTrait;

/**
 * This helper is used to populate database with needed fixtures before any tests should be run.
 * For example - populate database with demo login user that should be used in acceptance and functional tests.
 * All fixtures will be loaded before suite will be starded and unloaded after it.
 */
class FixtureHelper extends Module
{

    /**
     * Redeclare visibility because codeception includes all public methods that not starts from "_"
     * and not excluded by module settings, in actor class.
     */
    use FixtureTrait {
        loadFixtures as protected;
        fixtures as protected;
        globalFixtures as protected;
        unloadFixtures as protected;
        getFixtures as protected;
        getFixture as protected;
    }

    /**
     * Method called before any suite tests run. Loads User fixture login user
     * to use in acceptance and functional tests.
     * @param array $settings
     */
    public function _beforeSuite($settings = [])
    {
        $this->loadFixtures();
    }

    /**
     * Method is called after all suite tests run
     */
    public function _afterSuite()
    {
        $this->unloadFixtures();
    }

    /**
     * @inheritdoc
     */
    public function fixtures()
    {
        return [
            'user' => [
                'class' => CmsUserFixture::className(),
                'dataFile' => '@tests/codeception/common/fixtures/data/CmsUser.php',
            ],
            'tree' => [
                'class' => CmsTreeFixture::className(),
                'dataFile' => '@tests/codeception/common/fixtures/data/CmsTree.php',
            ],
//            'user' => [
//                'class' => CmsUserFixture::className(),
//                'dataFile' => '@tests/codeception/common/fixtures/data/CmsUser.php',
//            ],
//            'tree' => [
//                'class' => CmsTreeFixture::className(),
//                'dataFile' => '@tests/codeception/common/fixtures/data/CmsTree.php',
//            ],
            CmsContentElementFixture::class,
            ModelhistoryFixture::class,
            CmsContentFixture::class

        ];
    }
}
