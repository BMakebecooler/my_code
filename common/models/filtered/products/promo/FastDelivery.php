<?php

namespace common\models\filtered\products\promo;

use common\lists\TreeList;
use common\models\Tree;
use common\models\filtered\products\Catalog;
use skeeks\cms\models\CmsContentElementProperty;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

class FastDelivery extends Catalog
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
                    LEFT JOIN cms_content_element_property AS not_public_value
                      ON 
                        not_public_value.element_id = cms_content_element.id 
                        AND not_public_value.property_id = 
                            (SELECT id FROM `cms_content_property` WHERE `content_id` = '2' AND `code` = 'NOT_PUBLIC')
                    LEFT JOIN cms_content_element_property AS rest
                      ON 
                        rest.element_id = cms_content_element.id 
                        AND rest.property_id = 
                            (SELECT id FROM `cms_content_property` WHERE `content_id` = '2' AND `code` = 'REST')
                    WHERE `cms_content_element`.`content_id` = 2
                      AND cms_content_element.active = 'Y'
                      AND cms_tree.active = 'Y'
                      AND (not_public_value.value IS NULL OR not_public_value.value = '')
                      AND ifnull(rest.value,0) > 0
                      AND NOT EXISTS (
                        SELECT 1
                        FROM cms_content_element offer
                        INNER JOIN cms_content_element_property offer_props ON offer_props.element_id = offer.id 
                          AND offer_props.property_id = (SELECT id FROM cms_content_property WHERE code = "REST" AND content_id = 10)
                        WHERE offer.parent_content_element_id = cms_content_element.id
                          AND offer_props.value_num <= 0
                      )
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

        $query->innerJoin(
            ['rest' => CmsContentElementProperty::tableName()],
            'rest.element_id = cms_content_element.id 
             AND rest.property_id = (SELECT id FROM cms_content_property WHERE code = "REST" AND content_id = 2) 
             AND IFNULL(rest.value, 0) > 0'
        );
        $query->andWhere(new \yii\db\Expression('
        NOT EXISTS (
           SELECT 1
           FROM cms_content_element offer
           INNER JOIN cms_content_element_property offer_props ON offer_props.element_id = offer.id 
             AND offer_props.property_id = (SELECT id FROM cms_content_property WHERE code = "REST" AND content_id = 10)
           WHERE offer.parent_content_element_id = cms_content_element.id
             AND offer_props.value_num <= 0
        )
        '));

        $treeId = null;
        if ($this->category && !$this->subcategory) {
            $treeId = TreeList::getIdTreeByCode($this->category);
        } elseif ($this->subcategory) {
            $treeId = TreeList::getIdTreeByCode($this->subcategory);
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