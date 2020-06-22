<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "expert_users".
 *
 * @property integer $id ID
 * @property integer $phone Phone
 * @property integer $order_count Order Count
 * @property integer $is_processed Is Processed
*/
class ExpertUsers extends \common\ActiveRecord
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
        return 'expert_users';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['phone', 'order_count', 'is_processed'], 'integer'],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'phone' => 'Phone',
            'order_count' => 'Order Count',
            'is_processed' => 'Is Processed',
            ];
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\ExpertUsersQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\ExpertUsersQuery(get_called_class());
    }
}
