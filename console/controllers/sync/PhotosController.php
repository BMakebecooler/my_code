<?php

/**
 * php ./yii sync/photos
 */

namespace console\controllers\sync;

use common\helpers\Msg;
use skeeks\cms\components\storage\Cluster;
use common\models\cmsContent\CmsContentElement;
use skeeks\cms\models\CmsStorageFile;
use skeeks\sx\File;
use yii\helpers\Console;


/**
 * Class PhotosController
 *
 * @package console\controllers
 */
class PhotosController extends SyncController
{

    /** @var Cluster Хранилище картинок */
    protected $clusterId = 'element_images';

    private $tempPhoto = [];

    /** сотрет все фотки товаров
     * нужна на время в мстере
     */
    public function cleanPhotos()
    {

        $queries = [
            "select distinct image_id from cms_content_element where content_id in (2,10)",
            "select distinct image_full_id from cms_content_element where content_id in (2,10)",
            "select distinct storage_file_id from cms_content_element_image where content_element_id IN (select id from cms_content_element where content_id in (2,10))"
        ];

        foreach ($queries as $sql) {
            $affected = 0;

            $this->stdout("Delete image id\n");
            $fileIds = \Yii::$app->db->createCommand($sql)->queryAll();

            $this->stdout("Elements count: " . count($fileIds) . "\n");
            foreach ($fileIds as $id) {

                ++$affected;

                $storageFile = CmsStorageFile::findOne($id);

                if ($storageFile != null)
                    $storageFile->delete();

                if ($affected % 500 == 0)
                    echo "500... ";

            }
        }

    }

    public function actionIndex()
    {
        $this->actionUpdate();
        $this->actionClean();
    }

    /**
     * Подгружает новые и обновленные фотки, которые еще не были загружены в хранилище
     * @return bool
     */
    public function actionUpdate()
    {
        $this->stdout("Sync Product/Offers photos\n", Console::FG_CYAN);

        $query = "
                select
                    f.ID,
                    ce.id as element_id,
                    ce.name,
                    ce.bitrix_id,
                    p.code as code,
                    f.SUBDIR,
                    f.FILE_NAME,
                    f.original_name,
                    UNIX_TIMESTAMP(f.TIMESTAMP_X) as updated_at
                from front2.b_iblock_element b
                left join front2.b_iblock_element_property ep ON ep.iblock_element_id=b.id  and ep.iblock_property_id in (86, 504, 500)
                left join front2.b_iblock_property p ON p.id=ep.iblock_property_id
                left join front2.b_file f ON f.id=ep.VALUE
                left join cms_content_element ce ON ce.bitrix_id=b.id and ce.content_id in (2,10)
                -- main photo
                left join cms_storage_file sf ON sf.id = ce.image_id and sf.bitrix_id = f.id
                where
                    b.iblock_id in (10,11) and b.active = 'Y'
                    and p.code = 'MAIN_PHOTO'
                    and f.FILE_NAME IS NOT NULL
                    and ce.id is not null
                    and sf.id is null
                UNION ALL 
                select
                    f.ID,
                    ce.id as element_id,
                    ce.name,
                    ce.bitrix_id,
                    p.code as code,
                    f.SUBDIR,
                    f.FILE_NAME,
                    f.original_name,
                    UNIX_TIMESTAMP(f.TIMESTAMP_X) as updated_at
                from front2.b_iblock_element b
                left join front2.b_iblock_element_property ep ON ep.iblock_element_id=b.id  and ep.iblock_property_id in (86, 504, 500)
                left join front2.b_iblock_property p ON p.id=ep.iblock_property_id
                left join front2.b_file f ON f.id=ep.VALUE
                left join cms_content_element ce ON ce.bitrix_id=b.id and ce.content_id in (2,10)
                -- secondary photos
                left join cms_content_element_image ccei ON ccei.content_element_id=ce.id and ccei.bitrix_id = f.id
                where
                    b.iblock_id in (10,11) and b.active = 'Y'
                    and p.code != 'MAIN_PHOTO'
                    and f.FILE_NAME IS NOT NULL
                    and ce.id is not null
                    and ccei.id is null
                GROUP BY f.id
        ";

        $bitrixProps = \Yii::$app->db->createCommand($query)->queryAll();

        if (!$bitrixProps || count($bitrixProps) < 1) {
            $this->stdout("No new photos\n", Console::FG_GREY);
            return false;
        }

        $this->stdout("Got " . count($bitrixProps) . " updated photos\n", Console::FG_GREEN);

        $affected = $processed = 0;
        $total = count($bitrixProps);

        foreach ($bitrixProps as $prop) {

            ++$affected;

            $this->stdout("[{$affected} of {$total}] >> ", Console::FG_GREY);

            $this->stdout("Photo id {$prop['ID']} Element ID {$prop['element_id']} photo {$prop['code']} {$prop['original_name']} ", Console::FG_YELLOW);

            if (\common\helpers\Promo::isProductStarieVsMolodie($prop['element_id'])) {
                $this->stdout(" skipped - starie vs molodie ", Console::FG_GREEN);
                continue;
            }

            try {

                $vendorFilePath = \Yii::$app->params['storage']['vendorImagesPath'] . '/' . $prop['SUBDIR'] . '/' . $prop['FILE_NAME'];

                $vendorFile = new File($vendorFilePath);

                if ($vendorFile->isExist() === false) {
                    $this->stdout("FILE NOT EXIST: {$vendorFilePath}\n", Console::FG_RED);
                    continue;
                }

                /** Копируем фаил чтобы не удалять у вендора (в нашем случае из папки оригиналов) */
                $tmpFile = new File('/tmp/' . md5(time() . $vendorFilePath) . "." . $vendorFile->getExtension());

                $vendorFile->copy($tmpFile);

                $cmsContentElement = CmsContentElement::findOne($prop['element_id']);

                $file = \Yii::$app->storage->upload($tmpFile, [
                    'name' => $cmsContentElement->name,
                    'updated_at' => $prop['updated_at'],
                    'original_name' => $prop['original_name'],
                    'bitrix_id' => $prop['ID']
                ],
                    \Yii::$app->params['storage']['clusters'][$this->clusterId] // TODO: подумать как такой херни не плодить.
                );

                ++$processed;

                if ($prop['code'] == 'MAIN_PHOTO') {
                    // удаляем старое фото, но только если мы сами его не установили
                    if($cmsContentElement->image && !$this->isTempMainPhoto($cmsContentElement)) $cmsContentElement->image->delete();
                    // link new
                    $cmsContentElement->link('image', $file);

                } elseif ($prop['code'] == 'PHOTOS') {

                    /** Если у товара еще нет основного фото - прикрепить первое из дополнительных фото на место главного */
                    if ($cmsContentElement->image === null) {
                        // помечаем, что тут мы сами установили main image
                        $this->setTempMainPhoto($cmsContentElement);
                        $cmsContentElement->link('image', $file);

                        $this->stdout("Установлена главное фото из дополнительных\n", Console::FG_YELLOW);

                        continue;
                    }

                    $priority = 100;
                    if (preg_match('/_(\d+)\.(jpg|jpeg|gif|png|bmp)/xi', $prop['original_name'], $matches) && !empty($matches)){
                        $priority *= ((int)$matches[1] + 1);
                    }

                    $cmsContentElement->link('images', $file, [
                        'priority' => $priority,
                        'bitrix_id' => $prop['ID']
                    ]);

                } elseif ($prop['code'] == 'SUBJECT_PHOTO') {
                    if($subjectPhoto = $cmsContentElement->relatedPropertiesModel->getAttribute('SUBJECT_PHOTO')) {
                        if(file_exists($subjectPhoto)) unlink($subjectPhoto);
                    }
                    $cmsContentElement->relatedPropertiesModel->setAttribute('SUBJECT_PHOTO', $file->getSrc());
                    $cmsContentElement->relatedPropertiesModel->save();
                }

            } catch (\Exception $e) {

                $this->logError($e);

                /** Если что - загрузится при следующем обмене */
                continue;
            }

            $this->stdout("saved\n", Console::FG_GREEN);
        }

        $this->stdout("Done. Affected {$affected}, processed {$processed}\n", Console::FG_GREEN);

        return true;
    }

    /**
     * Удаляет фотки, которые отсутствуют в битриксе
     */
    public function actionClean()
    {
        $this->stdout("Clean old Product/Offers photos\n", Console::FG_CYAN);

        $query = "                
            SELECT 
                ccei.id, ccei.content_element_id
            FROM cms_content_element ce
            LEFT JOIN cms_content_element_image ccei ON ccei.content_element_id = ce.id
            LEFT JOIN front2.b_iblock_element b ON ce.bitrix_id = b.id AND b.iblock_id IN (10 , 11)
            WHERE
                ce.content_id IN (2 , 10)
            AND ccei.id is not null
            AND NOT EXISTS ( 
                SELECT 1
                FROM front2.b_iblock_element_property ep
                LEFT JOIN front2.b_iblock_property p ON p.id = ep.iblock_property_id
                LEFT JOIN front2.b_file f ON f.id = ep.VALUE
                WHERE
                    p.code != 'MAIN_PHOTO'
                AND ep.iblock_element_id = b.id
                AND ep.iblock_property_id IN (86 , 504, 500)
                AND f.id = ccei.bitrix_id
            )";

        $cmsContentElementImages = \Yii::$app->db->createCommand($query)->queryAll();

        if (!$cmsContentElementImages || count($cmsContentElementImages) < 1) {
            $this->stdout("No photos to clean\n", Console::FG_GREY);
            return false;
        }

        $this->stdout("Got " . count($cmsContentElementImages) . " photos to delete\n", Console::FG_GREEN);

        $affected = $processed = 0;
        $total = count($cmsContentElementImages);

        foreach ($cmsContentElementImages as $cmsContentElementImage) {

            ++$affected;

            $this->stdout("[{$affected} of {$total}] >> ", Console::FG_GREY);

            $this->stdout("CmsContentElementImage id {$cmsContentElementImage['id']} Element ID {$cmsContentElementImage['content_element_id']}", Console::FG_YELLOW);

            if (\common\helpers\Promo::isProductStarieVsMolodie($cmsContentElementImage['content_element_id'])) {
                $this->stdout(" skipped - starie vs molodie ", Console::FG_GREEN);
                continue;
            }

            try {
                $cmsContentElementImage = \skeeks\cms\models\CmsContentElementImage::findOne($cmsContentElementImage['id']);

                $cmsContentElementImage->storageFile->delete();
                $cmsContentElementImage->delete();

                ++$processed;
            } catch (\Exception $e) {

                $this->logError($e);

                /** Если что - удалится при следующем обмене */
                continue;
            }

            $this->stdout(" deleted\n", Console::FG_GREEN);
        }

        $this->stdout("Done. Affected {$affected}, processed {$processed}\n", Console::FG_GREEN);

        return true;
    }

    private function setTempMainPhoto(CmsContentElement $cmsContentElement)
    {
        $this->tempPhoto[$cmsContentElement->id] = true;
    }

    private function isTempMainPhoto(CmsContentElement $cmsContentElement)
    {
        return array_key_exists($cmsContentElement->id, $this->tempPhoto);
    }
}