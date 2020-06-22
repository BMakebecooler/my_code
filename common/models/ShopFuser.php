<?php


namespace common\models;


use common\models\query\ShopFuserQuery;
use yii\db\Expression;

class ShopFuser extends \common\models\generated\models\ShopFuser
{
    //Кол-во секунд после последнего добавления товара в корзину, после которых корзина считается брошенной
    public static $inactiveSecAsAbondoned = YII_ENV == 'prod' ? 60 * 30 : 60 * 3;

    public static function find()
    {
        return new ShopFuserQuery(get_called_class());
    }

    public static function getAbandonedBasketsKfssQuery()
    {
        //Брошенные корзины - это фузеры с номерами заказов, так как при оформлении заказа это поле пустеет
        //Проверим лишь на активность

        return ShopFuser::find()
            ->select([
                'shop_fuser.*',
                new Expression("MAX(baskets.created_at) AS last_add_product"),
                new Expression("(UNIX_TIMESTAMP() - MAX(baskets.created_at)) AS inactive_sec")
            ])
            ->leftJoin(ShopBasket::tableName() . ' baskets', "baskets.fuser_id=shop_fuser.id")
            ->where(['!=', 'external_order_id', ''])
            ->andWhere(['>', 'shop_fuser.updated_at', time() - HOUR_1]) //Брошенки старше этого перода отправлять уже не комильфо
            ->groupBy('shop_fuser.id')
            ->having(['>', 'inactive_sec', self::$inactiveSecAsAbondoned]);
    }
}