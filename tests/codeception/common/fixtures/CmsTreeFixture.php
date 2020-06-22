<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 19.03.19
 * Time: 14:14
 */

namespace tests\codeception\common\fixtures;

use yii\test\ActiveFixture;

class CmsTreeFixture extends ActiveFixture
{
    public $modelClass = 'common\models\Tree';

    public $dataFile = '@tests/codeception/common/fixtures/data/CmsTree.php';
}