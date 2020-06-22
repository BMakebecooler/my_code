<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 2019-03-26
 * Time: 19:57
 */

namespace tests\codeception\common\fixtures;


use common\models\generated\models\SsGuids;
use yii\test\ActiveFixture;

class SsGuidsFixture extends ActiveFixture
{
    public $modelClass = SsGuids::class;

    public $dataFile = '@tests/codeception/common/fixtures/data/ss_guids.php';

}