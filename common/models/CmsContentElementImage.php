<?php


namespace common\models;


use common\models\query\CmsContentElementImageQuery;

class CmsContentElementImage extends \common\models\generated\models\CmsContentElementImage
{
    public static function find()
    {
        return new CmsContentElementImageQuery(get_called_class());
    }
}