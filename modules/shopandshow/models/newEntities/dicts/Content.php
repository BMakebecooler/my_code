<?php

namespace modules\shopandshow\models\newEntities\dicts;

use common\models\cmsContent\CmsContent;
use modules\shopandshow\models\newEntities\common\CmsContentElementModel;
use skeeks\cms\components\Cms;

class Content extends CmsContentElementModel
{
    public $contentType;

    public $contentElements = [];
    public $contentProperties = [];

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'lotGuid' => 'lotGuid',
        ];
    }

    /**
     * @return bool
     */
    public function addData()
    {
        $cmsContent = new CmsContent();

        if ($this->guid) {
            $cmsContent->guid->setGuid($this->guid);
        }

        $config = [
            'code' => $this->code ?: $this->name,
            'name' => $this->name,
            'description' => $this->description,
            'content_type' => $this->contentType,
            'active' => ($this->active) ? Cms::BOOL_Y : Cms::BOOL_N,
            'guid' => $this->guid,
        ];

        if ($this->contentElements) {
            if (!isset($this->contentElements[0])) {
                $this->contentElements = [$this->contentElements];
            }
            $config['elements'] = $this->contentElements;
        }
        if ($this->contentProperties) {
            if (!isset($this->contentProperties[0])) {
                $this->contentProperties = [$this->contentProperties];
            }
            $config['properties'] = $this->contentProperties;
        }

        return $cmsContent->createContent($config);
    }


}