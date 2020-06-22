<?php

namespace common\models;


class CmsContentProperty extends \common\models\generated\models\CmsContentProperty
{
    public static function findByCode($code){
        return self::find()
            ->byCode($code)
            ->one();
    }

    public function getPropertyValues(){
        return $this->hasOne(CmsContentElementProperty::className(), ['property_id' => 'id'])->andWhere(['element_id' => $this]);
    }
}