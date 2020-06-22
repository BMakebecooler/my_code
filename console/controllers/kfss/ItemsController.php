<?php

/**
 * php ./yii kfss/items
 * php ./yii kfss/items/recount-tree-content-element
 */

namespace console\controllers\kfss;

use console\controllers\sync\SyncController;
use yii\helpers\Console;


/**
 * Class ItemsController
 *
 * @package console\controllers
 */
class ItemsController extends SyncController
{

    public function actionIndex()
    {
        $this->actionRecountTreeContentElement();
        $this->updateCounterCCECountChildren(); //Вроде норм под новые рельсы
//        $this->updateShopProductQuantity(OFFERS_CONTENT_ID); //Устарело, не актуально
//        $this->updateShopProductQuantity(CARD_CONTENT_ID); //Устарело, не актуально
//        $this->updateShopProductType(); //Устарело, не актуально
    }

    //считаем товары в
    public function actionRecountTreeContentElementNew()
    {
        //Сначала запишем кол-во товаров непосредственно находящихся в товарах
        //Затем пройдемся по разделам где все еещ числится 0 товаров и попробуем записать суммарное кол-во товаров из подразделов, причем начинать надо из "глубины"

        $query = <<<SQL
UPDATE cms_tree AS t
SET t.count_content_element = (SELECT sum(child.cnt)
                               FROM (SELECT *
                                     FROM cms_tree) AS tree, (
                                                               SELECT
                                                                 el.tree_id,
                                                                 COUNT(DISTINCT el.id) AS cnt
                                                               FROM cms_content_element AS el
                                                                 /*INNER JOIN shop_product AS shop ON shop.id = el.id*/
                                                                 /*INNER JOIN ss_shop_product_prices AS price ON price.product_id = el.id*/
                                                               WHERE el.content_id = 2 AND el.tree_id IS NOT NULL AND el.active = 'Y' AND
                                                                     el.new_quantity >= 1 AND el.new_price >= 2 AND el.new_not_public!=1
                                                                     /*price.min_price IS NOT NULL*/
                                                                     AND el.image_id IS NOT NULL
                                                                     AND el.published_at <= NOW()
                                                                     /*AND NOT exists(SELECT 1
                                                                                    FROM cms_content_element_property cep
                                                                                    WHERE cep.element_id = el.id AND cep.property_id = 83 AND
                                                                                          cep.value IS NOT NULL)*/
                                                               GROUP BY el.tree_id
                                                             ) AS child
                               WHERE tree.id = child.tree_id
                                     AND tree.dir LIKE concat(t.dir, '%'));
SQL;

        $this->stdout("Recalc Products count_content_element field ", Console::FG_YELLOW);

        try {

            $transaction = \Yii::$app->db->beginTransaction();

            $affected = \Yii::$app->db->createCommand($query)->execute();
            $this->stdout(" done. Affected " . $affected  . PHP_EOL, Console::FG_GREEN);

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
                                                                     /*AND NOT exists(SELECT 1
                                                                                    FROM cms_content_element_property cep
                                                                                    WHERE cep.element_id = el.id AND cep.property_id = 83 AND
                                                                                          cep.value IS NOT NULL)*/
                                                               GROUP BY el.tree_id
                                                             ) AS child
                               WHERE tree.id = child.tree_id
                                     AND tree.dir LIKE concat(t.dir, '%'));
SQL;

        $this->stdout("Recalc Products count_content_element field ", Console::FG_YELLOW);

        try {

            $transaction = \Yii::$app->db->beginTransaction();

            $affected = \Yii::$app->db->createCommand($query)->execute();
            $this->stdout(" done. Affected " . $affected  . PHP_EOL, Console::FG_GREEN);

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
     * TODO Не выставляет значения 0, доработать
     * @return int
     */
    private function updateCounterCCECountChildren()
    {
        $this->stdout("Recalc Products count_children field" . PHP_EOL, Console::FG_YELLOW);
        $sql = <<<SQL
UPDATE cms_content_element AS cce, (
    SELECT parent_content_element_id, COUNT(*) AS cnt 
    FROM cms_content_element
    GROUP BY parent_content_element_id
) AS child 
  SET cce.count_children = child.cnt 
WHERE cce.id = child.parent_content_element_id AND cce.content_id in (2, 5);
SQL;

        return \Yii::$app->db->createCommand($sql)->execute();
    }

    private function updateShopProductQuantity($contentId = OFFERS_CONTENT_ID)
    {
        $this->stdout("Recalc ShopProducts quantity for content #{$contentId}" . PHP_EOL, Console::FG_YELLOW);

        //см.так же modules/shopandshow/models/newEntities/shop/ShopProduct.php :: recalcQuantity(..)
        //см.так же modules/shopandshow/models/newEntities/common/CmsContentElementModel.php :: recalcQuantity(..)

        //ДЛЯ ПОДСЧЕТА В ТОВАР->КАРТОЧКА
        //Если родитель не база - суммируем все (это будут тоже не базы).
        //Если родитель база - то проверяем кол-во элементов, если ли только один (база) - берем число отсюда, если еще что есть - беремчисло из простых товаров
        //
        //ДЛЯ ПОДСЧЕТА В КАРТОЧКА->ЛОТ
        //[DEPRECATED] Проверям кол-во карточек, если больше одной - берем обычное кол-во, если одна - берем базу.

        $sql = <<<SQL
UPDATE shop_product t1
                    INNER JOIN (
                        SELECT
                            sum(CASE WHEN ce.is_base!='Y' AND t.quantity > 0 THEN t.quantity ELSE 0 END) AS sum_quantity,
                            sum(CASE WHEN ce.is_base='Y' AND t.quantity > 0 THEN t.quantity ELSE 0 END) AS sum_quantity_base,
                            ce.parent_content_element_id AS element_id,
                            ce.content_id,
                            ce.is_base,
                            parent_element.is_base       AS parent_is_base,
                            COUNT(1) AS num
                        FROM cms_content_element ce, shop_product t, cms_content_element AS parent_element
                        WHERE
                            ce.content_id = :content_id
                            AND ce.tree_id IS NOT NULL
                            AND ce.active = 'Y'
                            AND parent_element.id = ce.parent_content_element_id
                            AND t.id = ce.id
                            GROUP BY ce.parent_content_element_id
                    ) t2 ON t2.element_id=t1.id
SET t1.quantity = IF(
    t2.content_id = 10,
    (
      IF(
          t2.num = 1 AND t2.is_base = 'Y', -- в карточке всего одна модификация и она базовая
          t2.sum_quantity_base,
          t2.sum_quantity -- в карточке 1 нормальная модификация или несколько любых (из которых учтем только не базовые)
      )
    ),
    (
      t2.sum_quantity + t2.sum_quantity_base -- в карточкая базовость более не учитываем, плюсуем все
    )
)
;
SQL;

        return \Yii::$app->db->createCommand($sql, [':content_id' => $contentId])->execute();
    }

    /**
     * Обновление типа продукта (лота) - simple/offers в зависимости от кол-ва модификаций
     * @return int
     */
    private function updateShopProductType(){

        $this->stdout("Update ShopProducts product_type field" . PHP_EOL, Console::FG_YELLOW);

        $productContentId = PRODUCT_CONTENT_ID;

        $query = <<<SQL
UPDATE shop_product, cms_content_element AS cce 
SET product_type = IF(
    (
        SELECT COUNT(1) AS num
        FROM cms_content_element AS products
          LEFT JOIN cms_content_element AS cards ON cards.parent_content_element_id=products.id
          LEFT JOIN cms_content_element AS offers ON offers.parent_content_element_id=cards.id
        WHERE 
          products.content_id={$productContentId} 
          AND products.active='Y' 
          AND offers.id IS NOT NULL 
          AND products.id=shop_product.id
    ) > 1, -- 1 - только базовая модификация
    'offers',
    'simple'
)
WHERE 
  cce.active='Y'
  AND cce.id=shop_product.id
  AND cce.content_id={$productContentId}
  -- AND shop_product.id=224143 -- для теста
SQL;

        return \Yii::$app->db->createCommand($query)->execute();
    }
}