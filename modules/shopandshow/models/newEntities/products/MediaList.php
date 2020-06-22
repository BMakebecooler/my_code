<?php

namespace modules\shopandshow\models\newEntities\products;

use common\helpers\Strings;
use common\models\cmsContent\CmsContentElement;
use common\models\ProductProperty;
use console\controllers\queues\jobs\Job;
use modules\shopandshow\models\newEntities\common\CmsContentElementModel;
use skeeks\cms\models\CmsContentElementImage;
use skeeks\cms\models\CmsStorageFile;
use skeeks\cms\models\StorageFile;
use skeeks\sx\File;

class MediaList extends CmsContentElementModel
{
    const VIDEO_PROPERTY_ID = 106;

    const MAP = [
        '62968471C9770BAFE0538201090ACE60' => ['name' => 'images', 'func' => 'collectImages', 'attr' => ''], //Фото
        //'62968471C9780BAFE0538201090ACE60' => ['name' => 'cert',              'func' => '', 'attr' => ''], //Сертификаты
        //'62968471C9790BAFE0538201090ACE60' => ['name' => 'subject_photo',     'func' => '', 'attr' => ''], //Предметное фото
        '62968471C97A0BAFE0538201090ACE60' => ['name' => 'main_image', 'func' => 'saveMainImage', 'attr' => ''], //Главное фото
        //'62968471C97B0BAFE0538201090ACE60' => ['name' => 'banner_cts',        'func' => '', 'attr' => ''], //Баннер - ЦТС (большой)
        //'62968471C97C0BAFE0538201090ACE60' => ['name' => 'banner_promo_big',  'func' => '', 'attr' => ''], //Баннер - Промо (большой)
        //'62968471C97D0BAFE0538201090ACE60' => ['name' => 'banner_promo_cube', 'func' => '', 'attr' => ''], //Баннер - Промо (кубики)
        '62968471C97E0BAFE0538201090ACE60' => ['name' => 'video', 'func' => 'saveYoutubeVideo', 'attr' => 'VIDEO'], //Видео (Youtube)
        '62968471C97F0BAFE0538201090ACE60' => ['name' => 'video_cp', 'func' => '', 'attr' => 'VIDEO_PRICE_DISCOUNTED'], //Видео (Youtube) ЦП
        '62968471C9800BAFE0538201090ACE60' => ['name' => 'video_cts', 'func' => '', 'attr' => 'VIDEO_PRICE_TODAY'], //Видео (Youtube) ЦТС
        '62968471C9810BAFE0538201090ACE60' => ['name' => 'video_ss', 'func' => '', 'attr' => 'VIDEO_PRICE_BASE'], //Видео (Youtube) ШШ
        '62968471C9820BAFE0538201090ACE60' => ['name' => 'video_sale', 'func' => '', 'attr' => 'VIDEO_PRICE_SALE'], //Видео (Youtube) Цена "Распродажа"
        //'62968471C9830BAFE0538201090ACE60' => ['name' => 'detail_photo', 'func' => 'collectImagesDetail', 'attr' => ''], //Детальное фото
    ];
    public $media = [];

    private $images = [];

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'guid' => 'Глобальный идентификатор',
        ];
    }

    public function setCmsContentElement(CmsContentElement $contentElement)
    {
        $this->cmsContentElement = $contentElement;

        $this->setAttributes([
            'guid' => $contentElement->guid->getGuid(),
        ]);
    }

    public function setMediaList(array $mediaList = [])
    {
        $this->media = $mediaList;
    }

    /**
     * @return bool
     */
    public function addData()
    {
        foreach ($this->media as $media) {
            Job::dump('MediaTypeGuid: ' . $media['MediaTypeGuid']);
            Job::dump('MediaName: ' . $media['MediaName']);
            Job::dump('MediaPath: ' . $media['MediaPath']);

            /** @var $mediaTypeElement \common\models\cmsContent\CmsContentElement */
            /*if (!$mediaTypeElement = Guids::getEntityByGuid($media['MediaTypeGuid'])) {
                Job::dump(' mediatype not found');

                return false;
            }*/

            $result = $this->process($media);
            // что-то пошло не так
//            if ($result == false) {
//                return false;
//            }
        }

        return $this->afterProcess();
    }

    /**
     * @param array $media
     *
     * @return bool|mixed
     */
    protected function process(array $media)
    {
        $mediaTypeElement = $this->getMediaTypeByGuid($media['MediaTypeGuid']);
        if (!$mediaTypeElement) {
            Job::dump(' media type not supported');

            return false;
        }

        if (!empty($mediaTypeElement['func'])) {
            if (empty($media['MediaPath'])) {
                Job::dump(' empty MediaPath');
                Job::dump($media);
                // игнорим такие картинки
                return true;
            }

            if (method_exists($this, $mediaTypeElement['func'])) {
                return call_user_func([$this, $mediaTypeElement['func']], $media);
            }

            Job::dump(' method "' . $mediaTypeElement['func'] . '" not found');

            return false;
        } elseif (!empty($mediaTypeElement['attr'])) {
            //$this->relatedPropertiesModel[$mediaTypeElement['attr']] = $media['MediaPath'];
            ProductProperty::savePropByCode($this->cmsContentElement->id, $mediaTypeElement['attr'], $media['MediaPath']);
            return true;
        }

        Job::dump(' no func or attr provided');
        return false;
    }

    /**
     * @param $mediaTypeGuid
     *
     * @return bool|array
     */
    protected function getMediaTypeByGuid($mediaTypeGuid)
    {
        if (isset(self::MAP[$mediaTypeGuid])) {
            return self::MAP[$mediaTypeGuid];
        }
        return false;
    }

    /**
     * @return bool
     */
    protected function afterProcess()
    {
        // сохраняем накопленные фотки
        if ($this->images) {
            if (!$result = $this->saveImages($this->images)) {
                Job::dump(' failed to save images');
                return false;
            }
        }

        return true;
        //return $this->saveRelatedProperties();
    }

    /**
     * сохраняет пачку накопленных фоток
     * @param array $images
     *
     * @return bool
     */
    protected function saveImages(array $images)
    {
        /** @var StorageFile[] $oldImages */
        $oldImages = $this->cmsContentElement->getImages()->indexBy('original_name')->all();

        Job::dump('Find $oldImages.' . count($oldImages));


        Job::dump('>> Saving regular images.');

        foreach ($images as $media) {

            try {
                Job::dump('---');
                Job::dump('Working with ' . $media['MediaPath']);
                $tmpFile = $this->getFileImage($media['MediaPath']);

                if (!$tmpFile) {
                    Job::dump('file not found. Skip');
                    continue;
                }

                //Данный формат похоже не используется
                $priority = 100;
                if (preg_match('/_(\d+)\.(jpg|jpeg|gif|png|bmp)/xi', $media['MediaPath'], $matches) && !empty($matches)) {
                    $priority *= ((int)$matches[1] + 1);
                } elseif (!empty($media['MediaOrder'])) {
                    $priority = $media['MediaOrder'];
                }

                if (array_key_exists($media['MediaPath'], $oldImages)) {
                    /** @var StorageFile $oldImage */
                    $oldImage = $oldImages[$media['MediaPath']];
                    Job::dump("Element has this image. imageID={$oldImage->id}");
                    /**
                     * Если новая фотка отличается от ранее загруженной то загружаем ее
                     */

                    if ($oldImage->original_name != $media['MediaPath']
                        || $oldImage->size != $tmpFile->size()->getBytes()
                    ) {
                        $oldImage->cluster->priority = $priority;
                        $oldImage->cluster->update($oldImage->cluster_file, $tmpFile);

                        $oldImage->size = $tmpFile->size()->getBytes();
                        $oldImage->name = $media['MediaName'];
                        $oldImage->original_name = $media['MediaPath'];
                        $oldImage->description_short = $media['MediaNotes'];
                        Job::dump(" updated image [{$oldImage->id}] {$oldImage->original_name} [Priority={$priority}]");

                        $oldImage->save(false);
                    }

                    if (!empty($media['MediaOrder'])) {
                        Job::dump("updated imageId='{$oldImage->id}' priority [{$priority}] for element='{$this->cmsContentElement->id}'");
                        $contentElementImage = CmsContentElementImage::find()->where([
                            'storage_file_id' => $oldImage->id,
                            'content_element_id' => $this->cmsContentElement->id
                        ])->one();

                        /** @var $contentElementImage CmsContentElementImage */
                        if ($contentElementImage) {
                            $contentElementImage->priority = $priority;
                            $contentElementImage->save();
                        }
                    }

                    // фотку обновили (или нет)
                    Job::dump('Unset from $oldImages MediaPath - ' . $media['MediaPath'] . " [id={$oldImage->id}]");


                    unset($oldImages[$media['MediaPath']]);

                } else {
                    if ($file = $this->uploadFileImage($tmpFile, $media)) {
                        $this->cmsContentElement->link('images', $file, [
                            'priority' => $priority,
                        ]);
                        Job::dump(" new image [{$file->id}] {$media['MediaPath']} [Priority={$priority}]");

                        // Если у товара еще нет основного фото - прикрепить первое из дополнительных фото на место главного
                        if ($this->cmsContentElement->image === null) {
                            Job::dump("Main image empty. Set from adds [file={$file->id}]");
                            $this->cmsContentElement->link('image', $file);
                        }
                    } else {
                        Job::dump(' failed upload image ' . $media['MediaPath']);
                        continue;
                    }
                }

            } catch (\Exception $e) {
                Job::dump($e->getMessage());
                continue;
            }
        }

        // clean old images
        foreach ($oldImages as $oldImage) {
            if ($oldImage->id != $this->cmsContentElement->image_id) {


                $cmsContentElemntImage = CmsContentElementImage::find()->andWhere(['storage_file_id' => $oldImage->id])->andWhere(['content_element_id' => $this->cmsContentElement->id])->one();
                Job::dump(" Deleted (unlink) old image ({$oldImage->id}) {$oldImage->original_name} for element {$this->cmsContentElement->id}");
                $cmsContentElemntImage->delete();

//                if (!CmsContentElement::find()->andWhere(['image_id' => $oldImage->id])->exists()) {
//
//                    if () {
//                        Job::dump(' deleted old image (' . $oldImage->id . ') ' . $oldImage->original_name);
//                        if (!$oldImage->delete()) {
//                            \Yii::error('Can`t delete old image (' . $oldImage->id . ') ' . $oldImage->original_name, true);
//                        }
//                    }
//                }
            }
        }

        return true;
    }

    /**
     * собирает фотки для массовой обработки пачкой
     * @param array $media
     *
     * @return bool
     */
    protected function collectImages(array $media)
    {
        $this->images[$media['MediaPath']] = $media;

        return true;
    }

    /**
     * @param array $media
     *
     * @return bool
     */
    protected function collectImagesDetail(array $media)
    {
        unset($this->images[$media['MediaPath']]);

        array_unshift($this->images, $media);

        return true;
    }

    protected function saveYoutubeVideo($media)
    {
        $value = Strings::getYoutubeCodeFromString($media['MediaPath']);
        if ($value) {
            try {
                Job::dump('>> Saving main video.');
                $prop = ProductProperty::getElementProperty($this->cmsContentElement->id, self::VIDEO_PROPERTY_ID);
                if (!$prop) {
                    $prop = new ProductProperty();
                    $prop->element_id = $this->cmsContentElement->id;
                    $prop->property_id = self::VIDEO_PROPERTY_ID;
                    $prop->value = $value;
                } else {
                    $prop->value = $value;
                }
                $prop->save();

                return true;

            } catch (\Exception $e) {
                Job::dump($e->getMessage());
            }
        } else {
            Job::dump(' cant get video code ' . $media['MediaPath']);

            return false;
        }
    }

    /**
     * Сохраняет главное фото
     * @param $media
     *
     * @return bool
     */
    protected function saveMainImage($media)
    {
        try {
            Job::dump('>> Saving main image.');

            /** @var File $tmpFile */
            if ($tmpFile = $this->getFileImage($media['MediaPath'])) {

                //Кейсы
                //1) Фотки в элементе нет - загружаем
                //2) Фотка в элементе есть...
                //2.1) Имя статое, размер новый - обновляем сам файл и все
                //2.2) Имя новое...
                //2.2.1) Ищем в базе файл с этим новым именем, если находим - аттачим // DEPRECATED
                //2.2.2) Если не находим - загружаем

                //ИТОГО
                //Загружаем файл и аттачим к элементу если: В элементе нет фотки // фотка есть, но пути разные
                //Ничего не делаем если: фотка есть, пути одинаковые, размеры одинаковые
                //Обновляем только файл: фотка есть, пути одинаковые, размеры разные


                //Выясняем что надо делать
                $action = '';
                $file = null;
                if ($oldImage = $this->cmsContentElement->image) {
                    //Старая фотка есть, решаем, загрузить новую, обновить текущую или ничего не делать
                    if ($oldImage->original_name != $media['MediaPath']) {

                        //[DEPRECATED]
                        //$file = CmsStorageFile::find()->where('original_name = :original_name')->params([':original_name' => $media['MediaPath']])->one();

                        if ($file) {
                            $action = 'link';
                        } else {
                            $action = 'upload';
                        }
                    } elseif ($oldImage->size != $tmpFile->size()->getBytes()) {
                        $action = 'update';
                    }
                } else {
                    $action = 'upload';
                }

                switch ($action) {
                    case 'upload':

                        if ($file = $this->uploadFileImage($tmpFile, $media)) {
                            $this->cmsContentElement->link('image', $file);
                            Job::dump(" new image [id={$file->id}] {$media['MediaPath']}");
                        } else {
                            Job::dump(' failed upload image ' . $media['MediaPath']);

                            return false;
                        }

                        break;
                    case 'update':

                        //Обновление файла по тому же пути
                        Job::dump("Same path, new size - update file [id={$oldImage->id}] {$media['MediaPath']}");
                        $oldImage->cluster->update($oldImage->cluster_file, $tmpFile);

                        $oldImage->size = $tmpFile->size()->getBytes();
                        $oldImage->name = $media['MediaName'];
                        $oldImage->original_name = $media['MediaPath'];
                        $oldImage->description_short = $media['MediaNotes'];

                        if (!$oldImage->save(false)) {
                            Job::dump($oldImage->getErrors());
                            return false;
                        }

                        break;
                    case 'link':

                        if ($file) {
                            Job::dump(" image found [id={$file->id}]. Updating link.");
                            $this->cmsContentElement->image_id = $file->id;
                            $this->cmsContentElement->save();
                        }

                        break;
                }

                return true;
            } else {
                Job::dump(' cant get image ' . $media['MediaPath']);

                return false;
            }

        } catch (\Exception $e) {
            Job::dump($e->getMessage());
        }

        return false;
    }
}