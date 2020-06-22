<?php

namespace modules\shopandshow\models\common\form;


use common\helpers\ArrayHelper;
use skeeks\cms\models\behaviors\Serialize;
use skeeks\modules\cms\form2\models\Form2Form;
use skeeks\modules\cms\form2\models\Form2FormPropertyEnum;
use skeeks\modules\cms\form2\models\Form2FormSendProperty;

/**
 * Created by PhpStorm.
 * User: ubuntu5
 * Date: 12.10.17
 * Time: 13:49
 */

class Form2FormProperty extends RelatedPropertyModel
{



    public function behaviors()
    {
        return [
            Serialize::className() =>
                [
                    'class' => Serialize::className(),
                    'fields' => ['component_settings']
                ]
        ];
    }

    public function init()
    {
        parent::init();

//        $this->on(self::EVENT_BEFORE_INSERT,    [$this, "_processBeforeSave"]);
//        $this->on(self::EVENT_BEFORE_UPDATE,    [$this, "_processBeforeSave"]);
//        $this->on(self::EVENT_BEFORE_DELETE,    [$this, "_processBeforeDelete"]);
    }


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%form2_form_property}}';
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'form_id' => \Yii::t('skeeks/form2/app', 'Contact form'),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['form_id'], 'integer'],
            [['code', 'form_id'], 'unique', 'targetAttribute' => ['code', 'form_id'], 'message' => \Yii::t('skeeks/form2/app', 'User Ids')]
        ]);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getForm()
    {
        return $this->hasOne(Form2Form::className(), ['id' => 'form_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getForm2FormPropertyEnums()
    {
        return $this->hasMany(Form2FormPropertyEnum::className(), ['property_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getForm2FormSendProperties()
    {
        return $this->hasMany(Form2FormSendProperty::className(), ['property_id' => 'id']);
    }




    /**
     * @return \yii\db\ActiveQuery
     */
    public function getElementProperties()
    {
        return $this->hasMany(Form2FormSendProperty::className(), ['property_id' => 'id']);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEnums()
    {
        return $this->hasMany(Form2FormPropertyEnum::className(), ['property_id' => 'id']);
    }

}