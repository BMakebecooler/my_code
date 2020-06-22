<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "BUF_ECommFlashPrice".
 *
 * @property integer $OFFCNT_ID
 * @property integer $PRICE_TYPE_ID
 * @property integer $PRICE_LOC
 */
class BUFECommFlashPrice extends \common\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'BUF_ECommFlashPrice';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['OFFCNT_ID', 'PRICE_TYPE_ID', 'PRICE_LOC'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'OFFCNT_ID' => 'Offcnt  ID',
            'PRICE_TYPE_ID' => 'Price  Type  ID',
            'PRICE_LOC' => 'Price  Loc',
        ];
    }

    /**
     * @inheritdoc
     * @return \common\models\generated\query\BUFECommFlashPriceQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\generated\query\BUFECommFlashPriceQuery(get_called_class());
    }
}
