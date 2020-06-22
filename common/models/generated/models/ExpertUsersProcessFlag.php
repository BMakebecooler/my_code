<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "expert_users_process_flag".
 *
 * @property integer $id ID
 * @property string $update_time Update Time
 * @property integer $check_cnt Check Cnt
*/
class ExpertUsersProcessFlag extends \common\ActiveRecord
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
        return 'expert_users_process_flag';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['update_time'], 'safe'],
            [['check_cnt'], 'integer'],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'update_time' => 'Update Time',
            'check_cnt' => 'Check Cnt',
            ];
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\ExpertUsersProcessFlagQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\ExpertUsersProcessFlagQuery(get_called_class());
    }
}
