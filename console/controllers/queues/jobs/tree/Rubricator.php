<?php

namespace console\controllers\queues\jobs\tree;

use common\helpers\Common;
use console\controllers\queues\jobs\Job;
use modules\shopandshow\lists\Guids;
use modules\shopandshow\models\newEntities\common\TreeRubricatorModel;


class Rubricator extends Job
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

            return $this->addRubricator();
        }

        return false;
    }

    protected function addRubricator()
    {
        $info = $this->data['Info'];
        $data = $this->data['Data'];

        if ($data['OffcntIndxTreeGuid'] != TreeRubricatorModel::GUID_GROUP_SITE) {
            return true;
        }

        Job::dump('----- Rubricator ' . $data['OffcntIndxNodeName'] . '------');
        Job::dump('NotVisibleSite: ' . var_export($data['NotVisibleSite'] ?? '<NOT_SET> //aka FALSE', true));
        Job::dump('IsDeleted: ' . var_export($data['IsDeleted'] ?? '<NOT_SET> //aka FALSE', true));

        try {
            $guid = $data['Guid'];

            $newTree = new TreeRubricatorModel();

            if ($tree = Guids::getEntityByGuid($guid)) {
                $newTree->setTree($tree);
            }

            $newTree->guid = $guid;
            $newTree->name = $data['OffcntIndxNodeName'];
            $newTree->description = '';
            $newTree->parentGuid = $data['OffcntIndxNodeParentGuid'];
//            $newTree->active = isset($data['IsDeleted']) ? ($data['IsDeleted'] && $data['IsDeleted'] == true ? Common::BOOL_N : Common::BOOL_Y) : Common::BOOL_Y;
//            $newTree->active = isset($data['IsDeleted']) && $data['IsDeleted'] == true ? Common::BOOL_N : Common::BOOL_Y;
            $newTreeIsDeleted = !empty($data['IsDeleted']);
            $newTreeNotVisibleSite = !empty($data['NotVisibleSite']);
                //Элемент IsDeleted необязательный, добавляется в документ только при удалении указанного элемента
            $newTree->active = $newTreeIsDeleted || $newTreeNotVisibleSite ? Common::BOOL_N : Common::BOOL_Y;

            Job::dump("> Active: {$newTree->active}");

            return $newTree->addData();
        }
        catch (\Exception $e) {
            Job::dump($e->getTraceAsString());
            Job::dump($e->getMessage());
        }

        return false;
    }


}