<?php

namespace common\models\filtered\products\promo;

use common\lists\TreeList;
use common\models\Tree;
use common\models\filtered\products\Catalog;
use modules\shopandshow\models\shop\ShopContentElement;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

class ProductsLiquidation extends Catalog
{

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
                    SELECT SUBSTRING_INDEX(SUBSTRING_INDEX(CONCAT_WS('/', cms_tree.pids,cms_tree.id ), '/', :level),'/',-1) AS menu_level
                    FROM cms_tree 
                    INNER JOIN cms_content_element ON cms_content_element.tree_id = cms_tree.id 
                    INNER JOIN shop_product AS shop ON shop.id = cms_content_element.id
                    LEFT JOIN cms_content_element_property AS not_public_value
                      ON 
                        not_public_value.element_id = cms_content_element.id 
                        AND not_public_value.property_id = 
                            (SELECT id FROM `cms_content_property` WHERE `content_id` = '2' AND `code` = 'NOT_PUBLIC')
                    
                    WHERE `cms_content_element`.`content_id` IN (2)
                      AND cms_content_element.active = 'Y'
                      AND cms_content_element.count_children > 0
                      AND cms_tree.active = 'Y'
                      AND (
                        SELECT COUNT(1) AS num
                        FROM cms_content_element AS cce
                        LEFT JOIN shop_product AS sp ON sp.id=cce.id
                        WHERE
                        cce.parent_content_element_id=cms_content_element.id
                        AND sp.quantity>0
                      ) = 1
                      AND (not_public_value.value IS NULL OR not_public_value.value = '')
                    GROUP BY `cms_tree`.`id`
                ) AS t
                GROUP BY menu_level
            ) AND NOT tree.id = 9
SQL;

        if ($pid) {
            $sql .= ' AND tree.pid = ' . $pid;
        }else{
            //Только мода и Украшения
            $sql .= ' AND tree.id IN (1626,1623,1649)';
        }


        return Tree::getDb()->cache(function ($db) use ($sql, $level) {
            return Tree::findBySql($sql, [
                ':level' => $level,
            ])->all();
        }, HOUR_2);

        /*
        return Tree::findBySql($sql, [
            ':level' => $level,
        ])->all();
        */
    }

    public function search(ActiveDataProvider $activeDataProvider)
    {
        /**
         * @var $query ActiveQuery
         */

        $query = $activeDataProvider->query;

        $subQuery = ShopContentElement::find()
            ->select('count(1)')
            ->alias('cce')
//            ->joinWith('shopProduct')
            ->andWhere('new_quantity>0')
            ->andWhere('cce.parent_content_element_id=cms_content_element.id')
            ->createCommand()->getRawSql();

        $query->andWhere("cms_content_element.count_children>0", [
        ])->andWhere("({$subQuery}) = 1");

        $treeId = null;
        if ($this->category && !$this->subcategory) {
            $treeId = TreeList::getIdTreeByCode($this->category);
        } elseif ($this->subcategory) {
            $treeId = TreeList::getIdTreeByCode($this->subcategory);
        }

        //Только для Мода и Украшения
        if (!$treeId){
            $descendantsIds = array_merge(
                TreeList::getDescendantsById(1626) ?? [],
                TreeList::getDescendantsById(1623) ?? [],
                TreeList::getDescendantsById(1649) ?? []
            );
            $query->andWhere(['cms_content_element.tree_id' => $descendantsIds]);
        }

        if ($treeId) {
            $descendantsIds = TreeList::getDescendantsById($treeId);
            $descendantsIds[] = $treeId;
            $query->andWhere(['cms_content_element.tree_id' => $descendantsIds]);
        }

        parent::search($activeDataProvider);

        return $this;
    }


}