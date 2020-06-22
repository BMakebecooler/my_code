<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "BUF_ECommDayOnLine".
 *
 * @property integer $id ID
 * @property string $dt Dt
 * @property integer $OFFCNT_ID Offcnt  ID
*/
class BUFECommDayOnLine extends \common\ActiveRecord
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
        return 'BUF_ECommDayOnLine';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['dt'], 'safe'],
            [['OFFCNT_ID'], 'integer'],
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
            'OFFCNT_ID' => 'Offcnt  ID',
            ];
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\BUFECommDayOnLineQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\BUFECommDayOnLineQuery(get_called_class());
    }
}
