<?php

/**
 * php ./yii test/images/clear-images
 * php ./yii test/images/clear-duplicates
 * php ./yii test/images/clear-unused
 * php ./yii test/images/clear-bad-files
 */

namespace console\controllers\test;

use common\helpers\Msg;
use common\models\cmsContent\CmsContentElement;
use console\controllers\export\ExportController;
use modules\shopandshow\models\common\StorageFile;
use yii\helpers\Console;

/**
 * Class ImagesController
 * @package console\controllers
 */
class ImagesController extends ExportController
{

    /**
     * Удаляет из базы ссылки на изображения, которых реально нет
     */
    public function actionClearImages()
    {
        $chunkSize = 100000;
        $count = StorageFile::find()->count();

        Console::startProgress(0, $count);
        for ($i = 0; $i < $count; $i += $chunkSize) {
            Console::updateProgress($i, $count);

            $images = StorageFile::find()->limit($chunkSize)->offset($i)->all();
            foreach ($images as $image) {
                if (!file_exists($image->getRootSrc())) {
                    try {
                        if (!$image->delete()) {
                            Console::stdout(print_r($image->getErrors()));
                        }
                    } catch (\Throwable $e) {
                        Console::stderr($e->getMessage());
                    }
                }
            }
        }
        Console::endProgress();
    }

    /**
     * удаляет копии фоток, которые задваивались в момент импорта из кфсс
     */
    public function actionClearDuplicates()
    {
        $cmsContentElements = CmsContentElement::find()->where(['content_id' => [PRODUCT_CONTENT_ID, CARD_CONTENT_ID]])->select('id')->asArray()->all();
        $count = sizeof($cmsContentElements);

        Console::startProgress(0, $count);
        foreach ($cmsContentElements as $i => $cmsContentElementId) {
            Console::updateProgress($i, $count);

            $goodImages = [];
            $cmsContentElement = \common\lists\Contents::getContentElementById($cmsContentElementId);
            $images = $cmsContentElement->images;

            foreach ($images as $image) {
                if (!$image->original_name) continue;

                if (!array_key_exists($image->original_name, $goodImages)) {
                    $goodImages[$image->original_name] = true;
                    continue;
                }

                try {
                    if (!$image->delete()) {
                        Console::stdout(print_r($image->getErrors()));
                    }
                } catch (\Throwable $e) {
                    Console::stderr($e->getMessage());
                }

            }
        }
        Console::endProgress();
    }

    /**
     * удаляет копии фоток, которые нигде не используются
     */
    public function actionClearUnused()
    {
        $images = StorageFile::find()
            ->alias('f')
            ->andWhere('not exists (select 1 from cms_content_element ce where ce.image_id = f.id)')
            ->andWhere('not exists (select 1 from cms_content_element_image cei where cei.storage_file_id = f.id)');

        $count = $images->count();

        Console::startProgress(0, $count);
        foreach ($images->all() as $i => $image) {
            Console::updateProgress($i, $count);

            if (!file_exists($image->getRootSrc())) {
                try {
                    if (!$image->delete()) {
                        Console::stdout(print_r($image->getErrors()));
                    }
                } catch (\Throwable $e) {
                    Console::stderr($e->getMessage());
                }
            }

        }
        Console::endProgress();
    }

    /**
     * удаляет плохо сохраненные фотки (побились когда кончилось место)
     */
    public function actionClearBadFiles()
    {
        $cmsContentElements = CmsContentElement::find()->where(['content_id' => [PRODUCT_CONTENT_ID, CARD_CONTENT_ID]])->select('id')->asArray()->all();
        $count = sizeof($cmsContentElements);

        Console::startProgress(0, $count);
        foreach ($cmsContentElements as $i => $cmsContentElementId) {
            Console::updateProgress($i, $count);

            $cmsContentElement = \common\lists\Contents::getContentElementById($cmsContentElementId);
            $images = $cmsContentElement->images;

            if ($cmsContentElement->image_id) {
                $images[] = $cmsContentElement->image;
            }

            foreach ($images as $image) {
                if (!$image->original_name) continue;

                $src = $image->getRootSrc();
                if (file_exists($src) && filesize($src) > 100) {
                    continue;
                }

                try {
                    if (!$image->delete()) {
                        Console::stdout(print_r($image->getErrors()));
                    }
                } catch (\Throwable $e) {
                    Console::stderr($e->getMessage());
                }

            }
        }
        Console::endProgress();
    }
}



