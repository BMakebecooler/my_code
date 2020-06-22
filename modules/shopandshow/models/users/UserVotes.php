<?php

namespace modules\shopandshow\models\users;

use common\models\cmsContent\CmsContentElement;
use common\models\user\User;

/**
 * Class UserVotes
 * @property string value
 */
class UserVotes extends \yii\db\ActiveRecord
{
    const LOOKBOOK_VOTE_ID = 1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ss_user_vote}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cms_user_id', 'cms_content_element_id', 'vote_id'], 'integer'],
            [['cms_user_id', 'cms_content_element_id', 'vote_id'], 'required'],
            [['value'], 'string', 'max' => 64],
            [['cms_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['cms_user_id' => 'id']],
            [
                ['cms_content_element_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => CmsContentElement::className(),
                'targetAttribute' => ['cms_content_element_id' => 'id'],
            ],
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'cms_user_id' => 'User',
            'cms_content_element_id' => 'Model',
            'vote_id' => 'Vote ID',
            'value' => 'Rating'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['cms_user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCmsContentElement()
    {
        return $this->hasOne(CmsContentElement::className(), ['cms_content_element_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public static function findForLookbook()
    {
        return self::find()->andWhere(['vote_id' => self::LOOKBOOK_VOTE_ID]);
    }

    /**
     * @param \yii\db\ActiveQuery $query
     * @return \yii\db\ActiveQuery
     */
    public static function findForCmsContentElement(\yii\db\ActiveQuery $query, $id)
    {
        return $query->andWhere(['cms_content_element_id' => $id]);
    }

    /**
     * @param \yii\db\ActiveQuery $query
     * @return \yii\db\ActiveQuery
     */
    public static function findForUser(\yii\db\ActiveQuery $query)
    {
        if(\Yii::$app->user->isGuest) return $query->where('1=0');
        return $query->andWhere(['cms_user_id' => \Yii::$app->user->identity->id]);
    }

    public function getDisplayValue()
    {
        return round((float)$this->value);
    }
}
