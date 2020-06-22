<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "ss_mediaplan_air_blocks".
 *
 * @property integer $id ID
 * @property string $created_at Created At
 * @property integer $block_id Block ID
 * @property integer $block_repeat_id Block Repeat ID
 * @property integer $begin_datetime Begin Datetime
 * @property integer $end_datetime End Datetime
 * @property integer $duration Duration
 * @property integer $section_id Section ID
 * @property integer $bitrix_section_id Bitrix Section ID
 * @property string $type Type
 * @property string $title Title
 * @property string $subcategory Subcategory
 * @property string $section_name Section Name
 * @property string $section_color Section Color
*/
class SsMediaplanAirBlocks extends \common\ActiveRecord
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
        return 'ss_mediaplan_air_blocks';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_at'], 'safe'],
            [['block_id', 'begin_datetime', 'end_datetime', 'section_id'], 'required'],
            [['block_id', 'block_repeat_id', 'begin_datetime', 'end_datetime', 'duration', 'section_id', 'bitrix_section_id'], 'integer'],
            [['type'], 'string', 'max' => 50],
            [['title', 'subcategory', 'section_name', 'section_color'], 'string', 'max' => 100],
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
            'block_id' => 'Block ID',
            'block_repeat_id' => 'Block Repeat ID',
            'begin_datetime' => 'Begin Datetime',
            'end_datetime' => 'End Datetime',
            'duration' => 'Duration',
            'section_id' => 'Section ID',
            'bitrix_section_id' => 'Bitrix Section ID',
            'type' => 'Type',
            'title' => 'Title',
            'subcategory' => 'Subcategory',
            'section_name' => 'Section Name',
            'section_color' => 'Section Color',
            ];
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\SsMediaplanAirBlocksQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\SsMediaplanAirBlocksQuery(get_called_class());
    }
}
