<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "BUF_SiteTv".
 *
 * @property integer $id
 * @property string $dt
 * @property integer $hh
 * @property string $n1
 * @property string $n4
 * @property integer $LOT_ID
 * @property string $LotName
 * @property double $SumTV
 * @property double $SumSi
 * @property double $SumSiDiv
 * @property integer $F
 * @property string $LOT_CODE
 */
class BUFSiteTv extends \common\ActiveRecord
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
        return 'BUF_SiteTv';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['dt'], 'safe'],
            [['hh', 'LOT_ID', 'F'], 'integer'],
            [['SumTV', 'SumSi', 'SumSiDiv'], 'number'],
            [['n1', 'n4'], 'string', 'max' => 200],
            [['LotName'], 'string', 'max' => 1020],
            [['LOT_CODE'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'dt' => 'Dt',
            'hh' => 'Hh',
            'n1' => 'N1',
            'n4' => 'N4',
            'LOT_ID' => 'Lot  ID',
            'LotName' => 'Lot Name',
            'SumTV' => 'Sum Tv',
            'SumSi' => 'Sum Si',
            'SumSiDiv' => 'Sum Si Div',
            'F' => 'F',
            'LOT_CODE' => 'Lot  Code',
        ];
    }

    /**
     * @inheritdoc
     * @return \common\models\query\BUFSiteTvQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\BUFSiteTvQuery(get_called_class());
    }
}
