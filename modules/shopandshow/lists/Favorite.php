<?php
/**
 * Created by PhpStorm.
 * User: ubuntu5
 * Date: 07.06.17
 * Time: 15:24
 */

namespace modules\shopandshow\lists;


use modules\shopandshow\models\shop\ShopContentElement;
use yii\db\ActiveQuery;

class Favorite
{

    /**
     * @return ActiveQuery
     */
    public static function getMyFavoritesFind()
    {
        return ShopContentElement::find()->innerJoinWith(['favorite']);
    }

    public static function getMyFavorites()
    {
        return (self::getMyFavoritesFind())->all();
    }

}