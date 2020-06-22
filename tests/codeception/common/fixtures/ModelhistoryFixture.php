<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 2019-06-14
 * Time: 13:57
 */

namespace tests\codeception\common\fixtures;


use yii\test\ActiveFixture;

class ModelhistoryFixture extends ActiveFixture
{
    public $tableName = 'modelhistory';
    public $modelClass = 'common\models\generated\models\Modelhistory';


    public $dataFile = '@tests/codeception/common/fixtures/data/Modelhistory.php';
}