<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 05/02/2019
 * Time: 17:08
 */

namespace modules\api\controllers\v1;


use skeeks\cms\export\models\ExportTask;
use yii\rest\Controller;

class XmlController extends Controller
{


    public function actionList()
    {

        $array = [];

        foreach (ExportTask::find()->all() as $exportTask) {


            $handler = $exportTask->handler;
            if ($handler) {
                $pos = strpos($handler->file_path, 'xml');
                if (!($pos === false)) {
                    $array[] = [
                        'name' => $handler->name,
                        'url' => "https://shopandshow.ru" . $handler->file_path,
                        'schema' => $handler->getNameType()
                    ];
                }
            }
        }

        return $array;


    }
}