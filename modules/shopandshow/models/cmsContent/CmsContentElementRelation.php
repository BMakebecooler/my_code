<?php

namespace modules\shopandshow\models\cmsContent;

use common\models\cmsContent\CmsContentElement;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%cms_content_element_relation}}".
 *
 * @property integer $content_element_id
 * @property integer $related_content_element_id
 * @property string  $tag
 *
 * @property CmsContentElement[] $relatedContentElements
 * @property CmsContentElement $contentElement
 */
class CmsContentElementRelation extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%cms_content_element_relation}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['content_element_id', 'related_content_element_id'], 'integer'],
            [['content_element_id', 'related_content_element_id'], 'required'],
            [['content_element_id', 'related_content_element_id'], 'unique', 'targetAttribute' => ['content_element_id', 'related_content_element_id'], 'message' => 'The combination of Content Element ID and Related Content Element ID has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'content_element_id' => Yii::t('skeeks/cms', 'Content Element ID'),
            'related_content_element_id' => Yii::t('skeeks/cms', 'Content Element ID')
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRelatedContentElements()
    {
        return $this->hasMany(CmsContentElement::className(), ['id' => 'related_content_element_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContentElement()
    {
        return $this->hasOne(CmsContentElement::className(), ['id' => 'content_element_id']);
    }
}