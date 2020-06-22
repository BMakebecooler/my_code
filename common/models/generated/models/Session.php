<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "session".
 *
 * @property string $id ID
 * @property integer $expire Expire
 * @property resource $data Data
 * @property integer $user_id User ID
*/
class Session extends \common\ActiveRecord
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
        return 'session';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['expire', 'user_id'], 'integer'],
            [['data'], 'string'],
            [['id'], 'string', 'max' => 40],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'expire' => 'Expire',
            'data' => 'Data',
            'user_id' => 'User ID',
            ];
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\SessionQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\SessionQuery(get_called_class());
    }
}
