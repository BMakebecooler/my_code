<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "cms_content_element_relation".
 *
 * @property integer $content_element_id Content Element ID
 * @property integer $related_content_element_id Related Content Element ID
 *
     * @property CmsContentElement $contentElement
     * @property CmsContentElement $relatedContentElement
    */
class CmsContentElementRelation extends \common\ActiveRecord
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
        return 'cms_content_element_relation';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['content_element_id', 'related_content_element_id'], 'required'],
            [['content_element_id', 'related_content_element_id'], 'integer'],
            [['content_element_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsContentElement::className(), 'targetAttribute' => ['content_element_id' => 'id']],
            [['related_content_element_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsContentElement::className(), 'targetAttribute' => ['related_content_element_id' => 'id']],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'content_element_id' => 'Content Element ID',
            'related_content_element_id' => 'Related Content Element ID',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getContentElement()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsContentElement', ['id' => 'content_element_id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getRelatedContentElement()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsContentElement', ['id' => 'related_content_element_id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\CmsContentElementRelationQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\CmsContentElementRelationQuery(get_called_class());
    }
}
