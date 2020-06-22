<?php

namespace common\models\filtered\products;

use common\lists\TreeList;
use common\models\Tree;
use modules\shopandshow\models\shop\ShopTypePrice;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

class Sale extends Catalog
{

    public $typeSale = ShopTypePrice::TYPE_SALE;

    protected $typeSaleId = null;

    public function init()
    {
        parent::init();
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
                    SELECT SUBSTRING_INDEX(SUBSTRING_INDEX(cms_tree.pids, '/', :level),'/',-1) AS menu_level
                    FROM `cms_tree` 
                    INNER JOIN `cms_content_element` ON `cms_tree`.`id` = `cms_content_element`.`tree_id` 
                    INNER JOIN `cms_content_element_property` ON cms_content_element.id = cms_content_element_property.element_id
                    INNER JOIN `cms_content_property` ON cms_content_property.id = cms_content_element_property.property_id
                    INNER JOIN shop_product AS shop ON shop.id = cms_content_element.id
                    WHERE (`cms_content_element`.`content_id` = 2) AND (`cms_content_property`.`code`='SHOPPINGCLUB_ACTIVE')
                    AND (`cms_content_element_property`.`value_enum`='1') AND cms_content_element.active = 'Y' AND shop.quantity > 0
                    GROUP BY `cms_tree`.`id`
                ) AS t
                GROUP BY menu_level
            ) AND NOT tree.id = 9
SQL;

        if ($pid) {
            $sql .= ' AND tree.pid = ' . $pid;
        }

        return Tree::findBySql($sql, [
            ':level' => $level,
        ])->all();
    }

    public function search(ActiveDataProvider $activeDataProvider)
    {
        /**
         * @var $query ActiveQuery
         */
        $query = $activeDataProvider->query;

        $query->innerJoin('cms_content_element_property', 'cms_content_element.id = cms_content_element_property.element_id');
        $query->innerJoin('cms_content_property', 'cms_content_property.id = cms_content_element_property.property_id');

        $query->andWhere(['cms_content_element.content_id' => PRODUCT_CONTENT_ID]);
        $query->andWhere(['cms_content_property.code' => 'SHOPPINGCLUB_ACTIVE']);
        $query->andWhere(['cms_content_element_property.value_enum' => '1']);

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