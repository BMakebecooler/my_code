<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 2019-03-26
 * Time: 19:50
 */

namespace tests\codeception\common\fixtures;


use common\models\generated\models\CmsContentElement;
use yii\test\ActiveFixture;

class CmsContentElementFixture extends ActiveFixture
{
    public $modelClass = CmsContentElement::class;

    public $dataFile = '@tests/codeception/common/fixtures/data/сms_content_element.php';

    public $depends = [
        SsGuidsFixture::class
    ];
}