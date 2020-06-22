<?php

namespace common\models\generated\models;


/**
 * This is the model class for table "BUF_ECommABC".
 *
 * @property integer $id ID
 * @property integer $OFFCNT_ID Offcnt  ID
 * @property string $n1 N1
 * @property string $n4 N4
 * @property string $LotName Lot Name
 * @property integer $LotOrder Lot Order
 * @property string $LotABC Lot Abc
 * @property string $LotGUID Lot Guid
 * @property resource $LotBin Lot Bin
 * @property string $LotGUIDtext Lot Guidtext
*/
class BUFECommABC extends \common\ActiveRecord
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
        return 'BUF_ECommABC';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['OFFCNT_ID', 'LotOrder'], 'integer'],
            [['n1', 'n4', 'LotGUIDtext'], 'string', 'max' => 50],
            [['LotName', 'LotGUID'], 'string', 'max' => 255],
            [['LotABC'], 'string', 'max' => 2],
            [['LotBin'], 'string', 'max' => 2000],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'OFFCNT_ID' => 'Offcnt  ID',
            'n1' => 'N1',
            'n4' => 'N4',
            'LotName' => 'Lot Name',
            'LotOrder' => 'Lot Order',
            'LotABC' => 'Lot Abc',
            'LotGUID' => 'Lot Guid',
            'LotBin' => 'Lot Bin',
            'LotGUIDtext' => 'Lot Guidtext',
            ];
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\BUFECommABCQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\BUFECommABCQuery(get_called_class());
    }
}
