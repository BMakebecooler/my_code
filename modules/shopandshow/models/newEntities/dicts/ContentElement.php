<?php
namespace modules\shopandshow\models\newEntities\dicts;

use common\models\cmsContent\CmsContentElement;
use console\controllers\queues\jobs\Job;
use modules\shopandshow\lists\Guids;
use modules\shopandshow\models\newEntities\common\CmsContentElementModel;
use skeeks\cms\components\Cms;

class ContentElement extends CmsContentElementModel
{
    public $content_guid;
    public $value;

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'content_guid' => 'Глобальный идентификатор свойства',
            'value' => 'Значение'
        ];
    }

    /**
     * @return bool
     */
    public function addData()
    {

        /** @var $cmsContent \common\models\cmsContent\CmsContent */
        if (!$cmsContent = Guids::getEntityByGuid($this->content_guid)) {
            Job::dump(' parent property not found');
            return false;
        }

        $newContentElement = $this->getOrCreateElement($this->guid, $cmsContent->id);

        $newContentElement->code = $this->guid;
        $newContentElement->active = Cms::BOOL_Y;

        // значение таких свойств может меняться? Надеюсь что нет, но мало ли что, это же кфсс, там возможно все.
        $newContentElement->name = $this->value;

        if(!$newContentElement->save()) {
            Job::dump($newContentElement->getErrors());
            return false;
        }

        return true;
    }
}