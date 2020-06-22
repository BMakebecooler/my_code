<?php/** * php ./yii test/mongo/test-insert */namespace console\controllers\test;use common\helpers\Developers;use console\controllers\export\ExportController;use yii\helpers\Console;/** * Class MongoController * @package console\controllers */class MongoController extends ExportController{    public function actionTestInsert()    {        $mongoDB = \Yii::$app->mongodb->createCommand();        Developers::getMemoryUsageStart('mongo insert test');        $rand = rand(10, 500);        $data = array_combine(range(0, $rand), range(0, $rand));        for ($x = 0; $x <= 100; $x++) {            $mongoDB->addInsert($data);        }        $this->stdout("\n\nMemory: ".(Developers::getMemoryUsageEnd('mongo insert test'))."\n", Console::FG_YELLOW);        $mongoDB->executeBatch('test-inserts');    }}