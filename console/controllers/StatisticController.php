<?php

/**
 * php yii statistic/set-products-without-images-count
 * php yii statistic/generate-file-lots-without-images
 */


namespace console\controllers;

use common\helpers\StatisticProductsImagesHelper;
use common\models\StatisticProductsImages;
use yii\console\Controller;

class StatisticController extends Controller
{

    /**
     * метод добавления новой записи в таблицу статистики учета карточек без фото
     *
     */
    public function actionSetProductsWithoutImagesCount()
    {
        StatisticProductsImagesHelper::setProductsWithoutImagesCount();

    }

    /**
     * метод генерации файла с номерами лотов у которых есть карточки без фото
     */
    public function actionGenerateFileLotsWithoutImages()
    {
        StatisticProductsImagesHelper::generateFileLotsWithoutImages();
    }

}