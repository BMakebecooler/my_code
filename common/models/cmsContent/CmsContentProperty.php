<?php

namespace common\models\cmsContent;

use skeeks\cms\components\Cms;
use skeeks\cms\helpers\StringHelper;
use skeeks\cms\models\CmsContentProperty as SXCmsContentProperty;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%cms_content_property}}".
 *
 * @property integer $vendor_id
 * @property string $filter_name
 * @property string $item_name
 * @property string $widget_name
 * @property string $is_admin_show
 */
class CmsContentProperty extends SXCmsContentProperty
{

    /**
     * @inheritdoc
     */
    public function rules()
    {
        //@TODO - Это ужас но надо найти красивый способ переопределять правила в наследниках модели

        /**
         * Этот весь беспредел из за РЕГУЛЯРНОГО Выражения для проверки кода
         */

        return [
            [['vendor_id'], 'integer'],
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'id'], 'integer'],

            [['content_id'], 'integer'],
            [['code', 'content_id'], 'unique', 'targetAttribute' => ['content_id', 'code'], 'message' => \Yii::t('skeeks/cms', 'For the content of this code ({value}) is already in use.')],

            [['created_by', 'updated_by', 'created_at', 'updated_at', 'priority', 'multiple_cnt', 'version'], 'integer'],
            [['name', 'component'], 'required'],
            [['component_settings'], 'safe'],
            [['name', 'component', 'hint'], 'string', 'max' => 255],
            [['filter_name', 'widget_name', 'item_name'], 'string', 'max' => 255],
            //[['code'], 'string', 'max' => 64],
            [['code'], function ($attribute) {
                if (!preg_match('/^[a-zA-Z]{1}[a-zA-Z0-9_\-]{1,255}$/', $this->$attribute)) //if(!preg_match('/(^|.*\])([\w\.]+)(\[.*|$)/', $this->$attribute))
                {
                    $this->addError($attribute, \Yii::t('skeeks/cms', 'Use only letters of the alphabet in lower or upper case and numbers, the first character of the letter (Example {code})', ['code' => 'code1']));
                }
            }],

//            ['code', 'match', 'pattern' => '/^[a-zA-Z]{1}[a-zA-Z0-9]{1,255}$/', 'message' => 'Your username can only contain alphanumeric characters, underscores and dashes.'],


            [['is_admin_show', 'active', 'property_type', 'list_type', 'multiple', 'with_description', 'searchable', 'filtrable', 'is_required', 'smart_filtrable'], 'string', 'max' => 1],

            ['code', 'default', 'value' => function ($model, $attribute) {
                return "property" . StringHelper::ucfirst(md5(rand(1, 10) . time()));
            }],
            ['priority', 'default', 'value' => 500],
            [['active', 'searchable'], 'default', 'value' => Cms::BOOL_Y],
            [['is_required', 'smart_filtrable', 'filtrable', 'with_description'], 'default', 'value' => Cms::BOOL_N],
        ];
    }


    public function init()
    {
        parent::init();
    }

    /**
     * Переопределяем событие из скикса для фикса property_type
     * @param $e
     */
    public function _processBeforeSave($e)
    {
        if ($handler = $this->handler) {
            $this->property_type = ($this->property_type) ?: $handler->code;
            $this->multiple = $handler->isMultiple ? Cms::BOOL_Y : Cms::BOOL_N;
        }
    }

    /**
     * Название параметра для использования в ФИЛЬТРЕ
     * @return string
     */
    public function getFilterName()
    {
        return ($this->filter_name) ?: $this->name;
    }

    /**
     * Название параметра для использования на странице каталога в модификациях в "всплывающем виджете"
     * @return string
     */
    public function getWidgetName()
    {
        return ($this->widget_name) ?: $this->name;
    }

    /**
     * Название параметра для использования в карточке товара
     * @return string
     */
    public function getItemName()
    {
        if ($name = \common\helpers\Property::getPropertyProperName($this->code)){
            return $name;
        }else{
            return ($this->item_name) ?: $this->name;
        }
    }

    /**
     *
     * @return bool
     */
    public function isSizes()
    {
        return in_array($this->code, [
            'SIZE_BRASSIERE',
            'SIZE_PANTS',
            'SIZE_SHOES',
            'SIZE_CLOTHES',
            'SIZE_RINGS',
            'SIZE_CLOTHES_LETTER',
            'SIZE_BED_LINEN',
            'SIZE_PILLOWS',
            'SIZE_LINENS',
            'SHOES_SIZE_MODA',
            'SIZE_CLOTHING',
            'SIZE_WEIT',
        ]);
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'is_admin_show' => 'Показывать в админке',
        ]);
    }

}