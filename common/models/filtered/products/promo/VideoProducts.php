<?php

/**
 * Товары где есть видео
 */

namespace common\models\filtered\products\promo;

use common\lists\TreeList;
use common\models\filtered\products\Catalog;
use common\models\Tree;
use skeeks\cms\models\CmsContentElementProperty;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\Query;

class VideoProducts extends Catalog
{

    /**
     * @param int $level - По умолчанию 3 это главный каталог
     * @param int $pid
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getCategories($level = 3, $pid = null)
    {
        $sql = <<<SQL
            SELECT tree.* 
            FROM cms_tree AS tree 
            WHERE tree.id IN (
                SELECT t.menu_level FROM (
                    SELECT SUBSTRING_INDEX(SUBSTRING_INDEX(CONCAT_WS('/', cms_tree.pids,cms_tree.id ), '/', :level),'/',-1) AS menu_level
                    FROM cms_tree 
                    INNER JOIN (
                      SELECT tree_id, element_id 
                      FROM cms_content_element_property AS property
                      INNER JOIN cms_content_element AS cce ON cce.id = property.element_id
                      WHERE property.property_id IN (
                        SELECT id FROM cms_content_property 
                        WHERE code IN('VIDEO_PRICE_SALE', 'VIDEO_PRICE_BASE', 'VIDEO_PRICE_TODAY', 'VIDEO_PRICE_DISCOUNTED')
                      ) AND cce.content_id = 2 AND cce.active = 'Y'
                      GROUP BY tree_id, element_id
                    ) AS cce ON cce.tree_id = cms_tree.id
                    WHERE cms_tree.active = 'Y'
                    GROUP BY cms_tree.id
                ) AS t
                GROUP BY menu_level
            ) AND NOT tree.id = 9
SQL;

        if ($pid) {
            $sql .= ' AND tree.pid = ' . $pid;
        }

        return Tree::getDb()->cache(function ($db) use ($sql, $level) {
            return Tree::findBySql($sql, [
                ':level' => $level,
            ])->all();
        }, HOUR_2);

    }

    public function search(ActiveDataProvider $activeDataProvider)
    {
        /**
         * @var $query ActiveQuery
         */
        $query = $activeDataProvider->query;

        $subQuery = <<<SQL
SELECT element_id 
FROM cms_content_element_property AS property
INNER JOIN cms_content_element AS cce ON cce.id = property.element_id
WHERE property.property_id IN (
    SELECT id FROM cms_content_property 
    WHERE code IN('VIDEO_PRICE_SALE', 'VIDEO_PRICE_BASE', 'VIDEO_PRICE_TODAY', 'VIDEO_PRICE_DISCOUNTED')
) AND cce.content_id = 2 AND cce.active = 'Y'
SQL;

        $query->innerJoin(['properties' => CmsContentElementProperty::tableName()], 'cms_content_element.id = properties.element_id 
            AND properties.property_id IN(110,111,112, 113)');

        $treeId = null;
        if ($this->category && !$this->subcategory) {
            $treeId = TreeList::getIdTreeByCode($this->category);
        } elseif ($this->subcategory) {
            $treeId = TreeList::getIdTreeByCode($this->subcategory);
        }

        if ($treeId) {
            $descendantsIds = TreeList::getDescendantsById($treeId);
            $query->andWhere(['cms_content_element.tree_id' => $descendantsIds]);
        }

        if ($this->sort == 'recommend') {
            //$query->addOrderBy(['ss_shares_products.priority' => SORT_ASC, 'ss_shop_product_prices.discount_percent' => SORT_DESC]);
        }

        parent::search($activeDataProvider);

        return $this;
    }


}