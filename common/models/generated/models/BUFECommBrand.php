<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "BUF_ECommBrand".
 *
 * @property integer $id
 * @property string $n1
 * @property string $n4
 * @property string $BRAND_NAME
 * @property integer $Rub
 * @property string $g1
 * @property string $g4
 * @property string $b
 */
class BUFECommBrand extends \common\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'BUF_ECommBrand';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['Rub'], 'integer'],
            [['n1', 'n4'], 'string', 'max' => 255],
            [['BRAND_NAME', 'g1', 'g4', 'b'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'n1' => 'N1',
            'n4' => 'N4',
            'BRAND_NAME' => 'Brand  Name',
            'Rub' => 'Rub',
            'g1' => 'G1',
            'g4' => 'G4',
            'b' => 'B',
        ];
    }

    /**
     * @inheritdoc
     * @return \common\models\generated\query\BUFECommBrandQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\generated\query\BUFECommBrandQuery(get_called_class());
    }
}
