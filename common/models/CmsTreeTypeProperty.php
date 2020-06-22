<?php
/**
 * Created by PhpStorm.
 * User: andrei
 * Date: 2019-03-30
 * Time: 11:29
 */

namespace common\models;


use common\models\query\CmsTreeTypePropertyQuery;

class CmsTreeTypeProperty extends generated\models\CmsTreeTypeProperty
{
    const GOOGLE_CATEGORY_NAME_CODE = 'googleCategoryName';

    /**
     * @inheritdoc
     * @return CmsTreeTypePropertyQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new CmsTreeTypePropertyQuery(get_called_class());
    }

    /**
     * @param $code
     * @return int
     */
    public static function findIdByCode($code)
    {
        return (int)static::find()
            ->code($code)
            ->select(['id'])
            ->scalar();
    }


}