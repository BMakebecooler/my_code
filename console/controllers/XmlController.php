<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 11/01/2019
 * Time: 18:31
 */

namespace console\controllers;


use skeeks\cms\export\models\ExportTask;
use XMLReader;
use Yii;
use yii\console\Controller;
use yii\helpers\Console;

class XmlController extends Controller
{

    public function actionValidFeed()
    {
        foreach (ExportTask::find()->all() as $exportTask) {


            $handler = $exportTask->handler;
            if ($handler) {
                $pos = strpos($handler->file_path, 'xml');

                if (!($pos === false)) {
                    $path = Yii::getAlias('@runtime') . DIRECTORY_SEPARATOR . 'feed_' . Yii::$app->security->generateRandomString() . '.xml';

                    try {
                        file_put_contents($path, fopen("https://shopandshow.ru" . $handler->file_path, 'r'));
                        $xml = XMLReader::open($path);
                        $xml->setParserProperty(XMLReader::VALIDATE, true);
                        $this->stdout('ExportTask ' . $exportTask->name . ' is valid ' . (boolean)$xml->isValid() . PHP_EOL, Console::FG_GREEN);
                    } catch (\Exception $e) {
                        $this->stdout('ExportTask ' . $exportTask->name . ' is not valid ' . (boolean)$xml->isValid() . PHP_EOL, Console::FG_RED);
                    }
                }
            }
        }
    }
}