<?php

namespace modules\shopandshow\models\common\form;

use common\helpers\ArrayHelper;
use skeeks\cms\components\Cms;
use skeeks\cms\helpers\StringHelper;
use skeeks\cms\relatedProperties\models\RelatedPropertyModel as SXRelatedPropertyModel;

/**
 * Created by PhpStorm.
 * User: ubuntu5
 * Date: 12.10.17
 * Time: 13:50
 */
abstract class RelatedPropertyModel extends SXRelatedPropertyModel
{

    public function init()
    {
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'priority', 'multiple',], 'integer'],
            [['name', 'component'], 'required'],
            [['component_settings'], 'safe'],
            [['name', 'component', 'hint'], 'string', 'max' => 255],
            //[['code'], 'string', 'max' => 64],
            [['code'], function($attribute)
            {
                if(!preg_match('/^[a-zA-Z]{1}[a-zA-Z0-9]{1,255}$/', $this->$attribute))
                    //if(!preg_match('/(^|.*\])([\w\.]+)(\[.*|$)/', $this->$attribute))
                {
                    $this->addError($attribute, \Yii::t('skeeks/cms','Use only letters of the alphabet in lower or upper case and numbers, the first character of the letter (Example {code})',['code' => 'code1']));
                }
            }],

            [['active', 'property_type', 'multiple', 'is_required'], 'string', 'max' => 1],
            ['code', 'default', 'value' => function($model, $attribute)
            {
                return "property" . StringHelper::ucfirst(md5(rand(1, 10) . time()));
            }],
            ['priority', 'default', 'value' => 500],
            [['active', ], 'default', 'value' => Cms::BOOL_Y],
            [['is_required',], 'default', 'value' => Cms::BOOL_N],
        ];
    }

}