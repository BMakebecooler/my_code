<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "setting".
 *
 * @property integer $id
 * @property string $free_delivery_price
 * @property string $phone_code
 * @property string $phone_number
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $robots
 * @property integer $is_999
 * @property integer $is_show_product_feedback
 * @property integer $is_online_payment_allowed
 * @property string $onair_youtube_code
 * @property string $id_lot_onair
 * @property integer $onair_product_id
 * @property integer $use_captcha
 * @property integer $use_price_prime
 * @property integer $use_filters
 * @property integer $count_brands_products
 */
class Setting extends \common\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'setting';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'is_999', 'is_show_product_feedback', 'is_online_payment_allowed', 'onair_product_id', 'use_captcha', 'use_price_prime', 'use_filters', 'count_brands_products'], 'integer'],
            [['robots'], 'string'],
            [['free_delivery_price', 'phone_code', 'phone_number', 'onair_youtube_code', 'id_lot_onair'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'free_delivery_price' => 'Free Delivery Price',
            'phone_code' => 'Phone Code',
            'phone_number' => 'Phone Number',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'robots' => 'Robots',
            'is_999' => 'Is 999',
            'is_show_product_feedback' => 'Is Show Product Feedback',
            'is_online_payment_allowed' => 'Is Online Payment Allowed',
            'onair_youtube_code' => 'Onair Youtube Code',
            'id_lot_onair' => 'Id Lot Onair',
            'onair_product_id' => 'Onair Product ID',
            'use_captcha' => 'Use Captcha',
            'use_price_prime' => 'Use Price Prime',
            'use_filters' => 'Use Filters',
            'count_brands_products' => 'Count Brands Products',
        ];
    }

    /**
     * @inheritdoc
     * @return \common\models\generated\query\SettingQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\generated\query\SettingQuery(get_called_class());
    }
}
