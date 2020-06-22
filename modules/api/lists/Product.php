<?php

namespace modules\api\lists;

use common\components\mongo\Query;
use modules\api\models\mongodb\product\Product as ProductModel;

class Product
{

    /**
     * @param $id
     * @return array|false
     */
    public static function getId($id)
    {
        return self::getByCondition('id', $id);
    }

    /**
     * @param $field
     * @param $data
     * @return array|false
     */
    public static function getByCondition($field, $data)
    {
        $query = (new Query())->from(ProductModel::collectionName())
            ->where([$field => $data]);

        return is_array($data) ? $query->all() : $query->one();
    }

}