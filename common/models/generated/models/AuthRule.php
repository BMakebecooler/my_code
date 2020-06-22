<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "auth_rule".
 *
 * @property string $name Name
 * @property string $data Data
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 *
     * @property AuthItem[] $authItems
    */
class AuthRule extends \common\ActiveRecord
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
        return 'auth_rule';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['data'], 'string'],
            [['created_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 64],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'name' => 'Name',
            'data' => 'Data',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getAuthItems()
    {
        return $this->hasMany($this->called_class_namespace . '\AuthItem', ['rule_name' => 'name']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\AuthRuleQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\AuthRuleQuery(get_called_class());
    }
}
