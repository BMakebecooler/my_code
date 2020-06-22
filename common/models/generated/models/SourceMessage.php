<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "source_message".
 *
 * @property integer $id ID
 * @property string $category Category
 * @property string $message Message
 *
     * @property Message[] $messages
    */
class SourceMessage extends \common\ActiveRecord
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
        return 'source_message';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['message'], 'string'],
            [['category'], 'string', 'max' => 255],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'category' => 'Category',
            'message' => 'Message',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getMessages()
    {
        return $this->hasMany($this->called_class_namespace . '\Message', ['id' => 'id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\SourceMessageQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\SourceMessageQuery(get_called_class());
    }
}
