<?php

namespace common\models\cmsContent\support;

use common\lists\Contents;
use common\models\cmsContent\CmsContentElement;

class ContentElementSection extends CmsContentElement
{

    const CONTENT_CODE_SECTION = 'support-theme';
    const CONTENT_CODE_QUESTIONS = 'support-questions';

    const QUESTIONS_ATTRIBUTE_NAME_THEME = 'section';

    protected $propertyId;

    public function init()
    {
        parent::init();

        $contentQuestion = Contents::getContentByCode(self::CONTENT_CODE_QUESTIONS);

        $this->propertyId = Contents::getIdContentPropertyByCode(self::QUESTIONS_ATTRIBUTE_NAME_THEME, $contentQuestion->id);
    }

    /**
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getQuestion()
    {
        return CmsContentElement::find()
            ->joinWith('relatedElementProperties')
            ->andWhere(['value' => $this->id, 'property_id' => $this->propertyId])
            ->orderBy(['priority' => SORT_ASC, 'created_at' => SORT_ASC])
            ->all();
    }

}