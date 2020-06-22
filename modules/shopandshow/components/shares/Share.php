<?php
/**
 * Компонент для управления акциями
 * User: ubuntu5
 * Date: 24.04.17
 * Time: 13:56
 */

namespace modules\shopandshow\components\shares;

use common\helpers\Dates;
use common\lists\Contents;
use modules\shopandshow\models\shares\SsShare;
use yii\bASe\Component;
use yii\db\Connection;
use skeeks\sx\File;

class Share extends Component
{

    const PROMO_SECTION_CODE = 'promo';

    /** @var Cluster Хранилище картинок */
    protected $clusterId = 'element_images';

    /** @var Connection */
    protected $frontDb;


    /** @var  Connection */
    protected $db;

    protected $promoSection = null;

    public function init()
    {
        parent::init();

        $this->frontDb = null;// \Yii::$app->get('front_db');
        $this->db = \Yii::$app->get('db');
    }

    protected function getPromoSection()
    {
        return $this->promoSection ?: $this->promoSection = Contents::getContentByCode(self::PROMO_SECTION_CODE);
    }

    /**
     * @return int
     */
    protected function getBeginDate()
    {
        $beginOfDate = Dates::beginEfirPeriod();

//        return strtotime('- 5 days', $beginOfDate);

        return $beginOfDate;
    }

    /**
     * @return array
     */
    public function getDataAdvBannersInfoBlock()
    {
        $sql = <<<SQL
        select
        b.id,
        b.active_from,
        b.active_to,
        b.active,
        (
         SELECT CONCAT(f.subdir,'/', f.file_name) FROM front2.b_file f WHERE f.id=( SELECT fep.value FROM front2.b_iblock_element_property fep WHERE fep.iblock_element_id=b.id AND fep.iblock_property_id=560)
        ) AS image,
        (
         SELECT c.xml_id FROM front2.b_iblock_property_enum c WHERE c.id=(SELECT fep.value FROM front2.b_iblock_element_property fep WHERE fep.iblock_element_id=b.id AND fep.iblock_property_id=556)
        ) AS banner_type,
        (
         SELECT fep.value FROM front2.b_iblock_element_property fep WHERE fep.iblock_element_id=b.id AND fep.iblock_property_id=561
        ) AS action_code,
        (
         SELECT fep.value FROM front2.b_iblock_element_property fep WHERE fep.iblock_element_id=b.id AND fep.iblock_property_id=559
        ) AS link,
        (
         SELECT ss.id FROM front2.sands_schedule ss WHERE ss.CODE = action_code
        ) AS schedule_id,
        /*(
         SELECT ss.name FROM front2.sands_schedule ss WHERE ss.CODE = action_code
        ) AS name,*/
        b.PREVIEW_TEXT as name,
        b.DETAIL_TEXT as description,
        (
         SELECT fep.value FROM front2.b_iblock_element_property fep WHERE fep.iblock_element_id=b.id AND fep.iblock_property_id=563
        ) AS bitrix_product_id,
        (
         SELECT fep.value FROM front2.b_iblock_element_property fep WHERE fep.iblock_element_id=b.id AND fep.iblock_property_id=571
        ) AS gif_path
        
        from front2.b_iblock_element b
        where b.iblock_id = 72 AND active_to >= :show_from -- AND b.active = 'Y'
        order by b.active_from ASC, b.id ASC
SQL;


        return $this->frontDb->createCommand($sql, [
            ':show_from' => date('Y-m-d H:i:s', $this->getBeginDate()),
        ])->queryAll();
    }

    /**
     * Загрузка баннеров через инфоблок
     * @return array
     */
    public function getAdvBannersInfoBlock()
    {

        $banners = [];
        $bitrixInfoBlockIds = [];

        $actualBanners = $this->getDataAdvBannersInfoBlock();

        foreach ($actualBanners as $banner) {

            $beginDateTime = strtotime($banner['active_from']);
            $endDateTime = strtotime($banner['active_to']);

            /** Баннеры созданы, а картинки не загружены */
            /*if (!$this->hasImage($banner)) {
                var_dump('image < 3');
                continue;
            }*/

            $bitrixInfoBlockIds[] = $banner['id'];

            $newBanner = SsShare::find()->where([
                'bitrix_info_block_id' => $banner['id'],
            ])->one();

            /**
             * @var SsShare $newBanner
             */
            if (!$newBanner) {
                continue;
                $newBanner = new SsShare();
            }

            try {
                $newBanner->begin_datetime = $beginDateTime;
                $newBanner->end_datetime = $endDateTime;
                $newBanner->bitrix_info_block_id = $banner['id'];
                $newBanner->bitrix_sands_schedule_id = $banner['schedule_id'];
                $newBanner->bitrix_product_id = $banner['bitrix_product_id'];

                $newBanner->name = $banner['name'];
                $newBanner->description = $banner['description'];
                $newBanner->active = $banner['active'];
//                $newBanner->code = $banner['action_code'] ?: $newBanner->code;
                $newBanner->code = $banner['action_code'];
                $newBanner->url = $banner['link'];
                $newBanner->banner_type = $banner['banner_type'];


            if (!empty($newBanner->bitrix_product_id)) {
                $newBanner->image->cluster->deleteTmpDir($newBanner->image->cluster_file);
            }

            continue;

//            $newBanner->promo_type = $banner['PROMO_TYPE'];
//            $newBanner->promo_type_code = $banner['promo_type_code'];

                if ($this->hasImage($banner)) {

                    if ($banner['gif_path']) {
                        $vendorFilePath = \Yii::$app->params['storage']['vendorImagesGifPath'] . '/' . $banner['gif_path'];
                    } else {
                        $vendorFilePath = \Yii::$app->params['storage']['vendorImagesPath'] . '/' . $banner['image'];
                    }

                    $vendorFile = new File($vendorFilePath);

                    if ($vendorFile->isExist() === false) {
                        var_dump($vendorFilePath);
                        var_dump($banner['id']);
                        var_dump('vendor file FALSE');
                    } else {

                        $newFilePath = '/tmp/' . md5(time() . $vendorFilePath) . "." . $vendorFile->getExtension();

                        $tmpFile = new File($newFilePath);

                        $vendorFile->copy($tmpFile);

                        if (($savedImage = $newBanner->image)) {
                            /**
                             * Если новая фотка отличается от ранее загруженной то загружаем ее
                             */

                            if ($savedImage->original_name != $banner['image']
                                || $savedImage->size != $tmpFile->size()->getBytes()
                            ) {

                                $newBanner->image->cluster->update($newBanner->image->cluster_file, $tmpFile);
                                $savedImage->size = $tmpFile->size()->getBytes();
                                $savedImage->original_name = $banner['image'];
//                                $savedImage->save(false);
                            } // удаляем thumbnails для баннеров с продуктом всегда, даже если сама картинка не менялась
                            elseif (!empty($newBanner->bitrix_product_id)) {
                                $newBanner->image->cluster->deleteTmpDir($newBanner->image->cluster_file);
                            }

                        } else {
//                            $file = \Yii::$app->storage->upload($tmpFile, [
//                                'original_name' => $banner['image'],
//                            ], \Yii::$app->params['storage']['clusters'][$this->clusterId]
//                            );

//                            $newBanner->link('image', $file);
                        }
                    }

                } else {
                    var_dump(' no image ');
                    var_dump($banner['image']);
                    var_dump($banner['banner_type']);
                }
//
//                if (!$newBanner->save()) {
//                    var_dump($banner);
//                    var_dump($newBanner->getErrors());
//                }

            } catch (\yii\base\ErrorException $errorException) {
                var_dump($errorException->getMessage());
                var_dump($banner);
                continue;
            }

            $banners[$newBanner->id] = $newBanner;
        }

        /**
         * Удаляем акции которые были удалены в битриксе, но ранее были загружены
         */
//        SsShare::deleteAll([
//            'AND',
//            'begin_datetime >= :begin_datetime',
//            ['NOT IN', 'bitrix_info_block_id', $bitrixInfoBlockIds],
//        ], [
//            ':begin_datetime' => $this->getBeginDate()
//        ]);

        return $banners;
    }


    /**
     * Удаление превьюшек баннеров
     */
    public function removeThumbs()
    {

        $banners = SsShare::find()->andWhere('id >= 2744')->all();

        /**
         * @var $banner SsShare
         */
        foreach ($banners as $banner) {

            if (!empty($banner->bitrix_product_id) && $banner->image) {
                $banner->image->cluster->deleteTmpDir($banner->image->cluster_file);
            }
        }
    }

    /**
     * Получить типы баннеров
     * @return array
     */
    public function getTypesBanners()
    {
        $sql = <<<SQL
SELECT * FROM front2.b_iblock_property_enum enum WHERE enum.PROPERTY_ID = :property_id
SQL;

        return $this->frontDb->createCommand($sql, [
            ':property_id' => 556,
        ])->queryAll();
    }

    /**
     * Создать элементы контента (страницы с акциями)
     */
    public function createCmsContentElement()
    {
        $insertSql = <<<SQL
        
SET sql_mode = '';

SET @promo_content_id = (SELECT id FROM cms_content WHERE code = 'promo' AND active = 'Y'), 
  @tree_promo_id = (SELECT default_tree_id FROM cms_content WHERE code = 'promo' AND active = 'Y'); -- (SELECT id FROM cms_tree WHERE code = 'promo' AND active = 'Y');

INSERT IGNORE INTO cms_content_element (name, code, content_id, tree_id, image_id)

    SELECT share.name, share.code, @promo_content_id, @tree_promo_id, image_id
    
    FROM ss_shares AS share
    
    WHERE begin_datetime >= :show_from AND banner_type != 'MAIN_SMALL_EFIR'
    
    GROUP BY code

    LIMIT 50
SQL;

        $this->db->createCommand($insertSql, [
//            ':show_from' => date('Y-m-d H:i:s', $this->getBeginDate()),
            ':show_from' => $this->getBeginDate(),
        ])->execute();
    }

    /**
     * Получить товары из акций
     */
    public function bannersProducts()
    {
        $sql = <<<SQL
    SELECT shares.id as BANNER_ID, p.PROPERTY_434 as LOTS
    FROM ss_shares AS shares, front2.b_iblock_element e,front2.b_iblock_element_prop_s65 as p
    WHERE shares.end_datetime >= :show_from -- AND shares.bitrix_sands_schedule_id IS NOT NULL
      AND e.code = shares.code COLLATE utf8_unicode_ci AND e.iblock_id = 65 AND e.id = p.iblock_element_id
    ORDER BY shares.begin_datetime ASC
SQL;

        $shares = $this->db->createCommand($sql, [
            ':show_from' => $this->getBeginDate(),
        ])->queryAll();

        foreach ($shares as $share) {
            $lots = @unserialize($share['LOTS']);
            if (empty($lots)) continue;
            $lotsIds = array_filter(array_unique($lots['VALUE']));

            // массово достаем id для всех lot_id из запроса
            $bitrixMap = $this->getIdsByBitrixIds($lotsIds);

            $rows = [];
            foreach ($lotsIds as $lotId) {
                $rows[] = [$share['BANNER_ID'], $lotId, @$bitrixMap[$lotId] ? @$bitrixMap[$lotId] : null];
            }

            // фигачим
            $deleteSql = 'DELETE FROM ss_shares_products where banner_id = :banner_id';
            $this->db->createCommand($deleteSql, [':banner_id' => $share['BANNER_ID']])->execute();

            $insertSql = $this->db->createCommand()->batchInsert('ss_shares_products', ['banner_id', 'bitrix_id', 'product_id'], $rows)->rawSql;
            $insertSql = str_replace('INSERT', 'INSERT IGNORE', $insertSql);
            $this->db->createCommand($insertSql)->execute();
        }
    }

    /**
     * Получить товары из акций
     */
    public function bannersProductsOriginal()
    {

        $sql = <<<SQL
    SELECT shares.bitrix_sands_schedule_id, shares.id AS share_id
    FROM ss_shares AS shares
    WHERE shares.end_datetime >= :show_from AND bitrix_sands_schedule_id IS NOT NULL
    GROUP BY shares.bitrix_sands_schedule_id
    ORDER BY shares.begin_datetime ASC
SQL;

        $shares = $this->db->createCommand($sql, [
            ':show_from' => $this->getBeginDate(),
        ])->queryAll();


        foreach ($shares as $share) {

            $sql = <<<SQL
    SELECT links.SCHEDULE_ID AS banner_id, links.LOT_ID AS lot_id
    FROM sands_schedule_links AS links
    WHERE links.SCHEDULE_ID = :banner_id
SQL;

            $lots = $this->frontDb->createCommand($sql, [
                ':banner_id' => $share['bitrix_sands_schedule_id'],
            ])->queryAll();

            $lotsIds = array_unique(\common\helpers\ArrayHelper::getColumn($lots, 'lot_id'));
            // массово достаем id для всех lot_id из запроса
            $bitrixMap = $this->getIdsByBitrixIds($lotsIds);

            // дополняем массив лотов найденными id из cms_content_element
            array_walk($lots, function (&$row) use ($bitrixMap, $share) {
                $row['product_id'] = ($row['lot_id'] && @$bitrixMap[$row['lot_id']]) ? @$bitrixMap[$row['lot_id']] : null;

                $row['banner_id'] = $share['share_id'];
            });

            // фигачим
            $deleteSql = 'DELETE FROM ss_shares_products where banner_id = :banner_id';
            $this->db->createCommand($deleteSql, [':banner_id' => $share['share_id']])->execute();

            $insertSql = $this->db->createCommand()->batchInsert('ss_shares_products', ['banner_id', 'bitrix_id', 'product_id'], $lots)->rawSql;
//            $insertSql = str_replace('INSERT', 'INSERT IGNORE', $insertSql);
            $this->db->createCommand($insertSql)->execute();
        }
    }

    /**
     * вспомогательная функция, достает id элементов по их bitrix_id
     * @param array $lotsIds
     *
     * @return array
     */
    protected function getIdsByBitrixIds(array $lotsIds)
    {
        $params = [];
        $condition = $this->db->getQueryBuilder()->buildCondition(['IN', 'bitrix_id', $lotsIds], $params);

        $lotsSql = "SELECT id, bitrix_id from cms_content_element WHERE {$condition}";

        $lots = $this->db->createCommand($lotsSql, $params)->queryAll();

        $bitrixMap = \common\helpers\ArrayHelper::map($lots, 'bitrix_id', 'id');

        return $bitrixMap;
    }

    protected function hasImage($banner)
    {
        return (strlen($banner['image']) >= 3) || strlen($banner['gif_path']) >= 3;
    }
}