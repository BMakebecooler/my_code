<?php

namespace common\models\generated\models;


/**
 * This is the model class for table "BUF_ECommPairCTS".
 *
 * @property integer $id ID
 * @property string $LotCodeCTS Lot Code Cts
 * @property string $LotNameCTS Lot Name Cts
 * @property string $LotCode Lot Code
 * @property string $LotName Lot Name
 * @property integer $LotOrder Lot Order
*/
class BUFECommPairCTS extends \common\ActiveRecord
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
        return 'BUF_ECommPairCTS';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['LotOrder'], 'integer'],
            [['LotCodeCTS', 'LotCode'], 'string', 'max' => 50],
            [['LotNameCTS', 'LotName'], 'string', 'max' => 500],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'LotCodeCTS' => 'Lot Code Cts',
            'LotNameCTS' => 'Lot Name Cts',
            'LotCode' => 'Lot Code',
            'LotName' => 'Lot Name',
            'LotOrder' => 'Lot Order',
            ];
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\BUFECommPairCTSQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\BUFECommPairCTSQuery(get_called_class());
    }
}
