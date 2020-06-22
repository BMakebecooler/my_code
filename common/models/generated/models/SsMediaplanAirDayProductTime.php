<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "ss_mediaplan_air_day_product_time".
 *
 * @property integer $id ID
 * @property string $created_at Created At
 * @property integer $begin_datetime Begin Datetime
 * @property integer $end_datetime End Datetime
 * @property integer $lot_id Lot ID
 * @property integer $bitrix_section_id Bitrix Section ID
 * @property integer $section_id Section ID
 * @property string $section_name Section Name
 * @property string $type Type
 * @property integer $block_id Block ID
 * @property string $lot_guid Lot Guid
*/
class SsMediaplanAirDayProductTime extends \common\ActiveRecord
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
    public function behaviors()
    {
        return [
            'timestamp' => \yii\behaviors\TimestampBehavior::class,
        ];
    }

    /**
    * @inheritdoc
    */
    public static function tableName()
    {
        return 'ss_mediaplan_air_day_product_time';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_at'], 'safe'],
            [['begin_datetime', 'end_datetime', 'lot_id', 'section_id'], 'required'],
            [['begin_datetime', 'end_datetime', 'lot_id', 'bitrix_section_id', 'section_id', 'block_id'], 'integer'],
            [['section_name'], 'string', 'max' => 100],
            [['type'], 'string', 'max' => 50],
            [['lot_guid'], 'string', 'max' => 64],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Created At',
            'begin_datetime' => 'Begin Datetime',
            'end_datetime' => 'End Datetime',
            'lot_id' => 'Lot ID',
            'bitrix_section_id' => 'Bitrix Section ID',
            'section_id' => 'Section ID',
            'section_name' => 'Section Name',
            'type' => 'Type',
            'block_id' => 'Block ID',
            'lot_guid' => 'Lot Guid',
            ];
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\SsMediaplanAirDayProductTimeQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\SsMediaplanAirDayProductTimeQuery(get_called_class());
    }
}
