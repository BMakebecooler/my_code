<?php

namespace common\widgets\filters;

use common\helpers\Size;
use common\helpers\Color;
use common\lists\TreeList;
use common\models\cmsContent\CmsContentElement;
use common\models\cmsContent\CmsContentProperty;
use common\models\filtered\products\Catalog;
use common\models\filtered\products\Search;
use common\widgets\content\ContentElementWidget;
use modules\shopandshow\models\shop\ShopContentElement;
use modules\shopandshow\models\shop\ShopProduct;
use skeeks\cms\base\Widget;
use skeeks\cms\models\CmsContentElementProperty;
use skeeks\cms\relatedProperties\PropertyType;
use skeeks\cms\shop\cmsWidgets\filters\ShopProductFiltersWidget;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use yii\caching\TagDependency;

class ProductFiltersWidget extends ShopProductFiltersWidget
{

//    public $additinalModel;

    const COLOR_CODES = [
//        'shopandshow' => ['COLOR_SEARCH'],
        'shopandshow' => ['KFSS_COLOR'],
        'shik' => ['KFSS_COLOR_BX']
    ];

    public $filteredProperties;

    /**
     * @var Search
     */
    public $searchModel;

    /**
     * @var Catalog
     */
    public $filters;

    /**
     * @var ContentElementWidget
     */
    public $contentElementWidget;

    protected $descendantsIds;

    //Берутся (в том числе) из настроек виджета/компонета.
    //Хрянятся в БД в component_settings с разными неймспейсами, например ShopProductFiltersWidget-catalog-v2-<TREE_ID>
    protected $categoryProperties = [];
    protected $additionalOfferAttributes = [
//        'COLOR_SEARCH',
        'KFSS_COLOR',
    ];

    protected $sizeCodes = [];

    protected $colorCodes = ['KFSS_COLOR'];


    public function init()
    {
        Widget::init();

        if (!$this->searchModel) {
            $this->searchModel = new Search();
        }

        if (\Yii::$app instanceof \yii\web\Application) {
            $this->searchModel->load(\Yii::$app->request->get());
        }
        $sizeProps =  Size::getSizeProperties();
        foreach ($sizeProps as $prop){
            $this->sizeCodes[] = $prop['code'];
        }
    }

    public function search(ActiveDataProvider $activeDataProvider)
    {
        /**
         * @var $query ActiveQuery
         */
        $query = $activeDataProvider->query;
        //print_r($query->createCommand()->rawSql);die;

        if ($this->onlyExistsFilters) {
            /**
             * @var $query \yii\db\ActiveQuery
             */
//            $query = clone $activeDataProvider->query;

            //TODO::notice errors
            //$ids = $query->select(['*', 'cms_content_element.id as mainId'])->indexBy('mainId')->asArray()->all();

//            var_dump($query->prepare(Yii::$app->db->queryBuilder)->createCommand()->rawSql);

            //$this->elementIds = array_keys($ids);
        }

        /*if ($this->contentElementWidget) {
            $data = $this->contentElementWidget->dataProvider->query->indexBy('id')->all();
            $this->searchModel->productsIds = ($data) ? array_keys($data) : [];
        }*/

        if ($this->isFiltered()) {
            $this->searchModel->search($activeDataProvider);
        }

        if ($this->filteredProperties) {
            $this->categoryProperties($activeDataProvider);
        }

//        print_r($queryCategoryProperty->createCommand()->rawSql);
    }


    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(),
            [
                'filteredProperties' => 'Фильтрующие свойства',
            ]);
    }

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(),
            [
                [['filteredProperties'], 'safe'],
            ]);
    }

    /**
     * Получить данные для свойств в фильтре
     * @return array|\yii\db\ActiveRecord[]
     */
    protected function categoryPropertiesData()
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

        if ($this->searchModel->tree_id) {
            $this->descendantsIds = TreeList::getDescendantsById($this->searchModel->tree_id);
            $queryCategoryProperty->andWhere(['cms_content_element.tree_id' => $this->descendantsIds]);
        } elseif ($this->searchModel->productsIds) {
            $queryCategoryProperty->andWhere(['cms_content_element.id' => $this->searchModel->productsIds]);
        }

        $queryCategoryProperty->andWhere(['cms_content_element_property.property_id' => $this->filteredProperties]);

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

    /**
     * Инициализация фильтрующих свойство для конкретной категории
     * @param ActiveDataProvider $activeDataProvider
     */
    public function categoryProperties(ActiveDataProvider $activeDataProvider)
    {
        $properties = [];

        if ($this->searchModel->tree_id || $this->filters) {

            $categoryFilterId = $this->searchModel->tree_id ?: $this->filters->className();

            $keyCache = sprintf('category_properties_filter_id_%s_%s', $categoryFilterId, __FUNCTION__);
            $cache = \Yii::$app->cache;
            //$cache->delete($keyCache);

            $categoryPropertiesIds = $cache->get($keyCache);

            if ($categoryPropertiesIds === false) {

                $properties = $this->categoryPropertiesData();

                $properties = ($properties) ? array_keys($properties) : null;

                /*$dependency = new TagDependency([
                    'tags' =>
                        [
                            $this->className() . (string)$this->namespace,
                            (new ShopContentElement())->getTableCacheTag(),
                            (new CmsContentElementProperty())->getTableCacheTag(),
                        ],
                ]);*/

                $cache->set($keyCache, $properties, HOUR_8 /*, $dependency*/);

            } else {
                $properties = $categoryPropertiesIds;
            }

        } elseif ($this->searchModel->productsIds) {

            $properties = $this->categoryPropertiesData();

            $properties = ($properties) ? array_keys($properties) : null;
        }


        /*        if ($properties) {

                    $prices = ($properties) ? reset($properties) : [];

                    $this->searchModel->price_from = isset($prices['price_from']) ? $prices['price_from'] : null;
                    $this->searchModel->price_to = isset($prices['price_to']) ? $prices['price_to'] : null;
                }*/

        //id свойства Цвет из настроек cms_component_settings принимается не стабильно, хардкодим
        if (is_array($properties)) {
            $properties = array_merge($properties, [231]);
        }

        $this->categoryProperties = $properties;
        echo '<pre>';

    }


    protected function getPropertyOptionsData(CmsContentProperty $property)
    {
        if ($property->property_type && in_array($property->property_type, [
                PropertyType::CODE_ELEMENT,
                PropertyType::CODE_LIST
            ])
        ) {
            $products = ShopContentElement::find()->select(['cms_content_element.id'])->andWhere(['cms_content_element.active' => 'Y']);

            if ($this->searchModel->tree_id) {
                $this->descendantsIds = ($this->descendantsIds) ?: TreeList::getDescendantsById($this->searchModel->tree_id);
                if ($property->content_id == CARD_CONTENT_ID) {
                    //* Для карточек по разделу искать не получается, у них у всех трииИд = каталог (9) *//
                    $products->leftJoin(
                        CmsContentElement::tableName() . ' AS parent_element',
                        "cms_content_element.parent_content_element_id = parent_element.id AND parent_element.active='Y'"
                    );
                    $products->andWhere(['cms_content_element.content_id' => CARD_CONTENT_ID]);
                    $products->andWhere(['parent_element.tree_id' => $this->descendantsIds]);
                } else {
                    $products->andWhere(['not', ['cms_content_element.content_id' => CARD_CONTENT_ID]]);
                    $products->andWhere(['cms_content_element.tree_id' => $this->descendantsIds]);
                }
            } elseif ($this->searchModel->productsIds) {
                $products->andWhere(['cms_content_element.id' => $this->searchModel->productsIds]);
            }

//            $products->andWhere(['not', ['cms_content_element.tree_id' => null]]);
//            $products->andWhere(['not', ['cms_content_element.image_id' => null]]);


//            $products->andWhere(['not', ['parent_element.image_id' => null]]);
//            $products->andWhere(['not', ['parent_element.tree_id' => null]]);

            // по quantity искать обязательно, чтобы разделять свойства сайтов SS и SHIK
            // $products->andWhere(['>=', 'quantity', 1]);
            // $products->andWhere(['not', ['ss_shop_product_prices.price' => null]]);
            // $products->andWhere(['>', 'ss_shop_product_prices.price', 2]);

            ShopContentElement::catalogFilterQuery($products, false);

            if ($this->filters) {
                $parentProducts = clone $products;
                $dataProvider = new ActiveDataProvider(['query' => $parentProducts]);

                $filters = clone $this->filters;
                $filters->sort = null;
                $filters->search($dataProvider);

                $parentProducts->orderBy = null;
                $parentProducts->limit = null;

                if ($property->content_id == OFFERS_CONTENT_ID) {
                    $products->andWhere(['cms_content_element.parent_content_element_id' => $parentProducts]);
                } elseif ($property->content_id == PRODUCT_CONTENT_ID) {
                    $products = $parentProducts;
                }
            }

            $properties = CmsContentElementProperty::find()
                ->select([
                    'value',
//                    'count(element_id) AS count_product'
                ])
                ->andWhere(['element_id' => $products])
                ->andWhere('value IS NOT NULL AND value > 0')
                ->andWhere(['property_id' => $property->id])//->andWhere(['content_id' => $propertyType->content_id])
                ->groupBy('value');

            $return = CmsContentElement::find()
                ->select(['properties.value',
                    'name',
//                    'properties.count_product'
                ])
                ->active()
                ->innerJoin(['properties' => $properties], 'cms_content_element.id = properties.value')
                ->limit(50)
                ->orderBy('name ASC')
                ->asArray()
                ->all();

            return $return;


        }

        return [];

    }


    /**
     * @param CmsContentProperty $property
     * @return array
     */
    public function getPropertyOptions(CmsContentProperty $property)
    {
        $propertyValues = [];

        if ($this->searchModel->tree_id || $this->filters) {

            $categoryFilterId = $this->searchModel->tree_id ?: $this->filters->className();

            $keyCache = sprintf('category_properties_values_%s_%s_%s', $categoryFilterId, $property->id, __FUNCTION__);
            $cache = \Yii::$app->cache;
//                $cache->delete($keyCache);

            $propertyValues = $cache->get($keyCache);

            if ($propertyValues === false) {

                $options = $this->getPropertyOptionsData($property);

                $propertyValues = ArrayHelper::map($options, 'value', function ($model) {
                    return $model['name'];
                    //return sprintf('%s [%d]', $model['name'], $model['count_product']);
                });

                /*$dependency = new TagDependency([
                    'tags' =>
                        [
                            $this->className() . (string)$this->namespace,
                            (new ShopContentElement())->getTableCacheTag(),
                            (new ShopProduct())->getTableCacheTag(),
                        ],
                ]);*/

                $cache->set($keyCache, $propertyValues, HOUR_4 /*, $dependency*/);
            }

        } elseif ($this->searchModel->productsIds) {

            $options = $this->getPropertyOptionsData($property);

            $propertyValues = ArrayHelper::map($options, 'value', function ($model) {
                return $model['name'];
                //return sprintf('%s [%d]', $model['name'], $model['count_product']);
            });
        }

        if(in_array($property->code,$this->colorCodes)){
            $colorsCatalog = [];
            foreach ($propertyValues as $k=>$v){
                $colorsCatalog[] = $k;
            }

            $propertyValuesColor = Color::getFilterColors();
            $propertyValuesColorAviliable = [];
            foreach ($propertyValuesColor as $key=>$value) {
                $flag = false;
                $colors = Color::getColorsByFilterColorId($key);
                foreach ($colors as $color){
                    if(in_array($color,$colorsCatalog)){
                        $flag = true;
                        break;
                    }
                }
                if($flag){
                    $propertyValuesColorAviliable[$key] = $value;
                }
            }
            $propertyValues = $propertyValuesColorAviliable;
        }elseif (in_array($property->code,$this->sizeCodes)){
            $destSizes = [];
            foreach ($propertyValues as $key=>$value){
                $destSize = Size::getDestSizeBySource($key);
                if($destSize){
                    $destSizes[$destSize['related_content_element_id']] = $destSize['name'];
                }
            }
            if(count($destSizes))
                $propertyValues = $destSizes;
        }

        return $propertyValues;
    }

    /**
     * @return array|CmsContentProperty[]
     */
    public function getCategoryProperties()
    {
        $properties = [];

        if ($this->categoryProperties) {
            $properties = CmsContentProperty::find()->andWhere(['id' => $this->categoryProperties])
                ->orderBy([new \yii\db\Expression('FIELD (cms_content_property.id, ' . join(',', $this->categoryProperties) . ')')])
                ->all();
        }

        return $properties;
    }

    /**
     * @param $property
     * @return bool
     */
    public function isCheckBoxList($property)
    {
        return !in_array($property->code, $this->additionalOfferAttributes);
    }

    /**
     * Получить количество выбранных свойств
     * @param $propertyId
     * @return int
     */
    public function countSelectProperty($propertyId)
    {
        $data = \Yii::$app->request->get('Search');
        $params = ($data !== null) ? array_filter($data) : [];

        return isset($params[$propertyId]) ? count($params[$propertyId]) : 0;
    }

    /**
     * Признак фильтрации
     * @return bool
     */
    public function isFiltered()
    {
        $data = \Yii::$app->request->get('Search');
        $params = ($data !== null) ? array_filter($data) : [];

        return ((bool)$params) || $this->searchModel->is_filter === true;
    }

    /**
     * @param $property
     * @return bool
     */
    public function isColorBox($property)
    {
        return in_array($property->code, self::COLOR_CODES[SS_SITE]);
    }

    /**
     * @param $property
     * @return bool
     */
    public function isSizeBox($property)
    {
        return preg_match('/(SIZE|LENGTH|DIAMETR|RAZMER)(_|$)/', $property->code);
    }

    /**
     * @param $property
     * @return bool
     */
    public function isShowPriceFilter()
    {
        return true;


        if ($this->searchModel->price_from == $this->searchModel->price_to) {
            return false;
        }

        return true;
    }

    public function renderConfigForm(ActiveForm $form)
    {
        echo \Yii::$app->view->renderFile(__DIR__ . '/views/_product-filter-form.php', [
            'form' => $form,
            'model' => $this
        ], $this);
    }

    /**
     * @return array|CmsContentProperty[]
     */
    public function getFilteredProperties()
    {
        $properties = CmsContentProperty::find()->active()
            ->andWhere(['IN', 'content_id', [PRODUCT_CONTENT_ID, CARD_CONTENT_ID, OFFERS_CONTENT_ID]])
            ->andWhere(['IN', 'property_type', [PropertyType::CODE_ELEMENT,
                PropertyType::CODE_LIST
            ]])
            //->orderBy('content_id ASC, name ASC')
            ->all();

        return $properties;
    }

}