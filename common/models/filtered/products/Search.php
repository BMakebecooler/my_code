<?php
/**
 * Created by PhpStorm.
 * User: ubuntu5
 * Date: 09.03.17
 * Time: 13:18
 */

namespace common\models\filtered\products;

use common\helpers\ArrayHelper;
use common\helpers\Size;
use common\helpers\Color;

use common\models\cmsContent\CmsContentElement;
use common\models\cmsContent\CmsContentProperty;
use skeeks\cms\components\Cms;

use skeeks\cms\models\CmsContentElementProperty;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Query;


class Search extends Model
{

    public $image;

    public $price_from;
    public $price_to;
    public $type_price_id;
    public $isOnlyDiscountProduct;
    public $hasQuantity;

    public $is_filter = false;

    public $searchAttributes;
    public $searchValueAttributes;

    protected $sizePropIds = [];

    protected $colorPropIds = [231];

    /**
     * Ид каталога где мы сейчас находимся
     * @var
     */
    public $tree_id;

    public $productsIds;

    public function rules()
    {
        $rules = [
            [['image'], 'string'],
            [['price_from', 'price_to', 'tree_id', 'type_price_id', 'isOnlyDiscountProduct'], 'number'],
            [['hasQuantity'], 'boolean'],
            [($this->searchAttributes), 'safe'],
            [['productsIds', 'is_filter'], 'safe'],
        ];

        return $rules;
    }


    public function attributeLabels()
    {
        return [
            'image' => \skeeks\cms\shop\Module::t('app', 'With photo'),
            'price_from' => \skeeks\cms\shop\Module::t('app', 'Price from'),
            'price_to' => \skeeks\cms\shop\Module::t('app', 'Price to'),
            'type_price_id' => \skeeks\cms\shop\Module::t('app', 'Price type'),
            'hasQuantity' => \skeeks\cms\shop\Module::t('app', 'In stock'),
            'isOnlyDiscountProduct' => 'Только товары со скидкой'
        ];
    }

    public function init()
    {
        parent::init();

        $properties = CmsContentProperty::find()->active()
            ->select('id')
            ->andWhere(['IN', 'content_id', [PRODUCT_CONTENT_ID, CARD_CONTENT_ID, OFFERS_CONTENT_ID]])
            ->indexBy('id')
            ->asArray()
            ->all();

        if ($properties) {
            $properties = array_keys($properties);
            $this->searchAttributes = array_combine($properties, $properties);
        }

        $sizeProps =  Size::getSizeProperties();
        foreach ($sizeProps as $prop){
            $this->sizePropIds[] = $prop['id'];
        }

    }


    public function __set($name, $value)
    {
        if (isset($this->searchAttributes[$name])) {
            $this->searchValueAttributes[$name] = $value;
        } else {
            parent::__set($name, $value);
        }
    }

    public function __get($name)
    {
        if (isset($this->searchAttributes[$name])) {
            return isset($this->searchValueAttributes[$name]) ? $this->searchValueAttributes[$name] : '';
        }

        return parent::__get($name);
    }


    /**
     * @param $params
     * @return ActiveDataProvider
     * @throws \yii\base\InvalidConfigException
     */
    public function search(ActiveDataProvider $dataProvider)
    {
        /**
         * @var $query Query
         */
        $query = $dataProvider->query;

        $query->addSelect([
            'ss_shop_product_prices.price AS realPrice',
        ]);

        /*if ($this->image == Cms::BOOL_Y) {
            $query->andWhere([
                'or',
                ['!=', 'cms_content_element.image_id', null],
                ['!=', 'cms_content_element.image_id', ""],
            ]);
        } else if ($this->image == Cms::BOOL_N) {
            $query->andWhere([
                'or',
                ['cms_content_element.image_id' => null],
                ['cms_content_element.image_id' => ""],
            ]);
        }*/

        $filterValues = $this->searchValueAttributes ? array_filter($this->searchValueAttributes) : [];
        foreach ($filterValues as $propertyId => &$values){
            if(in_array($propertyId, $this->colorPropIds)){
                $valuesOrig = $values;
                $valuesNew = [];
                foreach ($valuesOrig as $val){
                    $data = Color::getColorsByFilterColorId($val);
                    foreach ($data as $elId){
                        $valuesNew[] = $elId;
                    }
                }
                $valuesNew = array_unique($valuesNew );
                $values = count($valuesNew) ? $valuesNew : $values;
            }else if(in_array($propertyId, $this->sizePropIds)){
                $valuesOrig = $values;
                $valuesNew = [];
                foreach ($valuesOrig as $val){
                    $data = Size::getSourceSizesByDest($val);
                    foreach ($data as $element){
                        $valuesNew[] = $element['content_element_id'];
                    }
                }
                $valuesNew = array_unique($valuesNew );
                $values = count($valuesNew) ? $valuesNew : $values;

            }
        }

        
        if ($filterValues) {

            $valuesIds = ArrayHelper::arrayFlatten($filterValues);
            $valuesIds = ArrayHelper::arrayToInt($valuesIds);

            $propertiesIds = array_keys($filterValues);

            $products = \modules\shopandshow\models\shop\ShopContentElement::find()
                ->select(['DISTINCT COALESCE(lot.id,cms_content_element.parent_content_element_id, cms_content_element.id) AS element_id'])
                ->from(['cms_content_element' => CmsContentElement::tableName()]);
            $products->innerJoin(['filter_properties' => CmsContentElementProperty::tableName()],
                "filter_properties.element_id = cms_content_element.id");
            $products->leftJoin(['card' => CmsContentElement::tableName()],'card.id = cms_content_element.parent_content_element_id');
            $products->leftJoin(['lot' => CmsContentElement::tableName()],'lot.id = card.parent_content_element_id');

            $products->andWhere(['IN', 'cms_content_element.content_id', [PRODUCT_CONTENT_ID, CARD_CONTENT_ID, OFFERS_CONTENT_ID]]);

            $products->andWhere(['IN', 'filter_properties.property_id', $propertiesIds]);
            $products->andWhere(['IN', 'filter_properties.value', $valuesIds]);

            // доп.фильтр по цене и кол-ву остатков модификаций при поиске
            \modules\shopandshow\models\shop\ShopContentElement::catalogFilterQuery($products, false);

            $query->andWhere(['IN', 'cms_content_element.id', $products]);

        }

        if ($this->price_to) {
            $query->andHaving(['<=', 'realPrice', $this->price_to]);
        }
        if ($this->price_from) {
            $query->andHaving(['>=', 'realPrice', $this->price_from]);
        }

        if ((int)$this->isOnlyDiscountProduct) {

        }

        if ($this->hasQuantity) {
            //$query->andWhere(['>', 'shop_product.quantity', 0]);
        }
//        $q = $query->createCommand()->getRawSql();
//            echo '<pre>';
//            print_r($q);
//            echo '</pre>';
//            die();



        return $dataProvider;
    }

}