<?php
/**
 * Created by PhpStorm.
 * User: ignatenkovnikita
 * Web Site: http://IgnatenkovNikita.ru
 */

namespace tests\codeception\common\unit;

use common\models\CmsTreeTypePropertyCode;
use common\models\TreeFactory;
use yii\helpers\Console;
use yii\helpers\FileHelper;

class EmptySaveTest extends \Codeception\Test\Unit
{

//    public function setUp()
//    {
//        parent::setUp();
//        $this->_login(1);
//        $this->_configureCurrentSite(1);
//    }

    /**
     * @dataProvider providerModels
     */
    public function testEmptySaveModelBackend($model)
    {

        $skipModel = [
            'common\models\BUFECommABCw',
            'common\models\BUFECommDop',
            'common\models\BUFECommABC',
            'common\models\BUFECommPairCTS',
            'common\models\BUFECommN1234',
            'common\models\BUFECommTop6Lots',
            'common\models\BUFSiteTv',
            'common\models\BUFECommDayOnLine',
            'common\models\BUFECommProducts',
            'common\models\ProductPriceType',

            'common\models\CmsContentElement',
            'common\cmsContent\CmsContentElement',

            'common\models\NewProduct',

            'common\models\ProductParamType',
            'common\models\ProductParamProduct',
            'common\models\ProductParam',
            'common\models\ProductParamCatalog',
            'common\models\ProductParamCategory',
            'common\models\Segment',
            'common\models\Promo',
            'common\models\SiteOrder',
            CmsTreeTypePropertyCode::class,
            TreeFactory::class
        ];
        $allowSave = [
            'common\models\CmsUserEmail',
            'common\models\ProductAbcAddition',
            'common\models\ShopProduct',
            'common\models\Tree',
            'common\models\ProductAbc',
            'common\models\QueueLog',
            'common\models\Setting',
            'common\models\Seo',
            'common\models\CmsTree',
            'common\models\ShopFuser',
        ];

        Console::ansiFormat($model);
        echo Console::ansiFormat($model);

        if (!in_array($model, $skipModel)) {
            //Что бы было понятно какой класс проверяется
            //echo Console::stdout(' | ' . var_export($model, true). PHP_EOL);

            $modelObject = \Yii::createObject($model);
            if (in_array($model, $allowSave)) {
                $r = $modelObject->save();
                $this->assertTrue($r);
            } else {
                $r = $modelObject->save();
                $this->assertFalse($r);
            }
        }

    }

    public function providerModels()
    {
        return $this->fillModel();
    }

    protected function fillModel()
    {
        $models = [];
        $path = \Yii::getAlias('@common/models/');
        $files = FileHelper::findFiles($path, ['only' => ['*.php'], 'recursive' => false]);

        foreach ($files as $index => $file) {
            $fileName = basename($file, '.php');
            $models[] = ['common\models\\' . $fileName];
        }

        return $models;
    }

//    public function testEmptySaveModelBackend()
//    {
//        $models = self::getModels();
//
//        foreach ($models as $model) {
//            codecept_debug($model);
//
//            $model = \Yii::createObject($model);
//            $this->assertFalse($model->save());
//        }
//    }
//
//    protected static function getModels()
//    {
//        $models = [];
//        $path = \Yii::getAlias('@backend/models/');
//        $files = FileHelper::findFiles($path, ['only' => ['*.php'], 'recursive' => false]);
//
//        foreach ($files as $index => $file) {
//            $fileName = str_replace($path, '', $file);
//            $fileName = str_replace('.php', '', $fileName);
//            $models[] = 'backend\models' . $fileName;
//        }
//
//        return $models;
//    }

}