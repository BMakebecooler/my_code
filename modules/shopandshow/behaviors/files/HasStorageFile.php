<?php

namespace modules\shopandshow\behaviors\files;

use skeeks\cms\models\behaviors\HasStorageFile as SxHasStorageFile;
use skeeks\cms\models\CmsStorageFile;
use skeeks\cms\models\StorageFile;
use skeeks\sx\File;

/**
 * Created by PhpStorm.
 * User: ubuntu5
 * Date: 26.12.17
 * Time: 14:06
 */
class HasStorageFile extends SxHasStorageFile
{

    /**
     * Режим обновления файлов с сохранением пути
     * @var bool
     */
    public $isUpdatedFile = false;


    /**
     * Загрузка файлов в хранилище и их сохранение со связанной сущьностью
     *
     * @param $e
     */
    public function saveStorgaFile($e)
    {
        foreach ($this->fields as $fieldCode) {
            /**
             * Удалить старые файлы
             */
            if ($this->owner->isAttributeChanged($fieldCode)) {
                if (
                    $this->isUpdatedFile === false &&
                    $this->owner->getOldAttribute($fieldCode) &&
                    $this->owner->getOldAttribute($fieldCode) != $this->owner->{$fieldCode}
                ) {
                    $this->_removeFiles[] = $this->owner->getOldAttribute($fieldCode);
                }
            }

            // Я так понимаю, этот кодж должен выполняться, если где-то используется не аяксовый аплоадер, в этом случае аплоадим файл
            if ($this->owner->{$fieldCode} && is_string($this->owner->{$fieldCode}) && ((string)(int)$this->owner->{$fieldCode} != (string)$this->owner->{$fieldCode})) {
                try {
                    $file = \Yii::$app->storage->upload($this->owner->{$fieldCode});
                    if ($file) {
                        $this->owner->{$fieldCode} = $file->id;
                    } else {
                        $this->owner->{$fieldCode} = null;
                    }

                } catch (\Exception $e) {
                    $this->owner->{$fieldCode} = null;
                }
            }

            /**
             * В случае когда необходимо сохранить путь то мы обновляем файл
             */
            if ($this->isUpdatedFile &&
                $this->owner->{$fieldCode} &&
                $this->owner->getOldAttribute($fieldCode) &&
                ($this->owner->getOldAttribute($fieldCode) != $this->owner->{$fieldCode})
            ) {
                try {
                    $oldFile = CmsStorageFile::findOne($this->owner->getOldAttribute($fieldCode));
                    $newFile = CmsStorageFile::findOne($this->owner->{$fieldCode});

                    if (!$oldFile || !$newFile) throw new \Exception('File not found');

                    $file = new File($newFile->cluster->getRootSrc($newFile->cluster_file));
                    //todo: почему-то не работает через апдейт
                    //$oldFile->cluster->update($oldFile->cluster_file, $file);
                    $oldFile->deleteTmpDir();
                    // перемещаем новый файл на место старого
                    $file->move($oldFile->cluster->getRootSrc($oldFile->cluster_file));

                    // копируем новые атрибуты
                    $oldFile->size = $newFile->size;
                    $oldFile->mime_type = $newFile->mime_type;
                    $oldFile->extension = $newFile->extension;
                    $oldFile->original_name = $newFile->original_name;
                    $oldFile->name_to_save = $newFile->name_to_save;
                    $oldFile->name = $newFile->name;
                    $oldFile->image_height = $newFile->image_height;
                    $oldFile->image_width = $newFile->image_width;
                    $oldFile->save();

                    // новый удаляем
                    $newFile->delete();
                    $this->owner->{$fieldCode} = $oldFile->id;
                } catch (\Exception $e) {
                    $this->owner->{$fieldCode} = null;
                }

            }
        }
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function afterSaveStorgaFile()
    {
        if ($this->_removeFiles) {
            if ($files = StorageFile::find()->where(['id' => $this->_removeFiles])->all()) {
                foreach ($files as $file) {
                    $file->delete();
                }
            }
        }
    }

}