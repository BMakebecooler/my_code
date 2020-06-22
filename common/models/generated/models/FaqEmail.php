<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "faq_email".
 *
 * @property integer $id ID
 * @property string $group Group
 * @property string $type Type
 * @property integer $tree_id Tree ID
 * @property string $fio Fio
 * @property string $email Email
 *
     * @property CmsTree $tree
    */
class FaqEmail extends \common\ActiveRecord
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
        return 'faq_email';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['group', 'type'], 'required'],
            [['tree_id'], 'integer'],
            [['group', 'type', 'fio', 'email'], 'string', 'max' => 255],
            [['tree_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsTree::className(), 'targetAttribute' => ['tree_id' => 'id']],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'group' => 'Group',
            'type' => 'Type',
            'tree_id' => 'Tree ID',
            'fio' => 'Fio',
            'email' => 'Email',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getTree()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsTree', ['id' => 'tree_id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\FaqEmailQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\FaqEmailQuery(get_called_class());
    }
}
