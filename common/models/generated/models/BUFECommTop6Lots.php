<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "BUF_ECommTop6Lots".
 *
 * @property integer $id ID
 * @property string $n1 N1
 * @property string $n4 N4
 * @property string $LotName Lot Name
 * @property string $LotCode Lot Code
 * @property integer $PRange Prange
 * @property integer $sum_n1n4Lot Sum N1n4 Lot
*/
class BUFECommTop6Lots extends \common\ActiveRecord
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
        return 'BUF_ECommTop6Lots';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['PRange', 'sum_n1n4Lot'], 'integer'],
            [['n1', 'n4', 'LotCode'], 'string', 'max' => 50],
            [['LotName'], 'string', 'max' => 250],
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
            'LotName' => 'Lot Name',
            'LotCode' => 'Lot Code',
            'PRange' => 'Prange',
            'sum_n1n4Lot' => 'Sum N1n4 Lot',
            ];
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\BUFECommTop6LotsQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\BUFECommTop6LotsQuery(get_called_class());
    }
}
