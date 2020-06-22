<?php


namespace common\helpers;


use common\components\cache\PageCache;
use common\lists\TreeList;
use common\models\cmsContent\CmsContentProperty;
use common\models\CmsTree;
use common\models\ProductParamProduct;
use common\models\ProductParam;
use common\models\ProductParamType;
use modules\shopandshow\models\shop\ShopContentElement;

class Filter
{
    public static $typePrice = 'KFSS_PRICE';

    public static function getFilterGroups($searchModel, $enabledPropTypes = [], $onlyPrice = false)
    {
        $filterGroups = [];
        $return = [];
        $flagCheckedPrice = false;
        if ($searchModel->price_from || $searchModel->price_to || $searchModel->isOnlyDiscountProduct) {
            $flagCheckedPrice = true;
        }

        $types = ProductParamType::find()
            ->andWhere(['active' => 1])
            ->orderBy(['sort' => SORT_DESC])
            ->asArray()
            ->all();

        if ($searchModel->properties && !$onlyPrice) {
            foreach ($searchModel->properties as $property) {

                if (count($enabledPropTypes) && !in_array($property['property_id'], $enabledPropTypes)) {
                    continue;
                }

                $flagChecked = false;
                if (!isset($property['values']) && $property['values_orig']) {
                    $property['values'] = $property['values_orig'];
                }
                $property['values'] = self::sortFilterValues($property['values']);

                foreach ($property['values'] as &$value) {
                    if (in_array($value['value'], $searchModel->productParams)) {
                        $value['checked'] = true;
                        $flagChecked = true;
                    } else {
                        $value['checked'] = false;
                    }
                }

                $filterGroups[$property['code']] = [
                    'code' => $property['code'],
                    'id' => $property['property_id'],
                    'name' => $property['name'],
                    'type' => 'checkbox',
                    'filters' => $property['values'],
                    'checked' => $flagChecked
                ];
            }
        }

        foreach ($types as $type) {

            if ($type['code'] == static::$typePrice) {
                $return[] = [
                    'id' => 'price',
                    'name' => 'Цена',
                    'type' => 'price',
                    'checked' => $flagCheckedPrice,
                    'filters' => [
                        'from' => (int)$searchModel->price_from,
                        'to' => (int)$searchModel->price_to,
                    ],
                ];
            } else {
                if (isset($filterGroups[$type['code']])) {
                    $return[] = $filterGroups[$type['code']];
                }
            }


        }
        return $return;
    }

    public static function getPropertiesBrand($brandId)
    {
        $query = \common\models\ProductParam::getProductsFiltersQuery();
        $query->andWhere([
            'lot.new_brand_id' => $brandId
        ]);
        $data = $query->asArray()->all();
        return $data;
    }


    public static function getPropertiesSearch($products)
    {
        $query = \common\models\ProductParam::getProductsFiltersQuery();
        $query->andWhere(['IN', 'lot.id', $products]);
        $data = $query->asArray()->all();
        return $data;
    }

    public static function getPropertiesProfile($profileId, $profileParams, $descendantsIds = null)
    {
        $data = \Yii::$app->cache->get('profile-properties_' . $profileId);

        if ($data === false) {
            $query = \common\models\ProductParam::getProductsFiltersQuery();
            $query->leftJoin(ProductParamProduct::tableName() . ' AS product_param_product_profile',
                'product_param_product_profile.card_id=card.id');
            $query->andWhere(['AND', ['IN', 'product_param_product_profile.product_param_id', $profileParams]]);
            $query->andWhere(['not', ['product_param_product_profile.id' => null]]);

            if ($descendantsIds) {
                $query->andWhere(['IN', 'lot.tree_id', $descendantsIds]);
            }

            $data = $query->asArray()->all();

            \Yii::$app->cache->set('profile-properties_' . $profileId, $data, PageCache::CACHE_DURATION);

        }

        return $data;
    }

    public static function getPropertiesPromo($products, $segment, $descendantsIds = null,
                                              $disableProducts = null, $segmentCardsDisable = [], $segmentLotsDisable = [])
    {
        if (!$segment) {
            return null;
        }

        $data = \Yii::$app->cache->get('promo-properties_' . $segment->id);

        $query = \common\models\ProductParam::getProductsFiltersQuery();

//        if($data === false) {

        $query->andWhere(['IN', 'lot.id', $products]);

        if ($descendantsIds) {
            $query->andWhere(['IN', 'lot.tree_id', $descendantsIds]);
        }
        if ($disableProducts) {
            $query->andWhere(['NOT IN', 'lot.id', $disableProducts]);
        }

        if ($segment->sale_from) {
            $query->andWhere(['>=', 'card.new_discount_percent', $segment->sale_from]);
        }

        if ($segment->sale_to) {
            $query->andWhere(['<=', 'card.new_discount_percent', $segment->sale_to]);
        }

        if ($segment->price_from) {
            $query->andWhere(['>=', 'card.new_price', $segment->price_from]);
        }

        if ($segment->price_to) {
            $query->andWhere(['<=', 'card.new_price', $segment->price_to]);
        }

        if (count($segmentCardsDisable)) {
            $query->andWhere(['NOT IN', 'card.id', $segmentCardsDisable]);
        }

        if (count($segmentLotsDisable)) {
            $query->andWhere(['NOT IN', 'lot.id', $segmentLotsDisable]);
        }

        $addParams = Segment::addAdditionalFilterParam($segment);

        if ($addParams) {
            $query->leftJoin(ProductParamProduct::tableName() . ' AS product_param_product_default',
                'product_param_product_default.card_id = card.id');
            $query->andWhere(['IN', 'product_param_product_default.product_param_id', $addParams]);
        }

        $data = $query->asArray()->all();

        \Yii::$app->cache->set('promo-properties_' . $segment->id, $data, PageCache::CACHE_DURATION);

//        }

        return $data;
    }

    public static function removeProductParam($product, $param)
    {
        $paramProduct = ProductParamProduct::find()
            ->andWhere(['product_id' => $product->id])
            ->andWhere(['product_param_id' => $param->id])
            ->one();
        if ($paramProduct) {
            $paramProduct->delete();
        }
    }

    public static function addProductParamModCard($productId, $cardId, $lotId, $paramId)
    {

        $paramProduct = ProductParamProduct::find()
            ->andWhere(['card_id' => $cardId])
            ->andWhere(['lot_id' => $lotId])
            ->andWhere(['product_param_id' => $paramId])
            ->one();
        if (!$paramProduct) {
            $paramProduct = new ProductParamProduct();
            $paramProduct->product_id = $productId;
            $paramProduct->card_id = $cardId;
            $paramProduct->lot_id = $lotId;
            $paramProduct->product_param_id = $paramId;
            $paramProduct->save();
        }
    }

    public static function addProductParam($productId, $cardId, $lotId, $paramId)
    {
        $paramProduct = ProductParamProduct::find()
            ->andWhere(['product_id' => $productId])
            ->andWhere(['product_param_id' => $paramId])
            ->andWhere(['card_id' => $cardId])
            ->one();

        if (!$paramProduct) {

            $paramProduct = new ProductParamProduct();
            $paramProduct->product_id = $productId;
            $paramProduct->card_id = $cardId;
            $paramProduct->lot_id = $lotId;
            $paramProduct->product_param_id = $paramId;
            $paramProduct->save();

            return true;
        }
        return false;
    }

    public static function getProperties($descendantsIds)
    {

        $key = implode('_', $descendantsIds);

        $properties = \Yii::$app->cache->get('catalog-oroperties_' . $key);
        if ($properties === false) {
            $query = \common\models\ProductParam::getProductsFiltersQuery();
            if (count($descendantsIds)) {
                $query->andWhere(['IN', 'lot.tree_id', $descendantsIds]);
            }
            $properties = $query->asArray()->all();

            \Yii::$app->cache->set('catalog-oroperties_' . $key, $properties, PageCache::CACHE_DURATION);
        }

        return $properties;
    }



// TODO: упростить логику получения списка групп фильтров для раздела

    /**
     * Получить данные для свойств в фильтре
     * @return array|\yii\db\ActiveRecord[]
     */
    protected function categoryPropertiesData($treeId)
    {
        /**
         * @var $queryCategoryProperty |\yii\db\ActiveQuery|ShopContentElement|CmsContentElement
         */
        $queryCategoryProperty = ShopContentElement::find()->select([
            'cms_content_element.id',
            'property_id',
            'MIN(ss_shop_product_prices.price) AS price_from',
            'MAX(ss_shop_product_prices.price) AS price_to',
            'COUNT(DISTINCT cms_content_element_property.value) AS count_values',
        ]);

//        $queryCategoryProperty->andWhere($activeDataProvider->query->where);
//        $queryCategoryProperty->addParams($activeDataProvider->query->params);


        $queryCategoryProperty->active();
        $queryCategoryProperty->innerJoinWith(['cmsContentElementProperties']);

        if ($treeId) {
            $descendantsIds = TreeList::getDescendantsById($treeId);
            $queryCategoryProperty->andWhere(['cms_content_element.tree_id' => $descendantsIds]);
        }
//        elseif ($this->searchModel->productsIds) {
//            $queryCategoryProperty->andWhere(['cms_content_element.id' => $this->searchModel->productsIds]);
//        }
        $filteredProperties = ["218", "262", "266", "267"];

        $queryCategoryProperty->andWhere(['cms_content_element_property.property_id' => $filteredProperties]);

        // по quantity искать обязательно, чтобы разделять свойства сайтов SS и SHIK
        // $queryCategoryProperty->andWhere('quantity >= 1');
        // $queryCategoryProperty->andWhere(['not', ['ss_shop_product_prices.price' => null]]);
        // $queryCategoryProperty->andWhere(['>', 'ss_shop_product_prices.price', 2]);
        ShopContentElement::catalogFilterQuery($queryCategoryProperty, false);

        $queryCategoryProperty->andWhere('cms_content_element.active= "Y") AND (cms_content_element.content_id IN (2,5,10)');
        $queryCategoryProperty->andWhere('cms_content_element_property.value IS NOT NULL AND cms_content_element_property.value > 0');
        $queryCategoryProperty->groupBy('cms_content_element_property.property_id');
        $queryCategoryProperty->limit(10);
        $queryCategoryProperty->having('count_values > 1');

//        $queryCategoryProperty->andWhere(['not', ['cms_content_element.tree_id' => null]]);
//        $queryCategoryProperty->andWhere(['not', ['cms_content_element.image_id' => null]]);


//        $query->andWhere('ss_shop_product_prices.price > 2');
//        $query->andWhere(['not', ['ss_shop_product_prices.price' => null]]);
//        $query->andWhere(['>=', 'shop_product.quantity', 1]);


        $properties = $queryCategoryProperty->indexBy('property_id')->orderBy('')->asArray()->all();

        return $properties;
    }

    public static function sortFilterValues($filters)
    {
        if (!is_array($filters) || !count($filters))
            return array();

        usort($filters, function ($a, $b) {
            if ($a['name'] == $b['name']) {
                return 0;
            }
            return ($a['name'] < $b['name']) ? -1 : 1;
        });

        return $filters;
    }


    public function getPropertyValues($propertyId, $treeId)
    {

        $descendantsIds = TreeList::getDescendantsById($treeId);
        $descendantsIds[] = $treeId;

        $sizeProps = Size::getSizeProperties();
        $sizePropsIds = [];
        foreach ($sizeProps as $prop) {
            $sizePropsIds[] = $prop['id'];
        }

        //Размеры
        if (in_array($propertyId, $sizePropsIds)) {

            $destSizes = [];

            $sql = "select ccep.value,
               ccep.element_id,
               cce_modification.content_id,
               cce_modification.tree_id,
               cce_property.name
            from cms_content_element_property ccep
            left join cms_content_element cce_modification on ccep.element_id = cce_modification.id
//            left join shop_product  on shop_product.id = cce_modification.id
            left join cms_content_element cce_property on ccep.value = cce_property.id
            left join cms_content_element cce_card on cce_modification.parent_content_element_id = cce_card.id
            left join cms_content_element cce_lot on cce_card.parent_content_element_id = cce_lot.id
            left join ss_shop_product_prices on ss_shop_product_prices.product_id = cce_modification.id
            where property_id = :property_id
                        and cce_lot.tree_id in (" . implode(',', $descendantsIds) . ")
                        and new_quantity > 0
                        and ss_shop_product_prices.min_price > 2
                        and ccep.value != ''
            group by cce_property.id";

            $propertyValues = \Yii::$app->db->createCommand($sql, [
                ':property_id' => $propertyId,
//                ':tree_id' => $descendantsIds
            ])->queryAll();

            foreach ($propertyValues as $value) {
                $destSize = Size::getDestSizeBySource((int)$value['value']);

                if ($destSize) {

                    $destSizes[$destSize['related_content_element_id']] = [
                        'name' => $destSize['name'],
                        'value' => $destSize['related_content_element_id'],
                        'checked' => false
                    ];
                }
            }
            if (count($destSizes)) {
                $propertyValues = self::sortFilterValues($destSizes);
            } else {
//                $propertyValues = [];
            }
        }

        //Цвет
        if ($propertyId == 231) {

            $sql = "SELECT cms_content_element_property.value, cce_property.id, cce_property.name,cce_property.code
            FROM cms_content_element_property
            left join cms_content_element cce_property on cms_content_element_property.value = cce_property.id
            left join cms_content_element cce on cms_content_element_property.element_id = cce.id
            left join cms_content_element cce_lot on cce.parent_content_element_id = cce_lot.id
            
            left join ss_shop_product_prices sspp on `cce_lot`.`id` = `sspp`.`product_id`
//            left join shop_product on `cce_lot`.`id` = `shop_product`.`id`
            
          
            WHERE cms_content_element_property.property_id = {$propertyId}
                        and cce_lot.active = 'Y'
                        and cce.active = 'Y'
                        and cce_lot.tree_id  in (" . implode(',', $descendantsIds) . ")
                        and cce_lot.active = 'Y'
                        and new_quantity > 0
                        and sspp.min_price > 2
            group by cce_property.id";


            $propertyValues = \Yii::$app->db->createCommand($sql, [
                ':property_id' => $propertyId,
//                ':tree_id' => $descendantsIds
            ])->queryAll();


            $enabledColors = [];
            foreach ($propertyValues as $propValue) {
                $enabledColors[] = $propValue['value'];
            }
            $return = [];
            $filterColors = Color::getFilterColors();

            if (count($filterColors)) {
                foreach ($filterColors as $idColor => $nameColor) {
                    $colors = Color::getColorsByFilterColorId($idColor);
                    $result = !empty(array_intersect($colors, $enabledColors));
                    if ($result) {
                        $return[] = [
                            'name' => $nameColor,
                            'value' => $idColor,
                            'checked' => false
                        ];

                    }
                }
                $propertyValues = self::sortFilterValues($return);
            }

        }


        return $propertyValues;

    }

    /**
     * Инициализация фильтрующих свойство для конкретной категории
     * @param ActiveDataProvider $activeDataProvider
     */
    public function categoryProperties($treeId)
    {
        $properties = [];

        if ($treeId) {

            $keyCache = sprintf('category_properties_filter_id_%s_%s', $treeId, __FUNCTION__);
            $cache = \Yii::$app->cache;
            //$cache->delete($keyCache);

            $categoryPropertiesIds = $cache->get($keyCache);

            if ($categoryPropertiesIds === false) {

                $properties = $this->categoryPropertiesData($treeId);
                $properties = ($properties) ? array_keys($properties) : null;
                $cache->set($keyCache, $properties, HOUR_8 /*, $dependency*/);

            } else {
                $properties = $categoryPropertiesIds;
            }

        } elseif ($this->searchModel->productsIds) {

            $properties = $this->categoryPropertiesData();
            $properties = ($properties) ? array_keys($properties) : null;
        }

        //id свойства Цвет из настроек cms_component_settings принимается не стабильно, хардкодим
        if (is_array($properties)) {
            $properties = array_merge($properties, [231]);
        }
        return $properties;
    }


    public static function getFilterProperties($treeId, $isRoot = false)
    {

        //Пока возвращаем только цвет и размеры
        $properties = [231];

        if (!$isRoot) {
            $enabledSizeTreeIds = [
                1626, 1649, 1980
            ];
            foreach ($enabledSizeTreeIds as $treeId) {
                $cmsTrees = CmsTree::find()->where(['pid' => $treeId])->all();
                if ($cmsTrees) {
                    foreach ($cmsTrees as $cmsTree) {
                        $enabledSizeTreeIds[] = $cmsTree->id;
                        $cmsTreesSec = CmsTree::find()->where(['pid' => $cmsTree->id])->all();
                        if ($cmsTreesSec) {
                            foreach ($cmsTreesSec as $cmsTreeSec) {
                                $enabledSizeTreeIds[] = $cmsTreeSec->id;
                            }
                        }
                    }
                }
            }

            if (in_array($treeId, $enabledSizeTreeIds)) {
                $sizeProps = Size::getSizeProperties();
                foreach ($sizeProps as $prop) {
                    $properties[] = $prop['id'];
                }
            }
        }

        if ($properties) {
            $query = CmsContentProperty::find()->active()
                ->andWhere(['IN', 'content_id', [PRODUCT_CONTENT_ID, CARD_CONTENT_ID, OFFERS_CONTENT_ID]])
                ->andWhere(['IN', 'property_type', [\skeeks\cms\relatedProperties\PropertyType::CODE_ELEMENT,
                    \skeeks\cms\relatedProperties\PropertyType::CODE_LIST
                ]])
                ->andWhere(['IN', 'id', $properties])
                ->orderBy('content_id ASC, name ASC')
                ->all();

            foreach ($query as $prop) {
                $return[] = [
                    'id' => $prop->id,
                    'name' => $prop->name,
                    'code' => $prop->code
                ];
            }
        }

        return $return;
    }

    public static function getParamsByCode($code)
    {
        $return = [];
        $data = ProductParam::find()
            ->select([
                ProductParam::tableName() . '.id',
                ProductParam::tableName() . '.name',
            ])
            ->leftJoin(ProductParamType::tableName(), ProductParamType::tableName() . '.id=' . ProductParam::tableName() . '.type_id')
            ->andWhere(['=', 'code', $code])
            ->orderBy(ProductParam::tableName() . '.name ASC')
            ->asArray()
            ->all();
        if ($data) {
            foreach ($data as $part) {
                $return[$part['id']] = $part['name'];
            }
        }

        return $return;
    }

    public static function getParamTypes()
    {
        $return = [];
        $data = ProductParamType::find()
            ->select([
                'id',
                'name'
            ])
            ->orderBy('name')
            ->asArray()
            ->all();

        if ($data) {
            foreach ($data as $part) {
                $return[$part['id']] = $part['name'];
            }
        }
        return $return;
    }


}