<?php
/**
 * Created by PhpStorm.
 * User: andrei
 * Date: 2019-03-30
 * Time: 11:29
 */

namespace common\models;


use common\models\query\CmsTreePropertyQuery;

class CmsTreeProperty extends generated\models\CmsTreeProperty
{
    /**
     * @inheritdoc
     * @return CmsTreePropertyQuery the active query used by this AR class.
     */
    public static function find ()
    {
        return new CmsTreePropertyQuery(get_called_class());
    }
}