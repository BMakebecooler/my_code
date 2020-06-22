<?php
/**
 * Created by PhpStorm.
 * User: andrei
 * Date: 2019-03-30
 * Time: 10:56
 */

namespace common\models;

use common\models\query\CmsTreeTypeQuery;

class CmsTreeType extends generated\models\CmsTreeType
{
    /**
     * @inheritdoc
     * @return CmsTreeTypeQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new CmsTreeTypeQuery(get_called_class());
    }

}