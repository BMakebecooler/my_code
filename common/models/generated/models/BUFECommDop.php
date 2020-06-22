<?php

namespace common\models\generated\models;


/**
 * This is the model class for table "BUF_ECommDop".
 *
 * @property integer $id ID
 * @property string $dt1 Dt1
 * @property string $dt2 Dt2
 * @property string $n1_1 N1 1
 * @property string $n4_1 N4 1
 * @property string $LotCode_1 Lot Code 1
 * @property string $LotName_1 Lot Name 1
 * @property string $n1_2 N1 2
 * @property string $n4_2 N4 2
 * @property string $LotCode_2 Lot Code 2
 * @property string $LotName_2 Lot Name 2
 * @property integer $sum_sale_loc_pos Sum Sale Loc Pos
*/
class BUFECommDop extends \common\ActiveRecord
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
        return 'BUF_ECommDop';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['dt1', 'dt2'], 'safe'],
            [['sum_sale_loc_pos'], 'integer'],
            [['n1_1', 'n4_1', 'LotCode_1', 'n1_2', 'n4_2', 'LotCode_2'], 'string', 'max' => 50],
            [['LotName_1', 'LotName_2'], 'string', 'max' => 500],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'dt1' => 'Dt1',
            'dt2' => 'Dt2',
            'n1_1' => 'N1 1',
            'n4_1' => 'N4 1',
            'LotCode_1' => 'Lot Code 1',
            'LotName_1' => 'Lot Name 1',
            'n1_2' => 'N1 2',
            'n4_2' => 'N4 2',
            'LotCode_2' => 'Lot Code 2',
            'LotName_2' => 'Lot Name 2',
            'sum_sale_loc_pos' => 'Sum Sale Loc Pos',
            ];
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\BUFECommDopQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\BUFECommDopQuery(get_called_class());
    }
}
