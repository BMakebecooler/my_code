<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "BUF_ECOMM_PRICE_TYPE".
 *
 * @property string $dt
 * @property string $LotCode
 * @property integer $PRICE_TYPE_ID
 */
class BUFEcommPriceType extends \common\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'BUF_ECOMM_PRICE_TYPE';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['dt'], 'safe'],
            [['PRICE_TYPE_ID'], 'integer'],
            [['LotCode'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'dt' => 'Dt',
            'LotCode' => 'Lot Code',
            'PRICE_TYPE_ID' => 'Price  Type  ID',
        ];
    }

    /**
     * @inheritdoc
     * @return \common\models\generated\query\BUFEcommPriceTypeQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\generated\query\BUFEcommPriceTypeQuery(get_called_class());
    }
}
