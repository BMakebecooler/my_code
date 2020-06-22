<?php

namespace console\controllers\queues\jobs\tree;

use console\controllers\queues\jobs\Job;
use modules\shopandshow\lists\Guids;
use modules\shopandshow\models\newEntities\common\TreeModel;
use skeeks\cms\components\Cms;


/**
 * Created by PhpStorm.
 * User: ubuntu5
 * Date: 17.10.17
 * Time: 13:57
 */
class Classificator extends Job
{


    /**
     *
     * @param \yii\queue\Queue $queue
     * @param string $guid
     *
     * @return bool
     * @throws \Exception
     */
    public function execute($queue, &$guid)
    {
        if ($this->prepareData($queue)) {
            $guid = $this->data['Data']['Guid'];

            return $this->addClassificator();
        }

        return false;
    }

    protected function addClassificator()
    {
        $info = $this->data['Info'];
        $data = $this->data['Data'];

        if ($data['MClassTreeGuid'] != TreeModel::GUID_GROUP_SITE) {
            return true;
        }

        Job::dump('----- Classificator ' . $data['MClassNodeName'] . '------');

        try {
            $guid = $data['Guid'];

            $newTree = new TreeModel();

            if ($tree = Guids::getEntityByGuid($guid)) {
                $newTree->setTree($tree);
            }

            $newTree->guid = $guid;
            $newTree->name = $data['MClassNodeName'];
            $newTree->description = $data['MClassNodeDescr'];
            $newTree->parentGuid = $data['MClassNodeParentGuid'];
            $newTree->active = isset($data['IsDeleted']) ? ($data['IsDeleted'] && $data['IsDeleted'] == true ? Cms::BOOL_N : Cms::BOOL_Y) : Cms::BOOL_Y;
                //Элемент IsDeleted необязательный, добавляется в документ только при удалении указанного элемента

            return $newTree->addData();
        }
        catch (\Exception $e) {
            Job::dump($e->getTraceAsString());
            Job::dump($e->getMessage());
        }

        return false;
    }


}