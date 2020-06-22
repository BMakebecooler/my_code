<?php

namespace common\models\filtered\products\promo;

use common\models\filtered\products\Catalog;
use common\models\Tree;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

class NewYear extends Catalog
{

    public $perPage = 80;

    /** @var Tree */
    public $tree = null;

    /**
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getCategories()
    {
        $sql = <<<SQL
            SELECT tree.* 
            FROM cms_tree AS tree 
            WHERE tree.pid = (select id from cms_tree where code = 'newyear')
            ORDER BY tree.priority
SQL;

        return Tree::findBySql($sql)->all();
    }

    public function search(ActiveDataProvider $activeDataProvider)
    {
        /**
         * @var $query ActiveQuery
         */
        $query = $activeDataProvider->query;

        $property = $this->tree->relatedPropertiesModel->getRelatedProperty('savedProducts');

        $query->innerJoin(\skeeks\cms\models\CmsTreeProperty::tableName().' as prop', [
            'prop.element_id' => $this->tree->id,
            'prop.property_id' => $property->id,
            'prop.value' => new \yii\db\Expression('cms_content_element.id')
        ]);
        $query->orderBy(['prop.value_num' => SORT_ASC]);


        //parent::search($activeDataProvider);

        return $this;
    }


}