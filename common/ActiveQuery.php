<?php

namespace common;


use common\models\generated\models\CmsContentElement;
use common\models\generated\models\SsGuids;
use common\models\Product;

/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 14/03/2019
 * Time: 12:01
 */
class ActiveQuery extends \yii\db\ActiveQuery
{
    public function byGuid($guid)
    {
        return $this
            ->leftJoin(SsGuids::tableName(), SsGuids::tableName() . '.id=' . CmsContentElement::tableName() . '.guid_id')
//            ->where([SsGuids::tableName() . '.guid' => $guid]);
//            ->andWhere(['content_id' => [Product::LOT, Product::CARD, Product::MOD]]) //Метод общий, не только для товаров
            ->andWhere(['OR', ['new_guid' => $guid], ['guid' => $guid]]);
    }

    public function byOldGuid($guid)
    {
        //TODO Проверить что без указания типа контента работает нормально, т.к. метод общий, не только для товаров
        return $this
            ->leftJoin(SsGuids::tableName(), SsGuids::tableName() . '.id=' . CmsContentElement::tableName() . '.guid_id')
            ->andWhere(['content_id' => [Product::LOT, Product::CARD, Product::MOD]])
            ->andWhere([SsGuids::tableName() . '.guid' => $guid]);
//            ->andWhere(['OR', ['new_guid' => $guid], ['guid' => $guid]]);
    }

    public function byNewGuid($guid)
    {
        //TODO Проверить что без указания типа контента работает нормально, т.к. метод общий, не только для товаров
        return $this
            ->andWhere(['content_id' => [Product::LOT, Product::CARD, Product::MOD]])
            ->andWhere(['new_guid' => $guid]);
    }

}