<?php

namespace modules\shopandshow\models\newEntities\products;

use common\models\cmsContent\CmsContentElement;
use console\controllers\queues\jobs\Job;
use modules\shopandshow\lists\Guids;
use modules\shopandshow\models\newEntities\common\CmsContentElementModel;
use skeeks\cms\components\Cms;

class Classificator extends CmsContentElementModel
{

    public $classificators;

    const GUID_GROUP_SITE = '56C6FD87BC080421E0534301090A113F';

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'guid' => 'Глобальный идентификатор',
            'node_guid' => 'Идентификатор ноды раздела'
        ];
    }

    public function setCmsContentElement(CmsContentElement $contentElement)
    {
        $this->cmsContentElement = $contentElement;

        $this->setAttributes([
            'guid' => $contentElement->guid->getGuid(),
        ]);
    }

    /**
     * @return bool
     */
    public function addData()
    {
        if (!$this->classificators) {
            Job::dump(' empty class list');

            return true;
        }

        // ищем среди всех классификаторов только наш
        $nodeGuid = '';
        foreach ($this->classificators as $node) {
            if ($node['CLASSGUID'] == self::GUID_GROUP_SITE) {
                $nodeGuid = $node['NODEGUID'];
            }
        }

        // так и не нашли наш классификатор
        if (empty($nodeGuid)) {
            $this->cmsContentElement->tree_id = null;
        } else {
            if (!$tree = Guids::getEntityByGuid($nodeGuid)) {
                Job::dump(' cant find tree');

                return false;
            }

            $this->cmsContentElement->tree_id = $tree->id;
        }

        if (!$this->cmsContentElement->save()) {
            Job::dump($this->cmsContentElement->getErrors());

            return false;
        }

        return true;
    }
}