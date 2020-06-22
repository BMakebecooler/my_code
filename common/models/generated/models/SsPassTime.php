<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "ss_pass_time".
 *
 * @property integer $id ID
 * @property integer $created_at Created At
 * @property integer $seconds_first_symbol Seconds First Symbol
 * @property integer $seconds Seconds
 * @property integer $fuser_id Fuser ID
*/
class SsPassTime extends \common\ActiveRecord
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
        return 'ss_pass_time';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_at', 'seconds_first_symbol', 'seconds', 'fuser_id'], 'integer'],
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
            'seconds_first_symbol' => 'Seconds First Symbol',
            'seconds' => 'Seconds',
            'fuser_id' => 'Fuser ID',
            ];
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\SsPassTimeQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\SsPassTimeQuery(get_called_class());
    }
}
