<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "ss_shares_schedule".
 *
 * @property integer $id ID
 * @property integer $created_at Created At
 * @property integer $created_by Created By
 * @property integer $updated_at Updated At
 * @property integer $updated_by Updated By
 * @property integer $begin_datetime Begin Datetime
 * @property integer $end_datetime End Datetime
 * @property string $block_type Block Type
 * @property integer $tree_id Tree ID
 * @property integer $block_position Block Position
 * @property string $name Name
 * @property string $description Description
 * @property string $type Type
 *
     * @property SsShares[] $ssShares
    */
class SsSharesSchedule extends \common\ActiveRecord
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
            'author' => \yii\behaviors\BlameableBehavior::class,
        ];
    }

    /**
    * @inheritdoc
    */
    public static function tableName()
    {
        return 'ss_shares_schedule';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_at', 'created_by', 'updated_at', 'updated_by', 'begin_datetime', 'end_datetime', 'tree_id', 'block_position'], 'integer'],
            [['begin_datetime', 'end_datetime', 'block_type', 'block_position'], 'required'],
            [['block_type', 'name', 'description'], 'string', 'max' => 255],
            [['type'], 'string', 'max' => 2],
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
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
            'begin_datetime' => 'Begin Datetime',
            'end_datetime' => 'End Datetime',
            'block_type' => 'Block Type',
            'tree_id' => 'Tree ID',
            'block_position' => 'Block Position',
            'name' => 'Name',
            'description' => 'Description',
            'type' => 'Type',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getSsShares()
    {
        return $this->hasMany($this->called_class_namespace . '\SsShares', ['share_schedule_id' => 'id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\SsSharesScheduleQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\SsSharesScheduleQuery(get_called_class());
    }
}
