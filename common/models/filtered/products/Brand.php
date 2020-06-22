<?php

namespace common\models\filtered\products;

use common\lists\TreeList;
use common\models\cmsContent\CmsContentProperty;
use common\models\Tree;
use yii\data\ActiveDataProvider;

class Brand extends Catalog
{
    /**
     * @var int ID бренда для фильтра товаров
     */
    public $brandId;

    public function init()
    {
        parent::init();

        if (!$this->brandId) {
            throw new \RuntimeException('Не указан бренд для фильтра');
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
                    INNER JOIN cms_content_element ON cms_content_element.tree_id = cms_tree.id 
                    LEFT JOIN cms_content_element_property AS not_public_value
                      ON 
                        not_public_value.element_id = cms_content_element.id 
                        AND not_public_value.property_id = 
                            (SELECT id FROM `cms_content_property` WHERE `content_id` = '2' AND `code` = 'NOT_PUBLIC')
                    LEFT JOIN cms_content_element_property AS brand
                      ON 
                        brand.element_id = cms_content_element.id 
                        AND brand.property_id = 
                            (SELECT id FROM `cms_content_property` WHERE `content_id` = '2' AND `code` = 'BRAND')
                    WHERE `cms_content_element`.`content_id` = 2
                      AND cms_content_element.active = 'Y'
                      AND cms_tree.active = 'Y'
                      AND (not_public_value.value IS NULL OR not_public_value.value = '')
                      AND brand.value = $this->brandId
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
        }, MIN_30);

        /*
        return Tree::findBySql($sql, [
            ':level' => $level,
        ])->all();
        */
    }

    public function search(ActiveDataProvider $activeDataProvider)
    {
        $query = $activeDataProvider->query;

        /**
         * @var $query \yii\db\ActiveQuery
         */

        $brandProperty = CmsContentProperty::findOne(['code' => 'BRAND', 'content_id' => PRODUCT_CONTENT_ID]);

        $query->innerJoin('cms_content_element_property brand',
            'brand.property_id = :brand_property_id AND brand.value = :brand_id AND brand.element_id = cms_content_element.id'
        );
        $query->addParams([':brand_property_id' => $brandProperty->id, ':brand_id' => $this->brandId]);

        //* Разделы *//

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

        //* /Разделы *//

        parent::search($activeDataProvider);

        return $this;
    }

}