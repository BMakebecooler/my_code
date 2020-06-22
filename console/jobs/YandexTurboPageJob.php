<?php

namespace console\jobs;

use common\seo\YmlCatalogFactory;

/**
 * @author Arkhipov Andrei <arhan89@gmail.com>
 * @copyright (c) K-Gorod
 * Date: 06.06.2019
 * Time: 15:43
 */

class YandexTurboPageJob extends \yii\base\Object implements \yii\queue\Job
{
    /**
     * @param \yii\queue\Queue $queue
     */
    public function execute($queue)
    {
        $dir = \Yii::getAlias('@frontend/web/export/yandex-yml/');
        $filename = "yml.xml";
        $fullPath = $dir . $filename;

        YmlCatalogFactory::create()
            ->make()
            ->save(\Yii::getAlias($fullPath));
    }
}