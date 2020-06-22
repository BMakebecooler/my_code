<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "auth_item".
 *
 * @property string $name Name
 * @property integer $type Type
 * @property string $description Description
 * @property string $rule_name Rule Name
 * @property string $data Data
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 *
     * @property AuthAssignment[] $authAssignments
     * @property CmsUser[] $users
     * @property AuthRule $ruleName
     * @property AuthItemChild[] $authItemChildren
     * @property AuthItemChild[] $authItemChildren0
     * @property AuthItem[] $children
     * @property AuthItem[] $parents
    */
class AuthItem extends \common\ActiveRecord
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
        return 'auth_item';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['name', 'type'], 'required'],
            [['type', 'created_at', 'updated_at'], 'integer'],
            [['description', 'data'], 'string'],
            [['name', 'rule_name'], 'string', 'max' => 64],
            [['rule_name'], 'exist', 'skipOnError' => true, 'targetClass' => AuthRule::className(), 'targetAttribute' => ['rule_name' => 'name']],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'name' => 'Name',
            'type' => 'Type',
            'description' => 'Description',
            'rule_name' => 'Rule Name',
            'data' => 'Data',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getAuthAssignments()
    {
        return $this->hasMany($this->called_class_namespace . '\AuthAssignment', ['item_name' => 'name']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getUsers()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsUser', ['id' => 'user_id'])->viaTable('auth_assignment', ['item_name' => 'name']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getRuleName()
    {
        return $this->hasOne($this->called_class_namespace . '\AuthRule', ['name' => 'rule_name']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getAuthItemChildren()
    {
        return $this->hasMany($this->called_class_namespace . '\AuthItemChild', ['parent' => 'name']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getAuthItemChildren0()
    {
        return $this->hasMany($this->called_class_namespace . '\AuthItemChild', ['child' => 'name']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getChildren()
    {
        return $this->hasMany($this->called_class_namespace . '\AuthItem', ['name' => 'child'])->viaTable('auth_item_child', ['parent' => 'name']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getParents()
    {
        return $this->hasMany($this->called_class_namespace . '\AuthItem', ['name' => 'parent'])->viaTable('auth_item_child', ['child' => 'name']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\AuthItemQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\AuthItemQuery(get_called_class());
    }
}
