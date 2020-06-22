<?php

/**
 * php ./yii tools/cache/clear-tag top-menu
 * php ./yii tools/cache/flush
 */

namespace console\controllers\tools;

use console\controllers\export\ExportController;
use yii\caching\TagDependency;
use yii\helpers\Console;

/**
 * Class CacheController
 * @package console\controllers
 */
class CacheController extends ExportController
{

    /**
     * Чистка кеша по тегу
     * @param $tag
     */
    public function actionClearTag($tag)
    {
        TagDependency::invalidate(\Yii::$app->cache, $tag);
    }

    public function actionFlush()
    {
        //Очищаем кеш и ассеты
        \Yii::$app->frontendCache->flush();
        $this->stdout("Кеш почищен!" . PHP_EOL, Console::FG_GREEN);

        return true;
    }
}



