<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "shop_product_statistic".
 *
 * @property integer $id ID
 * @property double $k_viewed K Viewed
 * @property double $k_ordered K Ordered
 * @property double $k_margin K Margin
 * @property double $k_pzp K Pzp
 * @property double $k_1 K 1
 * @property double $k_2 K 2
 * @property double $k_rnd K Rnd
 * @property double $k_rating K Rating
 * @property integer $viewed Viewed
 * @property integer $ordered Ordered
 * @property integer $margin Margin
 * @property integer $pzp Pzp
 * @property double $k_quantity K Quantity
 * @property double $k_stock K Stock
*/
class ShopProductStatistic extends \common\ActiveRecord
{
    private $called_class_namespace;

    public function __construct()
    {
        $this->called_class_namespace = substr(get_called_class(), 0, strrpos(get_called_class(), '\\'));
        parent::__construct();
    }

                                                            
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
            [['id'], 'required'],
            [['id', 'viewed', 'ordered', 'margin', 'pzp'], 'integer'],
            [['k_viewed', 'k_ordered', 'k_margin', 'k_pzp', 'k_1', 'k_2', 'k_rnd', 'k_rating', 'k_quantity', 'k_stock'], 'number'],
            [['id'], 'unique'],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'k_viewed' => 'K Viewed',
            'k_ordered' => 'K Ordered',
            'k_margin' => 'K Margin',
            'k_pzp' => 'K Pzp',
            'k_1' => 'K 1',
            'k_2' => 'K 2',
            'k_rnd' => 'K Rnd',
            'k_rating' => 'K Rating',
            'viewed' => 'Viewed',
            'ordered' => 'Ordered',
            'margin' => 'Margin',
            'pzp' => 'Pzp',
            'k_quantity' => 'K Quantity',
            'k_stock' => 'K Stock',
            ];
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\ShopProductStatisticQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\ShopProductStatisticQuery(get_called_class());
    }
}
