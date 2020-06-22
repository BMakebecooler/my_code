<?php

namespace common\models\filtered\products\promo;

use common\lists\TreeList;
use common\models\filtered\products\Catalog;
use common\models\Tree;
use modules\shopandshow\lists\Shares;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

class PromoProductsShare extends Catalog
{

    public $code = 'non_existant_banner';

    public $share_id = null;

    public function init()
    {
        parent::init();

        if ($this->share_id) {
            $share = Shares::getById($this->share_id);
            $this->code = ($share) ? $share->code : null;
        }
    }

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
                        SELECT tree_id 
                        FROM cms_content_element AS cce
                        INNER JOIN ss_shares_products AS sp ON sp.product_id = cce.id
                        WHERE sp.banner_id = (SELECT id FROM ss_shares WHERE code = :code ORDER BY id ASC LIMIT 1) AND cce.tree_id IS NOT NULL
                        GROUP BY tree_id
                    ) AS cms_content_element ON cms_tree.id = cms_content_element.tree_id
                    WHERE cms_tree.active = 'Y'
                    GROUP BY cms_tree.id
                ) AS t GROUP BY menu_level
            ) AND NOT tree.id = 9
SQL;

        if ($pid) {
            $sql .= ' AND tree.pid = ' . $pid;
        }

        return Tree::getDb()->cache(function ($db) use ($sql, $level) {
            return Tree::findBySql($sql, [
                ':level' => $level,
                ':code' => $this->code,
            ])->all();
        }, HOUR_2);

    }

    public function search(ActiveDataProvider $activeDataProvider)
    {
        /**
         * @var $query ActiveQuery
         */
        $query = $activeDataProvider->query;

        /*
        $query->andWhere('cms_content_element.id IN (SELECT product_id FROM ss_shares_products WHERE banner_id = (SELECT id FROM ss_shares WHERE code = :code  ORDER BY id ASC LIMIT 1))', [
            ':code' => $this->code
        ]);
        */

        $query->innerJoin('ss_shares_products', 'ss_shares_products.banner_id = (SELECT id FROM ss_shares WHERE code = :code  ORDER BY id ASC LIMIT 1) AND cms_content_element.id = ss_shares_products.product_id', [
            ':code' => $this->code
        ]);

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