<?php

namespace common\components;

use skeeks\cms\models\CmsContent;
use skeeks\cms\models\CmsContentElement;
use skeeks\cms\modules\admin\widgets\form\ActiveFormUseTab;
use skeeks\cms\relatedProperties\propertyTypes\PropertyTypeElement;
use skeeks\cms\relatedProperties\propertyTypes\PropertyTypeList;
use skeeks\cms\shop\models\ShopCmsContentElement;
use skeeks\cms\shop\models\ShopTypePrice;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

/**
 * Class SavedFiltersHandler
 *
 * @package common\components
 */
class SavedFiltersHandler extends \skeeks\cms\savedFilters\SavedFiltersHandler
{
    public $content_id;
    public $filters = [];
    public $tree_id = null;
    public $price_from = null;
    public $age_from = null;
    public $age_to = null;
    public $price_to = null;
    public $type_price_id = null;
    public $meta_second_title = null;
    public $meta_second_description = null;
    public $sort = 'popular';
    public $visibleFilters = [];
    public $q = null;

    public function init()
    {
        parent::init();

        $this->name = 'Товары';
    }

    public function rules()
    {
        return [
            ['content_id', 'required'],
            ['filters', 'safe'],
            ['price_from', 'integer'],
            ['price_to', 'integer'],
            ['type_price_id', 'integer'],
            ['age_from', 'number'],
            ['age_to', 'number'],
            ['tree_id', 'integer'],
            ['q', 'string'],
            ['meta_second_title', 'string'],
            ['meta_second_description', 'string'],
            ['sort', 'string'],
            ['visibleFilters', 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'content_id' => 'Контент',
            'filters' => 'Фильтры',
            'price_from' => 'Цена от',
            'price_to' => 'Цена до',
            'type_price_id' => 'Тип цены',
            'age_from' => 'Возраст от',
            'age_to' => 'Возраст до',
            'q' => 'Поисковый запрос',
            'meta_second_title' => 'Meta Title [вторых страниц]',
            'meta_second_description' => 'Meta Description [вторых страниц]',
            'sort' => 'Сортировка',
            'visibleFilters' => 'Отображаемые фильтры',
            'tree_id' => 'Ид раздела (цифра в квадратных скобочках[] на странице разделов)',
        ];
    }

    public function load($data, $formName = null)
    {
        if (isset($data['RelatedPropertiesModel'])) {
            $this->filters = $data['RelatedPropertiesModel'];
        }

        return parent::load($data, $formName);
    }

    /**
     * @param ActiveFormUseTab $form
     */
    public function renderConfigForm(ActiveForm $form)
    {
        echo $form->field($this, 'content_id')->listBox(
            array_merge(['' => ' - '], CmsContent::getDataForSelect()), [
            'size' => 1,
            'data-form-reload' => 'true'
        ]);

        if ($this->content_id) {
            $shopCmsContentElement = new ShopCmsContentElement();
            $shopCmsContentElement->content_id = $this->content_id;
            $rpm = $shopCmsContentElement->relatedPropertiesModel;
            $rpm->setAttributes($this->filters);

            echo $form->field($this, 'tree_id');

            echo $form->field($this, 'type_price_id')->listBox(ArrayHelper::merge([null => '-'],
                ArrayHelper::map(ShopTypePrice::find()->all(), 'id', 'name')
            ), ['size' => 1, 'multiple' => false]);

            $price_from = $form->field($this, 'price_from');
            $price_to = $form->field($this, 'price_to');
            $age_from = $form->field($this, 'age_from');
            $age_to = $form->field($this, 'age_to');
            echo <<<HTML
<div class="row">
    <div class="col-md-6">
        {$price_from}
    </div>
    <div class="col-md-6">
        {$price_to}
    </div>
</div>

HTML;
            <<<HTML
<div class="row">
    <div class="col-md-6">
        {$age_from}
    </div>
    <div class="col-md-6">
        {$age_to}
    </div>
</div>

HTML;

            echo $form->field($this, 'q');
            echo $form->field($this, 'sort')->listBox([
                '-popular' => 'Сначала популярные',
                'price' => 'Сначала дешевые',
                '-price' => 'Сначала дорогие',
            ], ['size' => 1]);


            if ($rpm->toArray()) {
                foreach ($rpm->toArray() as $code => $value) {

                    //Атрибуты которые не будут участвовать в формировании СЗ
                    if (in_array($code, [
                        'similarProducts',
                        'IS_MAINPAGE',
                        'IS_FREE_DELIVERY',
                        'FRAGILE',
                        'SEARCH_SIZE_CLOTHES',
                        'PLUS_BUY',
                        'PRICE_ACTIVE',
                        'BRAND',
                    ])) {
                        continue;
                    }

                    $property = $rpm->getRelatedProperty($code);
                    $handler = $property->handler;
                    if ($handler instanceof PropertyTypeList) {
                        echo $form->field($rpm, $code)->checkboxList(
                            ArrayHelper::map($property->enums, 'id', 'value'), [
                        ]);
                    }

                    if ($handler instanceof PropertyTypeElement) {
                        echo $form->field($rpm, $code)->checkboxList(
                            ArrayHelper::map(CmsContentElement::find()->where(['content_id' => $handler->content_id])->limit(10)->all(), 'id', 'name'), [
                        ]);
                    }
                }
            }

//            echo $form->fieldSelectMulti($this, 'visibleFilters', $rpm->attributeLabels());

            echo "<hr />";
            echo $form->field($this, 'meta_second_title');
            echo $form->field($this, 'meta_second_description');
        }
    }
}