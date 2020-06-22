<?php

/**
 * php ./yii imports/multi-photo
 */

namespace console\controllers\imports;

use common\models\cmsContent\CmsContentElement;
use skeeks\cms\models\CmsStorageFile;
use skeeks\cms\models\StorageFile;
use skeeks\sx\File;
use yii\helpers\Console;

/**
 * Class CommentsController
 *
 * @deprecated - пока нет нужды загружать фотки под аб тест
 * @package console\controllers
 */
class MultiPhotoController extends \yii\console\Controller
{
    protected $clusterId = 'element_images';

    // Путь до фоток
    // структура папки $path: <lot_id>/<m|s>/<image|images>/<file.jpg>
    private $path = __DIR__.'/images';

    public function actionIndex()
    {
        if ($this->import()) {
            $this->stdout("Фотки импортированы!\n", Console::FG_GREEN);
        } else {
            $this->stdout("Фотки не импортированы!\n", Console::FG_RED);
        }

        $this->stdout("Импорт Фоток закончен!\n", Console::FG_YELLOW);
    }

    /**
     * Импорт лотов по списку
     *
     * @return bool
     */
    protected function import()
    {
        $products = \common\helpers\Promo::getStarieVsMolodieProducts();

        foreach ($products as $productId) {
            $product = \common\lists\Contents::getContentElementById($productId);

            if (!$product || !$this->importLot($product)) {
                $this->stdout("не удалось импортировать лот: {$product->id}\n", Console::FG_RED);

                return false;
            }
        }

        return true;
    }

    // импорт конкретного лота
    protected function importLot(CmsContentElement $product)
    {
        $this->stdout("Начинаем сбор данных для лота: {$product->id}\n", Console::FG_YELLOW);

        $path = $this->path.DIRECTORY_SEPARATOR.$product->id;

        if (!file_exists($path)) {
            $this->stdout("Путь до '$path' не найден\n", Console::FG_RED);

            return false;
        }

        if (!$this->cleanDefaultImages($product)) {
            $this->stdout("Не удалось очистить старые фото\n", Console::FG_RED);

            return false;
        }

        if (!$this->importLotAge($product, CmsContentElement::IMAGE_AGE_MOLODIE_CODE)) {
            return false;
        }
        if (!$this->importLotAge($product, CmsContentElement::IMAGE_AGE_STARUHI_CODE)) {
            return false;
        }

        return true;
    }

    // истка оригинальных старых фоток
    protected function cleanDefaultImages(CmsContentElement $product)
    {
        $this->stdout('Удаляем старые фотки'.PHP_EOL, Console::FG_YELLOW);

        /** @var StorageFile $oldImage */
        $oldImage = $product->hasOne(StorageFile::className(), ['id' => 'image_id'])
            ->onCondition(['not', ['description_short' => [CmsContentElement::IMAGE_AGE_MOLODIE_CODE, CmsContentElement::IMAGE_AGE_STARUHI_CODE]]])
            ->orOnCondition(['description_short' => null])
            ->one();

        if ($oldImage) {
            $this->stdout('Removing old image: '.$oldImage->id.PHP_EOL, Console::FG_YELLOW);
            $oldImage->delete();
        }

        /** @var StorageFile[] $oldImages */
        $oldImages = $product->hasMany(StorageFile::className(), ['id' => 'storage_file_id'])
            ->onCondition(['not', ['description_short' => [CmsContentElement::IMAGE_AGE_MOLODIE_CODE, CmsContentElement::IMAGE_AGE_STARUHI_CODE]]])
            ->orOnCondition(['description_short' => null])
            ->via('cmsContentElementImages')->all();

        foreach ($oldImages as $oldImage) {
            $this->stdout('Removing old image: '.$oldImage->id.PHP_EOL, Console::FG_YELLOW);
            $oldImage->delete();
        }

        return true;
    }

    // импорт фоток лота по возрасту $age = [m | s]
    protected function importLotAge(CmsContentElement $product, $age)
    {
        $path = $this->path.DIRECTORY_SEPARATOR.$product->id.DIRECTORY_SEPARATOR.$age;

        if (!file_exists($path)) {
            $this->stdout("Путь до '$path' не найден\n", Console::FG_RED);

            return false;
        }

        if (!$this->importLotImage($product, $age)) {
            $this->stdout("Не удалось импортировать основное фото\n", Console::FG_RED);

            return false;
        }
        if (!$this->importLotImages($product, $age)) {
            $this->stdout("Не удалось импортировать дополнительные фото\n", Console::FG_RED);

            return false;
        }

        return true;
    }

    // импорт основного фото
    protected function importLotImage(CmsContentElement $product, $age)
    {
        $path = $this->path.DIRECTORY_SEPARATOR.$product->id.DIRECTORY_SEPARATOR.$age.DIRECTORY_SEPARATOR.'image';

        $this->stdout('Importing main image from : '.$path.PHP_EOL, Console::FG_YELLOW);

        if (!file_exists($path)) {
            $this->stdout("Путь до '$path' не найден\n", Console::FG_RED);

            return false;
        }

        $images = \yii\helpers\FileHelper::findFiles($path);
        if (!$images) {
            $this->stdout("Фоток в папке '$path' не найдено\n", Console::FG_RED);

            return false;
        }
        if (sizeof($images) > 1) {
            $this->stdout("Фоток в папке '$path' больше 1, ожидается 1 основное фото\n", Console::FG_RED);

            return false;
        }

        $vendorFilePath = end($images);

        /** @var File | bool $image */
        $tmpFile = $this->uploadFile($vendorFilePath);
        if (!$tmpFile) {
            $this->stdout("не удалось сохранить файл $vendorFilePath\n", Console::FG_RED);

            return false;
        }

        $imageAttribute = CmsContentElement::getAgeImageNameByCode($age);
        /** @var CmsStorageFile $savedImage */
        $savedImage = $product->{$imageAttribute};

        if ($savedImage) {
            /**
             * Если новая фотка отличается от ранее загруженной то загружаем ее
             */
            if ($savedImage->original_name != $vendorFilePath
                || $savedImage->size != $tmpFile->size()->getBytes()
            ) {
                $savedImage->cluster->update($savedImage->cluster_file, $tmpFile);
                $savedImage->size = $tmpFile->size()->getBytes();
                $savedImage->original_name = $vendorFilePath;
                $savedImage->description_short = $age;

                $savedImage->save(false);

                // удаляем thumbnails
                $savedImage->cluster->deleteTmpDir($savedImage->cluster_file);
            }
        } else {
            $file = \Yii::$app->storage->upload($tmpFile, [
                'original_name' => $vendorFilePath,
                'description_short' => $age,
            ], \Yii::$app->params['storage']['clusters'][$this->clusterId]
            );

            $product->link($imageAttribute, $file);
        }

        return true;
    }

    // импорт дополнительных фото
    protected function importLotImages(CmsContentElement $product, $age)
    {
        $path = $this->path.DIRECTORY_SEPARATOR.$product->id.DIRECTORY_SEPARATOR.$age.DIRECTORY_SEPARATOR.'images';

        $this->stdout('Importing images from : '.$path.PHP_EOL, Console::FG_YELLOW);

        if (!file_exists($path)) {
            $this->stdout("Путь до '$path' не найден\n", Console::FG_RED);

            return false;
        }

        $images = \yii\helpers\FileHelper::findFiles($path);
        if (!$images) {
            $this->stdout("Фоток в папке '$path' не найдено\n", Console::FG_RED);

            return false;
        }

        // сортируем по алфавиту
        sort($images, SORT_NATURAL);

        $imagesAttribute = CmsContentElement::getAgeImagesNameByCode($age);

        /** @var CmsStorageFile[] $savedImages */
        $savedImages = $product->{$imagesAttribute};

        foreach ($images as $vendorFilePath) {
            $this->stdout('Importing image from : '.$vendorFilePath.PHP_EOL, Console::FG_YELLOW);
            /** @var File | bool $image */
            $tmpFile = $this->uploadFile($vendorFilePath);
            if (!$tmpFile) {
                $this->stdout("не удалось сохранить файл $vendorFilePath\n", Console::FG_RED);

                return false;
            }

            foreach ($savedImages as $key => $savedImage) {
                // фотка не менялась
                if ($savedImage->original_name == $vendorFilePath
                    && $savedImage->size == $tmpFile->size()->getBytes()
                ) {
                    unset ($savedImages[$key]);
                    continue 2;
                }
            }
            $file = \Yii::$app->storage->upload($tmpFile, [
                'original_name' => $vendorFilePath,
                'description_short' => $age,
            ], \Yii::$app->params['storage']['clusters'][$this->clusterId]
            );

            $product->link($imagesAttribute, $file);
        }

        // удаляем старые фотки
        foreach ($savedImages as $savedImage) {
            $savedImage->delete();
        }

        return true;
    }

    protected function uploadFile($vendorFilePath)
    {
        $vendorFile = new File($vendorFilePath);

        if ($vendorFile->isExist() === false) {
            var_dump($vendorFilePath);
            var_dump('vendor file not found');

            return false;
        }

        $newFilePath = '/tmp/'.md5(time().$vendorFilePath).".".$vendorFile->getExtension();

        $tmpFile = new File($newFilePath);

        $vendorFile->copy($tmpFile);

        return $tmpFile;

    }
}