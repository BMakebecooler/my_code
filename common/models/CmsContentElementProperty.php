<?php

namespace common\models;

use common\models\query\CmsContentElementPropertyQuery;

class CmsContentElementProperty extends \common\models\generated\models\CmsContentElementProperty
{
    const TECH_DETAILS_PROPERTY_ID = 107;

    public static function find()
    {
        return new CmsContentElementPropertyQuery(get_called_class());
    }
}