<?php

/**
 * php ./yii sync/core/data
 * php ./yii sync/core/data/sync-brands
 */

namespace console\controllers\sync\core;

use common\helpers\Msg;
use common\models\cmsContent\CmsContentElement;
use skeeks\cms\models\CmsContent;
use yii\db\Exception;
use yii\helpers\Console;
use console\controllers\sync\SyncController;
use skeeks\sx\File;

/**
 * Class DataController
 *
 * @package console\controllers
 */
class DataController extends SyncController
{

    /** @var string Хранилище картинок */
    protected $clusterId = 'property_images';

    public function actionIndex()
    {

        /** Синхронизировать Цвета и фотки к ним */
        $this->updateColorReference();

        /** Синхронизировать остальные справочники */
        $this->syncRefElements();

        //$this->actionSyncBrands();
    }

    /** Синхронизация справочника Бренды */
    public function actionSyncBrands()
    {

        $this->stdout("Prepare to sync Brands elements\n", Console::FG_CYAN);

        $treeId = (int)\common\lists\TreeList::getIdTreeByCode('brands');
        if (!$treeId) {
            $this->stdout("Cant find brand tree by code\n", Console::FG_RED);
            return 0;
        }

        $sql = "
        select
            cce.id,
            case when cce.id is null then unix_timestamp() else cce.created_at end as created_at,
            case when cce.id is null then unix_timestamp() else cce.updated_at end as updated_at,
            p.name as name,
            cc.id as content_id,
            p.id as bitrix_id,
            {$treeId} as tree_id
        from front2.b_iblock_element p
        inner join front2.b_iblock ib ON ib.id = p.iblock_id AND ib.code = 'brands'
        left join cms_content cc on cc.code = 'BRAND'
        left join cms_content_element cce ON cce.bitrix_id=p.id AND cce.content_id=cc.id
        where p.WF_PARENT_ELEMENT_ID is null
        ";

        $brandList = \Yii::$app->db->createCommand($sql)->queryAll();

        foreach ($brandList as $brandRow) {
            $brand = CmsContentElement::findOne($brandRow['id']);
            if (!$brand) {
                $brand = new CmsContentElement();
            }
            unset($brandRow['id']);

            $brand->setAttributes($brandRow);
            if (!$brand->save()) {
                \Yii::error('Failed to save brand '. print_r($brand->getErrors(), true));
            }
        }

        $this->stdout(" done. Affected ".count($brandList)." rows\n", Console::FG_GREEN);

        return 0;

    }

    protected function updateColorReference()
    {

        $this->colorRef('COLOR_REF', 'front2.highload_color_reference');
        $this->colorRefImages('COLOR_REF', 'front2.highload_color_reference');

        if(YII_ENV == 'production') {
            $this->colorRef('PARAMETER1', 'front2.highload_parameters_ref');
            $this->colorRefImages('PARAMETER1', 'front2.highload_parameters_ref');

            $this->colorRef('PARAMETER2', 'front2.highload_parameters_ref');
            $this->colorRefImages('PARAMETER2', 'front2.highload_parameters_ref');
        }

    }

    /**
     * @param string $propertyCode - код справочника
     * @param string $frontHighloadTable - highload таблица
     * Синхронизация справочника COLOR_REF (Цвет)
     * @return bool
     */
    protected function colorRef($propertyCode = 'COLOR_REF', $frontHighloadTable = 'front2.highload_color_reference')
    {

        $this->stdout("Sync {$propertyCode} values\n", Console::FG_CYAN);

        $query = "
            INSERT INTO cms_content_element (id, created_by, created_at, updated_by, updated_at, name, code, content_id, meta_title, bitrix_id)
            SELECT 
                IFNULL(exist_value.id, 'default') as id,
                1,
                UNIX_TIMESTAMP(),
                1,
                UNIX_TIMESTAMP(),
                CASE WHEN fp.UF_NAME IS NOT NULL AND fp.UF_NAME != '' THEN fp.UF_NAME END AS name,
                fp.UF_XML_ID AS code,
                cc.id AS content_id,
                '' AS meta_title,
                fp.id AS bitrix_id
            FROM
                {$frontHighloadTable} fp
                    LEFT JOIN
                cms_content cc ON cc.code = '{$propertyCode}'
                    AND cc.content_type = 'info'
                    LEFT JOIN
                cms_content_element cce ON cce.bitrix_id = fp.id
                    AND cce.content_id = cc.id
                    LEFT JOIN
                cms_content_element exist_value ON exist_value.bitrix_id = fp.id
                    AND exist_value.content_id = cc.id
            GROUP BY fp.id
            ON DUPLICATE KEY UPDATE updated_at=VALUES(updated_at), updated_by=VALUES(updated_by), name=VALUES(name), code=VALUES(code)
        ";

        $this->logQuery($query);

        $this->stdout("Begin sync - ", Console::FG_YELLOW);

        try {

            $_transactionTime = time();

            $transaction = \Yii::$app->db->beginTransaction();

            $affected = \Yii::$app->db->createCommand($query)->execute();
            $this->stdout("done.\nAffected " . $affected . " rows in ".(time()-$_transactionTime)."sec\n", Console::FG_GREEN);

            $transaction->commit();

        } catch (\yii\db\Exception $e) {

            $this->logError($e);

            $transaction->rollBack();
            return false;
        }

        return true;

    }

    /**
     * @param string $propertyCode - код справочника
     * @param string $frontHighloadTable - highload таблица
     *
     * @return bool
     * @throws \yii\base\Exception
     */
    protected function colorRefImages($propertyCode = 'COLOR_REF', $frontHighloadTable = 'front2.highload_color_reference')
    {

        $this->stdout("Sync {$propertyCode} images\n", Console::FG_CYAN);

        if ( \Yii::$app->params['storage']['vendorImagesPath'] === null ) {
            throw new \yii\base\Exception("Bitrix upload folder path not set");
        }

        if ( $this->clusterId === null ) {
            throw new \yii\base\Exception("ClusterIds not set");
        }

        $query = "
            SELECT 
                f.ID,
                csf.id,
                ce.id as element_id,
                ce.content_id as content_id,
                ce.code as code,
                ce.name as name,
                f.SUBDIR AS subdir,
                f.FILE_NAME AS file_name
            FROM
                cms_content cc
                    LEFT JOIN
                cms_content_element ce ON ce.content_id = cc.id
                    LEFT JOIN
                cms_storage_file csf ON csf.id = ce.image_id
                    LEFT JOIN
                {$frontHighloadTable} pe ON pe.id = ce.bitrix_id
                    LEFT JOIN
                front2.b_file f ON f.ID = pe.UF_FILE
            WHERE
                cc.code = '{$propertyCode}' AND cc.content_type = 'info'
                AND (ce.image_id IS NULL OR csf.updated_at < unix_timestamp(f.TIMESTAMP_X))
			ORDER BY ce.id ASC
        ";

        $images = \Yii::$app->db->createCommand($query)->queryAll();
        $total = count($images);

        $this->stdout("Got {$total} images to upload\n", Console::FG_YELLOW);

        $affected = $processed = 0;

        foreach ($images as $image) {

            ++$affected;

            $this->stdout("[{$affected} of {$total}] {$image['code']} -> ", Console::FG_YELLOW);

            if ( $image['file_name'] == '' ) {
                $this->stdout("skip due empty filename\n", Console::FG_GREY);
                continue;
            }

            $filePath = \Yii::$app->params['storage']['vendorImagesPath'] . '/'.$image['subdir'].'/'.$image['file_name'];

            $localFile = new File($filePath);

            if ( $localFile->isExist() === false ) {
                $this->stdout("skip due file not exist {$filePath}\n", Console::FG_RED);
                continue;
            }

            try {

                $tmpFilePath = '/tmp/' . md5(time() . '/upload/' . $image['subdir'] . '/' . $image['file_name']) . "." . $localFile->getExtension();
                $tmpFile = new File($tmpFilePath);
                $localFile->copy($tmpFile);

            } catch (Exception $e) {
                $this->stdout("skip because temp file create error {$e->getMessage()}\n", Console::FG_RED);
                continue;
            }

            try {

                /** @var CmsContentElement $element */
                $element = CmsContentElement::find()
                    ->andWhere('content_id=:CONTENT_ID',[':CONTENT_ID' => $image['content_id']])
                    ->andWhere('id=:ID', [ ':ID' => $image['element_id'] ])
                    ->one();

                if ( $element == null ) {
                    $this->stdout("skip due content element {$image['element_id']} not found\n", Console::FG_RED);
                    continue;
                }

                if ( $element->image != null )
                    $element->unlink('image', $element->image, true);


                $file = \Yii::$app->storage->upload($tmpFile, [
                    'name' => $image['name'],
                ], \Yii::$app->params['storage']['clusters'][$this->clusterId]);

                $element->link('image', $file);

                $this->stdout("linked!\n", Console::FG_GREEN);

            } catch (\Exception $e) {
                $this->stdout("skip due file upload error: {$e->getMessage()}\n", Console::FG_RED);
                continue;
            }

        }

         return true;

    }

    /** Синхронизация справочников */
    protected function syncRefElements()
    {

        $this->stdout("Prepare to sync Reference elements\n", Console::FG_CYAN);

        $_refs = $this->getReferences();

        $this->stdout("References to sync count: ".count($_refs)."\n", Console::FG_YELLOW);

        if ( count($_refs) < 1 ) {
            $this->stdout("No refs found!\n", Console::FG_RED);
            return 0;
        }

        foreach ( $_refs as $ref ) {

            $this->stdout("Sync {$ref->code}", Console::FG_YELLOW);

            $sql = "
            INSERT IGNORE into cms_content_element (id, created_at, updated_at, name, code, content_id, meta_title, bitrix_id)
            select
                cce.id,
                case when cce.id is null then unix_timestamp() else cce.created_at end as created_at,
                case when cce.id is null then unix_timestamp() else cce.updated_at end as updated_at,
                p.value as name,
                TRIM(LEADING 'PRICE_' FROM p.xml_id) as code,
                cc.id as content_id,
                p.value as meta_title,
                p.id as bitrix_id
            from front2.b_iblock_property_enum p
            left join front2.b_iblock_property ip ON ip.id=p.property_id AND ip.iblock_id IN (10,11)
            left join cms_content cc ON cc.code=ip.code and cc.content_type='info'
            left join cms_content_element cce ON cce.bitrix_id=p.id AND cce.content_id=cc.id
            left join cms_content_property ccp ON ccp.vendor_id=p.property_id
            where cc.id={$ref->id}
            ON DUPLICATE KEY UPDATE updated_at=VALUES(updated_at),
                code=VALUES(code),
                bitrix_id=VALUES(bitrix_id)
            ";

            $affected = \Yii::$app->db->createCommand($sql)->execute();
            $this->stdout(" done. Affected {$affected} rows\n", Console::FG_GREEN);

        }

        return 0;

    }

    /**
     * Получить все справочники
     */
    protected function getReferences()
    {

        $_references = CmsContent::find()
            ->andWhere('content_type="info"')
            ->andWhere('code!="experts"')
            ->andWhere('code!="COLOR_REF"')
            ->andWhere('code!="PARAMETER1"')
            ->andWhere('code!="PARAMETER2"')
            ->andWhere('code!="BRAND"')
            ->all();

        return $_references;

    }

}