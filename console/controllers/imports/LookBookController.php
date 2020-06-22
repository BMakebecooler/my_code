<?php

/**
 * php ./yii imports/look-book/import
 */

namespace console\controllers\imports;

use common\lists\Contents;
use common\models\cmsContent\CmsContentElement;
use skeeks\cms\models\CmsContentElementProperty;
use yii\db\Connection;
use yii\helpers\Console;

/**
 * Class LookBookController
 * @package console\controllers
 */
class LookBookController extends \yii\console\Controller
{

    /**
     * Ид раздела с лукбуком в битриксе
     */
    const IBLOCK_BITRIX_LOOKBOOK = 75;

    /**
     * Ид раздела с лукбуком на новом сайте
     */
    const IBLOCK_NEW_SITE_SECTION_LOOKBOOK = 165;

    /**
     * Ид с лукбуком на новом сайте
     */
    const IBLOCK_NEW_SITE_LOOKBOOK = 167;

    /** @var Connection */
    protected $frontDb;

    /** @var  Connection */
    protected $db;

    protected $promoSection = null;

    public function init()
    {
        parent::init();

        $this->frontDb = \Yii::$app->get('front_db');
        $this->db = \Yii::$app->get('db');
    }

    /**
     * @return mixed
     */
    protected function getFilePath()
    {
        return \Yii::$app->params['storage']['vendorImagesPath'] . '/';
    }

    public function actionImport()
    {
        if ($this->import()) {
            $this->stdout("Луки импортированы!\n", Console::FG_GREEN);
        } else {
            $this->stdout("Луки не импортированы!\n", Console::FG_RED);
        }

        $this->stdout("Импорт лукбука закончен!\n", Console::FG_YELLOW);
    }

    /**
     * @return bool
     */
    protected function import()
    {
        $sql = <<<SQL
        SELECT 
            s.id AS section_id,
            CONCAT(section_file.subdir,'/', section_file.file_name) AS section_image,
            s.SORT AS section_sort,
            s.NAME AS section_name, 
            s.CODE AS section_code, 
            s.DESCRIPTION AS section_description, 
            s.ACTIVE AS section_active, 
            e.ID AS bitrix_id, 
            e.NAME AS look_name,
            e.DETAIL_TEXT AS look_description,
            e.SORT AS look_sort,
            e.ACTIVE AS look_active,
            discount.VALUE AS discount_val,
            GROUP_CONCAT(DISTINCT products.VALUE ORDER BY products.id ASC SEPARATOR ', ') AS products_id,
            GROUP_CONCAT(DISTINCT (SELECT CONCAT(file.subdir,'/', file.file_name)) SEPARATOR ', ') AS images,
            (
              SELECT CONCAT(f.subdir,'/', f.file_name) FROM front2.b_file f WHERE f.id = (SELECT fep.value FROM front2.b_iblock_element_property fep WHERE fep.iblock_element_id=e.id AND fep.iblock_property_id = 575)
            ) AS image
        FROM front2.b_iblock_section AS s
        INNER JOIN b_iblock_element AS e ON e.IBLOCK_SECTION_ID = s.id
        LEFT JOIN b_iblock_element_property AS products ON products.IBLOCK_ELEMENT_ID = e.ID AND products.IBLOCK_PROPERTY_ID = 578
        LEFT JOIN b_iblock_element_property AS image_file_id ON image_file_id.IBLOCK_ELEMENT_ID = e.ID AND image_file_id.IBLOCK_PROPERTY_ID = 576
        LEFT JOIN b_iblock_element_property AS discount ON discount.IBLOCK_ELEMENT_ID = e.ID AND discount.IBLOCK_PROPERTY_ID = 591
        LEFT JOIN b_file AS file ON file.id = image_file_id.value
        LEFT JOIN b_file AS section_file ON section_file.id = s.PICTURE
        WHERE s.iblock_id = :block_id
        GROUP BY e.id;
SQL;

        $blockElements = $this->frontDb->createCommand($sql, [
            ':block_id' => self::IBLOCK_BITRIX_LOOKBOOK,
        ])->queryAll();

        $lookBooks = [];

        foreach ($blockElements as $element) {
            $lookBooks[$element['section_id']][] = $element;
        }

        if (!$lookBooks) {
            return false;
        }

        foreach ($lookBooks as $sectionId => $looks) {

            $section = CmsContentElement::findOne([
                'bitrix_id' => $sectionId,
                'content_id' => LOOKBOOK_SECTION_CONTENT_ID
            ]);

            if (!$section) {
                $section = new CmsContentElement();
            }

            $section->name = $looks[0]['section_name'];
            $section->code = $looks[0]['section_code'];
            $section->description_full = $looks[0]['section_description'];
            $section->bitrix_id = $sectionId;
            $section->active = $looks[0]['section_active'];
            $section->content_id = LOOKBOOK_SECTION_CONTENT_ID;
            $section->priority = $looks[0]['section_sort'];

            $lookSectionImage = $looks[0]['section_image'];

            if ($lookSectionImage) {
                $realUrl = $this->getFilePath() . $lookSectionImage;

                $file = \Yii::$app->storage->upload($realUrl, [
                    'name' => $section->name
                ]);

                $section->link('image', $file);
            }

            if (!$section->save()) {
                throw new \Exception('Не удалось записать элемент');
            }

            if ($permission = \Yii::$app->authManager->getPermission($section->permissionName)) {
                if ($guest = \Yii::$app->authManager->getRole('guest')) {
                    if (!\Yii::$app->authManager->hasChild($guest, $permission)) {
                        \Yii::$app->authManager->addChild($guest, $permission);
                    }
                }
            }

            foreach ($looks as $look) {

                $productsIds = ($look['products_id']) ? explode(', ', $look['products_id']) : [];
                $images = ($look['images']) ? explode(', ', $look['images']) : [];

                if (!$newLook = Contents::getContentElementByBitrixId($look['bitrix_id'], LOOKBOOK_CONTENT_ID)) {
                    $newLook = new CmsContentElement();
                }

                $newLook->name = $look['look_name'];
                $newLook->description_full= $look['look_description'];
                $newLook->bitrix_id = $look['bitrix_id'];
                $newLook->content_id = LOOKBOOK_CONTENT_ID;
                $newLook->parent_content_element_id = $section->id;
                $newLook->priority = $look['look_sort'];
                $newLook->active = $look['look_active'];

                if ($newLook->save()) {
                    $newLook->relatedPropertiesModel->setAttribute('discount', $look['discount_val']);
                    $newLook->relatedPropertiesModel->save();
                } else {
                    throw new \Exception('Не удалось записать элемент лука');
                }

                /**
                 * @var $property
                 */
                $property = $newLook->relatedPropertiesModel->getRelatedProperty('products');

                CmsContentElementProperty::deleteAll('property_id = :property_id AND element_id = :element_id', [
                    ':property_id' => $property->id,
                    ':element_id' => $newLook->id
                ]);

                foreach ($productsIds as $bitrixId) {
                    if ($product = Contents::getContentElementByBitrixId($bitrixId, [PRODUCT_CONTENT_ID, OFFERS_CONTENT_ID])) {

                        $value = (string)$product->id;
                        $cmsContentElementProperty = new CmsContentElementProperty();
                        $cmsContentElementProperty->property_id = $property->id;
                        $cmsContentElementProperty->element_id = $newLook->id;
                        $cmsContentElementProperty->value = $value;
                        $cmsContentElementProperty->value_enum = $value;
                        $cmsContentElementProperty->value_num = $value;
                        $cmsContentElementProperty->description = $bitrixId;

                        if (!$cmsContentElementProperty->save()) {
                            var_dump($cmsContentElementProperty->getErrors());
                        }


                    }
                }

                $lookImage = $look['image'];

                if ($lookImage) {
                    $realUrl = $this->getFilePath() . $lookImage;

                    $file = \Yii::$app->storage->upload($realUrl, [
                        'name' => $newLook->name
                    ]);

                    $newLook->link('image', $file);
                }

                try {

                    if ($newLook->images) {
                        foreach ($newLook->images as $image) {
                            $image->delete();
                        }
                    }

                } catch (\Exception $e) {
                    $this->stdout("Не удалось удалить фотографии\n", Console::FG_RED);
                }

                foreach ($images as $image) {

                    $realUrl = $this->getFilePath() . $image;

                    try {
                        $file = \Yii::$app->storage->upload($realUrl, [
                            'name' => $newLook->name
                        ]);

                        $newLook->link('images', $file);

                        $this->stdout("Добавлено дополнительное изображение\n", Console::FG_GREEN);

                    } catch (\Exception $e) {
                        $message = 'Не добавлено дополнительное изображение: ' . $newLook->id . " ({$realUrl})";
                        $this->stdout("\t{$message}\n", Console::FG_RED);
                    }
                }

                $newLook->save();

                $this->stdout("Добавлен лук\n", Console::FG_GREEN);
            }
        }

        return true;
    }
}