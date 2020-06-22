<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 2019-06-25
 * Time: 15:37
 */

namespace tests\codeception\common\fixtures;


use common\models\generated\models\CmsContent;
use yii\test\ActiveFixture;

class CmsContentFixture extends ActiveFixture
{
    public $modelClass = CmsContent::class;

    public $dataFile = '@tests/codeception/common/fixtures/data/сms_content.php';

//    public $depends = [
//        SsGuidsFixture::class
//    ];
}