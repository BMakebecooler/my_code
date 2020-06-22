<?php

/**
 * Виджет для страницы поддержки
 */

namespace common\widgets\content\support;

use common\lists\Contents;
use common\models\cmsContent\CmsContent;
use common\models\cmsContent\CmsContentElement;
use common\models\cmsContent\support\ContentElementSection;
use common\widgets\content\ContentElementWidget;
use skeeks\cms\components\Cms;
use yii\base\Widget;

class Support extends Widget
{

    public $label = '';

    public $viewFile = '@template/widgets/Support/index';
    public $viewNavigation = '@template/widgets/Support/navigation';
    public $viewListFile = '@template/widgets/Support/_list';
    public $questionForm = '@template/widgets/Support/_question_form';

    /**
     * @var int
     */
    public $limitProduct = 8;

    /**
     * @var CmsContentElement
     */
    public $model = null;

    /**
     * ContentElementFaq
     * @var null
     */
    public $parentModel = null;

    /**
     * @var CmsContent|null
     */
    protected $contentItem = null;

    /**
     * @var CmsContent|null
     */
    protected $contentQuestion = null;

    public function init()
    {
        parent::init();

        $this->contentItem = Contents::getContentByCode(ContentElementSection::CONTENT_CODE_SECTION);
        $this->contentQuestion = Contents::getContentByCode(ContentElementSection::CONTENT_CODE_QUESTIONS);
    }

    public function run()
    {
        return $this->render($this->viewFile);
    }

    /**
     * @return string
     */
    public function renderNavigation()
    {
        return $this->render($this->viewNavigation);
    }

    /**
     * @return string
     */
    public function getList()
    {
        if (!$this->contentItem && !$this->contentQuestion) {
            return false;
        }

        $propertyId = Contents::getIdContentPropertyByCode(ContentElementSection::QUESTIONS_ATTRIBUTE_NAME_THEME, $this->contentQuestion->id);

        $cmsContentElement = new ContentElementWidget([
            'contentElementClass' => ContentElementSection::className(),
            'namespace' => 'ContentElementsCmsWidget-support',
            'viewFile' => $this->viewListFile,
            'active' => Cms::BOOL_Y,
            'limit' => 20,
            //'orderBy' => 'priority',
            //'order' => SORT_ASC,
            'isAdditionalConditional' => false,
            'content_ids' => [$this->contentItem->id],
            'enabledCurrentTree' => false,
            'enabledRunCache' => Cms::BOOL_N,
            'runCacheDuration' => HOUR_5,
            'data' => [
                'propertyId' => $propertyId
            ]
        ]);

        $cmsContentElement->dataProvider->query->orderBy(['priority' => SORT_ASC, 'created_at' => SORT_ASC]);

        return $cmsContentElement->run();
    }

    /**
     * Вернуть данные разделов вопросов
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getTreeData()
    {
        return ContentElementSection::find()
            ->andWhere(['content_id' => $this->contentItem->id])
            ->active()
            ->orderBy(['priority' => SORT_ASC, 'created_at' => SORT_ASC])
            ->all();
    }

    /**
     * Признак активности раздела
     * @param $sectionId
     * @return bool
     */
    public function isTreeActive($sectionId)
    {
        return $this->model && ($this->model->relatedPropertiesModel->getAttribute(ContentElementSection::QUESTIONS_ATTRIBUTE_NAME_THEME) == $sectionId);
    }

    /**
     * Признак активности вопроса
     * @param $questionId
     * @return bool
     */
    public function isQuestionActive($questionId)
    {
        return $this->model && ($this->model->id === $questionId);
    }


    /**
     * Форма "задать вопрос поддержке"
     * @return string
     */
    public function renderQuestionForm()
    {
        return $this->render($this->questionForm);
    }

}