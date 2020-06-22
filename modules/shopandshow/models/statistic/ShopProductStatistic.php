<?php

namespace modules\shopandshow\models\statistic;


use modules\shopandshow\models\shop\ShopProduct;
use Yii;

/**
 * This is the model class for table "shop_product_statistic".
 *
 * @property integer     $id
 * @property double      $k_viewed
 * @property double      $k_ordered
 * @property double      $k_margin
 * @property double      $k_pzp
 * @property double      $k_1
 * @property double      $k_2
 * @property double      $k_rnd
 * @property double      $k_rating
 * @property int         $viewed
 * @property int         $ordered
 * @property int         $margin
 * @property int         $pzp
 * @property double      $k_quantity
 *
 * @property ShopProduct $shopProduct
 */
class ShopProductStatistic extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'shop_product_statistic';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['k_ordered', 'k_viewed', 'k_margin', 'k_pzp', 'k_1', 'k_2', 'k_rnd', 'k_rating', 'viewed', 'ordered', 'margin', 'pzp', 'k_quantity'], 'number'],
            [['id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopProduct::className(), 'targetAttribute' => ['id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'k_viewed' => 'Количество просмотров за период',
            'k_ordered' => 'Count Order',
            'k_margin' => 'k_margin',
            'k_pzp' => 'Прибыль за 1 переход',
            'k_1' => 'Коэффициент 1',
            'k_2' => 'Коэффициент 2',
            'k_rnd' => 'Случайный коэффициент',
            'k_rating' => 'Коэффициент рейтинга',
            'viewed' => 'Число просмотров',
            'ordered' => 'Число заказов',
            'margin' => 'Маржа',
            'pzp' => 'Прибыль за показ',
            'k_quantity' => 'Коэффициент кол-ва модификаций'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopProduct()
    {
        return $this->hasOne(ShopProduct::className(), ['id' => 'id']);
    }
}
