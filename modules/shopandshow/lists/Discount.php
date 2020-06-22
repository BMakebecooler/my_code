<?php
/**
 * Created by PhpStorm.
 * User: ubuntu5
 * Date: 27.04.17
 * Time: 12:56
 */

namespace modules\shopandshow\lists;

use modules\shopandshow\models\shares\SsShare;
use modules\shopandshow\models\shop\ShopDiscount;
use skeeks\cms\components\Cms;

class Discount
{

    /**
     * @param $code
     * @return ShopDiscount
     */
    public static function getByCode($code)
    {
        return ShopDiscount::findOne(['code' => $code]);
    }

    /**
     * @param $productId
     * @return array|false
     */
    public static function getPromocodeProduct($productId)
    {

        $sql = <<<SQL
SELECT sd_coupon.coupon AS coupon, CAST(sd.value AS UNSIGNED) AS discount, sd_coupon.active_to
FROM ss_shop_discount_values AS val
INNER JOIN ss_shop_discount_configuration AS conf ON conf.id = val.shop_discount_configuration_id
INNER JOIN ss_shop_discount_entity AS entity ON entity.id = conf.shop_discount_entity_id
INNER JOIN shop_discount AS sd ON sd.id = conf.shop_discount_id
INNER JOIN shop_discount_coupon AS sd_coupon ON sd.id = sd_coupon.shop_discount_id
WHERE entity.class = 'ForLots' AND val.value = :product_id AND sd_coupon.is_active = 1 AND sd_coupon.coupon IS NOT NULL 
  AND (sd_coupon.active_from <= UNIX_TIMESTAMP() AND sd_coupon.active_to >= UNIX_TIMESTAMP())
LIMIT 1
SQL;

        return \Yii::$app->db->createCommand($sql, [
            ':product_id' => $productId
        ])->queryOne();
    }

}