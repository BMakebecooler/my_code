<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 19.03.19
 * Time: 14:14
 */

namespace tests\codeception\common\fixtures;

use yii\test\ActiveFixture;

class CmsUserFixture extends ActiveFixture
{
    public $modelClass = 'common\models\user\User';

    public $dataFile = '@tests/codeception/common/fixtures/data/CmsUser.php';
}