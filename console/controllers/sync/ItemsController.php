<?php

/**
 * php ./yii sync/items
 * php ./yii sync/items/products
 * php ./yii sync/items/offers
 * php ./yii sync/items/set-guids-cce
 * php ./yii sync/items/set-guids-tree
 *
 * php ./yii sync/items/update-guids-cce
 * php ./yii sync/items/set-base-modification-guids
 * php ./yii sync/items/update-count-images
 */

namespace console\controllers\sync;

use common\helpers\Msg;
use common\lists\Contents;
use common\lists\TreeList;
use console\controllers\sync\helpers\SyncHelper;
use modules\shopandshow\models\common\Guid;
use yii\base\Exception;
use yii\helpers\Console;
use yii\helpers\Html;


/**
 * Class ItemsController
 *
 * @package console\controllers
 */
class ItemsController extends SyncController
{

    public function actionIndex()
    {

        $this->actionCleanDeletedOffers();

        $this->actionProducts();
        $this->actionOffers();

        $this->actionSetGuidsCce();
        $this->actionSetBaseModificationGuids();

        $this->actionShopProducts();
        $this->actionCategories();

//        $this->actionRecountTreeContentElement();
        $this->updateCounterCCECountChildren();
    }

    public function actionSetGuidsCce()
    {

        $sql = <<<SQL
SELECT cce.id AS product_id, gs.guid AS guid
FROM front2 .`sands_guid_storage`AS gs
INNER JOIN ss_web.cms_content_element AS cce ON cce.bitrix_id = gs.local_id 
WHERE cce.guid_id IS NULL
 AND (gs.ENTITY = 'PRODUCT' AND cce.content_id = 2 OR gs.ENTITY = 'OFFER' AND cce.content_id = 10)
SQL;

        $guids = \Yii::$app->db->createCommand($sql)->queryAll();

        var_dump('count : ' . count($guids));

        sleep(3);

        foreach ($guids as $guid) {
            $contentElement = Contents::getContentElementById($guid['product_id']);
            $contentElement->noGuidAutoGenerate = false;
            $contentElement->guid->setGuid($guid['guid']);

            try {
                if ($contentElement->save(false, ['guid_id'])) {
                    $this->stdout("{$guid['product_id']} = {$guid['guid']} ok" . "\n", Console::FG_GREEN);
                } else {
                    var_dump(Html::errorSummary($contentElement));
                    var_dump($contentElement->id);
                    var_dump('-------------------------------');
                }
            } catch (Exception $exception) {
                var_dump($exception->getMessage());
            }

        }
    }

    /**
     * выравнивает гуиды, которые не совпадают с битриксовыми
     * @throws \yii\db\Exception
     */
    public function actionUpdateGuidsCce()
    {

        $sql = <<<SQL
SELECT cce.guid_id, gs.guid
FROM front2 .`sands_guid_storage`AS gs
INNER JOIN ss_web.cms_content_element AS cce ON cce.bitrix_id = gs.local_id 
INNER JOIN ss_web.ss_guids as ssg ON ssg.id = cce.guid_id
WHERE cce.guid_id IS NOT NULL
AND (gs.ENTITY = 'PRODUCT' AND cce.content_id = 2 OR gs.ENTITY = 'OFFER' AND cce.content_id = 10)
and gs.guid != ssg.guid
SQL;

        $guids = \Yii::$app->db->createCommand($sql)->queryAll();

        var_dump('count : ' . count($guids));

        sleep(3);

        foreach ($guids as $guid) {
            $guidObject = Guid::findOne($guid['guid_id']);
            $guidObject->guid = $guid['guid'];

            try {
                if ($guidObject->save(false, ['guid'])) {
                    $this->stdout("{$guid['guid_id']} = {$guid['guid']} ok" . "\n", Console::FG_GREEN);
                } else {
                    var_dump(Html::errorSummary($guidObject));
                    var_dump($guidObject->id);
                    var_dump('-------------------------------');
                }
            } catch (Exception $exception) {
                var_dump($exception->getMessage());
            }

        }
    }

    /**
     * устанавливает гуид базовой модификации
     * @throws \yii\db\Exception
     */
    public function actionSetBaseModificationGuids()
    {

        $sql = <<<SQL
SELECT cce.id AS product_id, gs.guid AS guid
FROM front2 .`sands_guid_storage`AS gs
INNER JOIN ss_web.cms_content_element AS cce ON cce.bitrix_id = gs.local_id
LEFT JOIN cms_content_element_property ccep ON ccep.element_id = cce.id AND ccep.property_id = (
  SELECT id FROM cms_content_property WHERE code = 'base_modification_guid' 
  ) 
WHERE ccep.value IS NULL
AND gs.ENTITY = 'BASE_OFFER' 
AND cce.content_id = 2
SQL;

        $guids = \Yii::$app->db->createCommand($sql)->queryAll();

        var_dump('count : ' . count($guids));

        sleep(3);

        foreach ($guids as $guid) {
            $contentElement = Contents::getContentElementById($guid['product_id']);
            $contentElement->relatedPropertiesModel->setAttribute('base_modification_guid', $guid['guid']);

            try {
                if ($contentElement->relatedPropertiesModel->save()) {
                    $this->stdout("{$guid['product_id']} = {$guid['guid']} ok" . "\n", Console::FG_GREEN);
                } else {
                    var_dump(Html::errorSummary($contentElement->relatedPropertiesModel));
                    var_dump($contentElement->id);
                    var_dump('-------------------------------');
                }
            } catch (Exception $exception) {
                var_dump($exception->getMessage());
            }

        }
    }

    public function actionSetGuidsTree()
    {

        $sql = <<<SQL
SELECT tree.id AS tree_id, gs.guid AS guid
FROM front2 .`sands_guid_storage`AS gs
INNER JOIN ss_web.cms_tree AS tree ON tree.bitrix_id = gs.local_id 
WHERE gs.ENTITY IN('SECTION') AND tree.guid_id IS NULL
SQL;

        $guids = \Yii::$app->db->createCommand($sql)->queryAll();

        var_dump('count : ' . count($guids));

        sleep(3);

        foreach ($guids as $guid) {

            var_dump($guid['tree_id']);

            if ($tree = TreeList::getTreeById($guid['tree_id'])) {

                $tree->guid->setGuid($guid['guid']);

                if (!$tree->save()) {
                    var_dump($tree->getErrors());
                }
            }
        }
    }


    public function actionProducts()
    {

        $this->stdout("Sync Products\n", Console::FG_CYAN);

        $query = "
            insert IGNORE INTO cms_content_element (content_id, bitrix_id, code, name, meta_title, created_at, updated_at, published_at, tree_id, active, show_counter, show_counter_start, priority)
            select
                " . SyncController::LOCAL_PRODUCT_BLOCK_ID . ",
                b.id as bitrix_id,
                CONCAT(b.id,'-',b.code),
                b.name as name,
                CASE WHEN exists_element.id IS NOT NULL OR exists_element.meta_title IS NOT NULL THEN exists_element.meta_title ELSE b.name END as meta_title,
                unix_timestamp(b.date_create) as created_at,
                unix_timestamp(b.timestamp_x) as updated_at,
                unix_timestamp(b.date_create) as published_at,
                ct.id as tree_id,
                b.active,
                b.show_counter,
                unix_timestamp(b.show_counter_start),
                unix_timestamp(bp.value) as priority
                from front2.b_iblock_element b
                left join cms_tree ct ON ct.bitrix_id=b.iblock_section_id
                left join cms_content_element exists_element ON exists_element.bitrix_id=b.id and exists_element.content_id=" . SyncController::LOCAL_PRODUCT_BLOCK_ID . "
                left join front2.b_iblock_element_property bp ON bp.IBLOCK_ELEMENT_ID=b.ID AND bp.IBLOCK_PROPERTY_ID=419
                where
                    b.iblock_id=" . SyncController::BITRIX_PRODUCT_BLOCK_ID . "
                    and b.wf_parent_element_id is null
                    and exists_element.id is null
        ";

        $this->stdout("Inserting Products", Console::FG_YELLOW);

        $transaction = \Yii::$app->db->beginTransaction();

        try {

            $affected = \Yii::$app->db->createCommand($query)->execute();
            $this->stdout(" done. Affected " . $affected . "\n", Console::FG_GREEN);

            $transaction->commit();

        } catch (\yii\db\Exception $e) {

            $this->logError($e);

            \Yii::error('Импорт товаров не прошел!');

            $transaction->rollBack();
            return false;

        }

    }

    public function actionOffers()
    {

        $this->stdout("Sync Offers\n", Console::FG_CYAN);
        $this->stdout("Last update: ", Console::FG_YELLOW);

        $lastUpdate = \Yii::$app->db->createCommand("SELECT MAX(updated_at) FROM cms_content_element WHERE content_id=" . SyncController::LOCAL_OFFERS_BLOCK_ID)->queryScalar();

        $this->stdout(date('Y-m-d H:i:s', (int)$lastUpdate) . "\n", Console::FG_GREEN);

        if (time() < (int)$lastUpdate) {
            $this->stdout("Current time < Last update time\n", Console::FG_RED);
            return false;
        }

        $query = "
            insert into cms_content_element (content_id, bitrix_id, parent_content_element_id, code, name, meta_title, created_at, updated_at, published_at, active)
            select
                " . SyncController::LOCAL_OFFERS_BLOCK_ID . ",
                b.id as bitrix_id,
                parent_element.id as parent_content_element_id,
                CONCAT(b.id,'-',parent.value),
                b.name as name,
                CASE WHEN exists_element.id IS NOT NULL OR exists_element.meta_title IS NOT NULL THEN exists_element.meta_title ELSE b.name END as meta_title,
                unix_timestamp(b.date_create) as created_at,
                unix_timestamp(b.timestamp_x) as updated_at,
                unix_timestamp(b.date_create) as published_at,
                b.active
            from front2.b_iblock_element b
            left join front2.b_iblock_element_property parent ON parent.IBLOCK_ELEMENT_ID=b.ID AND parent.IBLOCK_PROPERTY_ID=58
            left join cms_content_element parent_element ON parent_element.bitrix_id=parent.value_num and parent_element.content_id=2
            left join cms_content_element exists_element ON exists_element.bitrix_id=b.id and exists_element.content_id=" . SyncController::LOCAL_OFFERS_BLOCK_ID . "
            where
                b.iblock_id=" . SyncController::BITRIX_OFFERS_BLOCK_ID . "
                and parent_element.id is not null
                and exists_element.id is null
        ";

        $this->stdout("Inserting Offers", Console::FG_YELLOW);

        $transaction = \Yii::$app->db->beginTransaction();

        try {

            $affected = \Yii::$app->db->createCommand($query)->execute();
            $this->stdout(" done. Affected " . $affected . "\n", Console::FG_GREEN);

            $transaction->commit();

        } catch (\yii\db\Exception $e) {

            $this->logError($e);

            \Yii::error('Импорт предложений не прошел!');

            $transaction->rollBack();
            return false;

        }

    }

    public function actionShopProducts()
    {

        $this->stdout("Sync ShopProducts\n", Console::FG_CYAN);

        $query = "
            insert into shop_product (id, created_by, updated_by, created_at, updated_at, quantity, quantity_trace, can_buy_zero, measure_id, product_type)
            select
            	ifnull(exists_element.id, ce.id) as id,
            	ce.created_by,
            	ce.updated_by,
            	ce.created_at,
            	ce.updated_at,
            	0,
            	'Y',
            	'N',
            	5,
            	CASE WHEN ce.content_id=2 THEN 'simple' ELSE
            	CASE WHEN ce.content_id=10 THEN 'offers' END END
            from cms_content_element ce
            left join shop_product exists_element ON exists_element.id=ce.id
            where ce.content_id in (2,10)
            on duplicate key update updated_by=VALUES(updated_by), updated_at=VALUES(updated_at)
        ";

        $this->stdout("Inserting Products", Console::FG_YELLOW);

        try {

            \Yii::$app->db->createCommand("SET FOREIGN_KEY_CHECKS=0;")->execute();

            $transaction = \Yii::$app->db->beginTransaction();

            $affected = \Yii::$app->db->createCommand($query)->execute();
            $this->stdout(" done. Affected " . $affected . "\n", Console::FG_GREEN);

            $transaction->commit();

            \Yii::$app->db->createCommand("SET FOREIGN_KEY_CHECKS=1;")->execute();

        } catch (\yii\db\Exception $e) {

            $this->logError($e);

            \Yii::$app->db->createCommand("SET FOREIGN_KEY_CHECKS=1;")->execute();

            $transaction->rollBack();
            return false;

        }

        /** Включить модификации у родительских товаров */
        $this->stdout("Enable Offers product_type for parent_items ", Console::FG_CYAN);

        try {

            $sql = trim(sprintf("update shop_product SET
product_type='offers' where id in (select distinct parent_content_element_id from cms_content_element where content_id=%d)",
                SyncController::LOCAL_OFFERS_BLOCK_ID));

            if ($affected = \Yii::$app->db->createCommand($sql)->execute())
                $this->stdout(" done. Affected " . $affected . "\n", Console::FG_GREEN);

        } catch (\yii\db\Exception $e) {

            $this->stdout("\tCant update parent element\n{$e}\n", Console::FG_RED);
            return false;

        }

        return true;


    }


    public function actionCategories()
    {

        $this->stdout("Set PRODUCTS/OFFERS categories\n", Console::FG_CYAN);

        $query = "
            update cms_content_element t1
                inner join (
                        select
                        ce.id as element_id,
                        ct.id as category_id
                    from cms_content_element ce
                    left join front2.b_iblock_element b ON b.id=ce.bitrix_id
                    LEFT JOIN cms_tree ct ON ct.bitrix_id=b.IBLOCK_SECTION_ID
                    where
                        ce.content_id=" . SyncController::LOCAL_PRODUCT_BLOCK_ID . "
                        and b.id is not null
                ) t2 ON t2.element_id=t1.id
                set
                    t1.tree_id=t2.category_id
        ";

        $this->stdout("Updating Products ", Console::FG_YELLOW);

        try {

            $transaction = \Yii::$app->db->beginTransaction();

            $affected = \Yii::$app->db->createCommand($query)->execute();
            $this->stdout(" done. Affected " . $affected . "\n", Console::FG_GREEN);

            $transaction->commit();

        } catch (\yii\db\Exception $e) {

            $this->logError($e);

            $transaction->rollBack();
            return false;
        }

        $query = "
            update cms_content_element t1
                inner join (
                    select
                        ce.id as element_id,
                        ce.tree_id as category_id
                    from cms_content_element ce
                    where
                        ce.content_id=" . SyncController::LOCAL_PRODUCT_BLOCK_ID . "
                        and ce.parent_content_element_id is null
                ) t2 ON t2.element_id=t1.parent_content_element_id
                set
                    t1.tree_id=t2.category_id
        ";

        $this->stdout("Updating Offers ", Console::FG_YELLOW);

        try {

            $transaction = \Yii::$app->db->beginTransaction();

            $affected = \Yii::$app->db->createCommand($query)->execute();
            $this->stdout(" done. Affected " . $affected . "\n", Console::FG_GREEN);

            $transaction->commit();

        } catch (\yii\db\Exception $e) {

            $this->logError($e);

            $transaction->rollBack();
            return false;
        }

    }

    public function actionCleanDeletedOffers()
    {

        $this->stdout("Cleaning deleted product OFFERS\n", Console::FG_CYAN);
        $this->stdout("SET name=[DELETED]+name, active='N', parent_content_element_id=NULL\n", Console::FG_CYAN);

        $query = "
                update cms_content_element t1
                inner join (
                    SELECT
                        ce.id as element_id
                    FROM cms_content_element ce
                    LEFT JOIN front2.b_iblock_element b ON b.id = ce.bitrix_id AND b.iblock_id= " . SyncController::BITRIX_OFFERS_BLOCK_ID . "
                    where
                        ce.content_id=" . SyncController::LOCAL_OFFERS_BLOCK_ID . "
                        and b.id is null
                ) t2 ON t2.element_id=t1.id
                set
                    t1.active='N',
                    t1.parent_content_element_id=NULL,
                    t1.tree_id=NULL,
                    t1.name=CONCAT('[DELETED] ', TRIM(BOTH FROM REPLACE(t1.name,'[DELETED]','')))
        ";

        $this->stdout("Updating elements ", Console::FG_YELLOW);

        try {

            $transaction = \Yii::$app->db->beginTransaction();

            $affected = \Yii::$app->db->createCommand($query)->execute();
            $this->stdout(" done. Affected " . $affected . "\n", Console::FG_GREEN);

            $transaction->commit();

        } catch (\yii\db\Exception $e) {

            $this->logError($e);

            $transaction->rollBack();
            return false;
        }

        return true;

    }

    /**
     * пересчет count_content_element
     * @return bool
     */
    public function actionRecountTreeContentElement()
    {
        /* // original query
        $query = <<<SQL
UPDATE cms_tree AS tree, (
    SELECT el.tree_id, COUNT(*) AS cnt 
    FROM cms_content_element AS el
    INNER JOIN shop_product AS shop ON shop.id = el.id
    WHERE el.content_id = 2 AND el.tree_id IS NOT NULL AND el.active = 'Y' AND shop.quantity > 0
    GROUP BY el.tree_id
) AS child 
  SET tree.count_content_element = child.cnt 
WHERE tree.id = child.tree_id
SQL;*/
        $query = <<<SQL
UPDATE cms_tree AS t
SET t.count_content_element = (SELECT sum(child.cnt)
                               FROM (SELECT *
                                     FROM cms_tree) AS tree, (
                                                               SELECT
                                                                 el.tree_id,
                                                                 COUNT(DISTINCT el.id) AS cnt
                                                               FROM cms_content_element AS el
                                                                 INNER JOIN shop_product AS shop ON shop.id = el.id
                                                                 INNER JOIN ss_shop_product_prices AS price ON price.product_id = el.id
                                                               WHERE el.content_id = 2 AND el.tree_id IS NOT NULL AND el.active = 'Y' AND
                                                                     shop.quantity >= 1 AND price.min_price >= 2 AND
                                                                     price.min_price IS NOT NULL
                                                                     AND el.image_id IS NOT NULL
                                                                     AND el.published_at <= NOW()
                                                                     AND NOT exists(SELECT 1
                                                                                    FROM cms_content_element_property cep
                                                                                    WHERE cep.element_id = el.id AND cep.property_id = 83 AND
                                                                                          cep.value IS NOT NULL)
                                                               GROUP BY el.tree_id
                                                             ) AS child
                               WHERE tree.id = child.tree_id
                                     AND tree.dir LIKE concat(t.dir, '%'));
SQL;

        $this->stdout("Recalc Products count_content_element field ", Console::FG_YELLOW);

        try {

            $transaction = \Yii::$app->db->beginTransaction();

            $affected = \Yii::$app->db->createCommand($query)->execute();
            $this->stdout(" done. Affected " . $affected . "\n", Console::FG_GREEN);

            $transaction->commit();

        } catch (\yii\db\Exception $e) {

            $this->logError($e);

            $transaction->rollBack();
            return false;
        }

        return true;
    }


    /**
     * Обновление счетчика количества потомков
     * @return int
     */
    private function updateCounterCCECountChildren()
    {
        $sql = <<<SQL
UPDATE cms_content_element AS cce, (
    SELECT parent_content_element_id, COUNT(*) AS cnt 
    FROM cms_content_element
    GROUP BY parent_content_element_id
) AS child 
  SET cce.count_children = child.cnt 
WHERE cce.id = child.parent_content_element_id AND cce.content_id = 2;
SQL;

        return \Yii::$app->db->createCommand($sql)->execute();
    }

    /**
     * Обновление счетчика количества картинок у cms_content_element
     * @return int
     */
    public function actionUpdateCountImages()
    {
        $sql = <<<SQL
UPDATE cms_content_element AS cce, (
    SELECT content_element_id AS element_id, COUNT(*) AS cnt 
    FROM cms_content_element_image
    GROUP BY content_element_id
) AS images 
  SET cce.count_images = images.cnt 
WHERE cce.id = images.element_id;
SQL;

        return \Yii::$app->db->createCommand($sql)->execute();
    }
}