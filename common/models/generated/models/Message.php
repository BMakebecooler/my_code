<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "message".
 *
 * @property integer $id ID
 * @property string $language Language
 * @property string $translation Translation
 *
     * @property SourceMessage $id0
    */
class Message extends \common\ActiveRecord
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
        return 'message';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['id', 'language'], 'required'],
            [['id'], 'integer'],
            [['translation'], 'string'],
            [['language'], 'string', 'max' => 255],
            [['id'], 'exist', 'skipOnError' => true, 'targetClass' => SourceMessage::className(), 'targetAttribute' => ['id' => 'id']],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'language' => 'Language',
            'translation' => 'Translation',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getId0()
    {
        return $this->hasOne($this->called_class_namespace . '\SourceMessage', ['id' => 'id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\MessageQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\MessageQuery(get_called_class());
    }
}
