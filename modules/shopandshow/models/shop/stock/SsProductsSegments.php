<?php

namespace modules\shopandshow\models\shop\stock;

use Yii;
use yii\base\Event;

/**
 * This is the model class for table "ss_products_segments".
 *
 * @property integer $id
 * @property integer $product_id
 * @property integer $bitrix_id
 * @property integer $begin_datetime
 * @property integer $end_datetime
 * @property integer $file_id
 * @property string $segment
 */
class SsProductsSegments extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ss_products_segments';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_id', 'bitrix_id'], 'required'],
            [['product_id', 'type_price_id', 'begin_datetime', 'end_datetime', 'file_id'], 'integer'],
            [['product_id', 'file_id'], 'unique'],
            [['segment'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_id' => 'Product ID',
            'bitrix_id' => 'Bitrix ID',
            'segment' => 'Сегмент',
            'begin_datetime' => 'begin_datetime',
            'end_datetime' => 'end_datetime',
            'file_id' => 'file_id',
        ];
    }
}
