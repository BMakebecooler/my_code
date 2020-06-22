<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "auth_item_child".
 *
 * @property string $parent Parent
 * @property string $child Child
 *
     * @property AuthItem $parent0
     * @property AuthItem $child0
    */
class AuthItemChild extends \common\ActiveRecord
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
        return 'auth_item_child';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['parent', 'child'], 'required'],
            [['parent', 'child'], 'string', 'max' => 64],
            [['parent'], 'exist', 'skipOnError' => true, 'targetClass' => AuthItem::className(), 'targetAttribute' => ['parent' => 'name']],
            [['child'], 'exist', 'skipOnError' => true, 'targetClass' => AuthItem::className(), 'targetAttribute' => ['child' => 'name']],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'parent' => 'Parent',
            'child' => 'Child',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getParent0()
    {
        return $this->hasOne($this->called_class_namespace . '\AuthItem', ['name' => 'parent']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getChild0()
    {
        return $this->hasOne($this->called_class_namespace . '\AuthItem', ['name' => 'child']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\AuthItemChildQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\AuthItemChildQuery(get_called_class());
    }
}
