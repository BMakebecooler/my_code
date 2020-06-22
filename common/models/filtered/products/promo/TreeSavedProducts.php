<?php

namespace common\models\filtered\products\promo;

use common\models\filtered\products\Catalog;
use common\models\Tree;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

class TreeSavedProducts extends Catalog
{

    public $perPage = 80;

    /** @var Tree */
    public $tree = null;

    /**
     * @return array|\skeeks\cms\models\Tree
     */
    public function getCategories()
    {
        if ($this->tree) {
            return $this->tree->descendants;
        }

        return [];
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