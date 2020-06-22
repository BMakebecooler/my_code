<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "ss_user_vote".
 *
 * @property integer $id ID
 * @property integer $cms_user_id Cms User ID
 * @property integer $cms_content_element_id Cms Content Element ID
 * @property string $value Value
 * @property integer $vote_id Vote ID
 *
     * @property CmsContentElement $cmsContentElement
     * @property CmsUser $cmsUser
    */
class SsUserVote extends \common\ActiveRecord
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
        return 'ss_user_vote';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['cms_user_id', 'cms_content_element_id', 'vote_id'], 'required'],
            [['cms_user_id', 'cms_content_element_id', 'vote_id'], 'integer'],
            [['value'], 'string', 'max' => 64],
            [['cms_content_element_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsContentElement::className(), 'targetAttribute' => ['cms_content_element_id' => 'id']],
            [['cms_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['cms_user_id' => 'id']],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'cms_user_id' => 'Cms User ID',
            'cms_content_element_id' => 'Cms Content Element ID',
            'value' => 'Value',
            'vote_id' => 'Vote ID',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsContentElement()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsContentElement', ['id' => 'cms_content_element_id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsUser()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsUser', ['id' => 'cms_user_id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\SsUserVoteQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\SsUserVoteQuery(get_called_class());
    }
}
