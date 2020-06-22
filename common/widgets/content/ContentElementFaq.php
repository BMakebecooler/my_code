<?php

namespace common\widgets\content;

use common\models\cmsContent\ContentElementFaq as ContentElementFaqModel;
use yii\base\Widget;

class ContentElementFaq extends Widget
{

    public $viewFile = '@template/widgets/ContentElementsCms/faq/index';

    public $contentElementId = null;

    public function init()
    {
        parent::init();
    }

    /**
     * @return string
     */
    public function run()
    {
        return $this->render($this->viewFile);
    }

    protected function find()
    {
        if (!$this->contentElementId) {
            return [];
        }

        $contentQaA = ContentElementFaqModel::find();
        $contentQaA->andWhere('status =:status AND element_id = :element_id AND published_at < :published_at', [
            ':status' => ContentElementFaqModel::STATUS_PUBLISHED,
            ':element_id' => $this->contentElementId,
            ':published_at' => time(),
        ]);

        $contentQaA->orderBy('cms_content_element_faq.published_at ASC');

        return $contentQaA;
    }

    /**
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getQaA()
    {
        return $this->find()->all();
    }

    public function getCountQaA()
    {
        return $this->find()->count();
    }
}