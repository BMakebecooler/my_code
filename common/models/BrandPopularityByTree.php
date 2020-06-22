<?php


namespace common\models;


use common\models\query\BrandPopularityByTreeQuery;

class BrandPopularityByTree extends \common\models\generated\models\BrandPopularityByTree
{
    public static function find()
    {
        return new BrandPopularityByTreeQuery(get_called_class());
    }
}