<?php

namespace common\models\filtered\products;

use common\lists\TreeList;
use common\models\Tree;
use modules\shopandshow\models\shop\ShopTypePrice;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

class PromoSale extends Catalog
{

    public $typeSale = ShopTypePrice::TYPE_SALE;

    protected $typeSaleId = null;

    public function init()
    {
        parent::init();

        $this->typeSaleId = ShopTypePrice::findOne(['code' => $this->typeSale])->id;
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
                    FROM `cms_tree` 
                    INNER JOIN `cms_content_element` ON `cms_tree`.`id` = `cms_content_element`.`tree_id` 
                    INNER JOIN `ss_shop_product_prices` AS price ON `price`.`product_id` = `cms_content_element`.`id`
                    INNER JOIN shop_product AS shop ON shop.id = cms_content_element.id
                    WHERE (`cms_content_element`.`content_id` = 2) AND (`price`.`type_price_id`= :type_price_id)
                      AND cms_content_element.active = 'Y' AND shop.quantity > 0 AND cms_tree.active = 'Y' 
                    GROUP BY `cms_tree`.`id`
                ) AS t
                GROUP BY menu_level
            ) AND NOT tree.id = 9
SQL;

        if ($pid) {
            $sql .= ' AND tree.pid = ' . $pid;
        }

        return Tree::getDb()->cache(function ($db) use ($sql, $level) {
            return Tree::findBySql($sql, [
                ':type_price_id' => $this->typeSaleId,
                ':level' => $level,
            ])->all();
        }, MIN_30);
    }

    public function search(ActiveDataProvider $activeDataProvider)
    {
        /**
         * @var $query ActiveQuery
         */
        $query = $activeDataProvider->query;

        $query->andWhere(['ss_shop_product_prices.type_price_id' => $this->typeSaleId]);

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

        parent::search($activeDataProvider);

        return $this;
    }


}