<?php


namespace common\models;


use common\models\query\ShopProductStatisticQuery;
use modules\shopandshow\models\shop\ShopProduct;

class ShopProductStatistic extends \common\models\generated\models\ShopProductStatistic
{
    const MIN_ORDERED_FOR_BESTSELLER_BADGE = 20;
    /**
     * @inheritdoc
     * @return ShopProductStatisticQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ShopProductStatisticQuery(get_called_class());
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopProduct()
    {
        return $this->hasOne(ShopProduct::className(), ['id' => 'id']);
    }

}