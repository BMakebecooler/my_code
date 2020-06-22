<?php

namespace console\controllers\queues\jobs\products;

use console\controllers\queues\jobs\Job;
use console\jobs\UpdateNewFieldsJob;
use modules\shopandshow\lists\Guids;
use modules\shopandshow\models\newEntities\products\Product as entityProduct;
use Yii;

class Product extends Job
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

            //Кривые лоты из 1С / КФСС
            $type = $this->data['Data']['Type'];
            if ($type == 'LOT' && $this->data['Data']['Name'] == 'Undefined!!!'){
                return true;
            }

            Yii::$app->queueProduct->push(new UpdateNewFieldsJob([
                'data' => $queue,
            ]));

            return $this->addProduct();
        }

        return false;
    }

    /**
     * @return bool
     */
    protected function addProduct()
    {
        $info = $this->data['Info'];
        if (!isset($this->data['Data'])) {
            Job::dump(' no data');

            return false;
        }
        $data = $this->data['Data'];

        $guid = trim($data['Guid']);

        Job::dump('---Product----');

        $newProduct = new entityProduct();

        if ($product = Guids::getEntityByGuid($guid)) {
            $newProduct->setCmsContentElement($product);
        }

        Job::dump('Guid: ' . $data['Guid']);
        Job::dump('ParentGuid: ' . $data['ParentGuid']);
        Job::dump('KfssId: ' . $data['Id']);
        Job::dump('Name: ' . $data['Name']);
        Job::dump('Type: ' . $data['Type']);
        Job::dump('IsBase: ' . @$data['IsBase']);
        Job::dump('NodeGUID: ' . $data['OffcntIndxNodeGUID'] ?? '<EMPTY>');

        $newProduct->guid = $guid;
        $newProduct->name = $data['Name'];
        $newProduct->parent_guid = $data['ParentGuid'];
        $newProduct->node_guid = $data['OffcntIndxNodeGUID'] ?? false;
        $newProduct->type = $data['Type'];
        $newProduct->active = $data['Active'];
        $newProduct->is_base = $data['IsBase'] ?? false;
        $newProduct->kfss_id = $data['Id'];

        return $newProduct->addData();
    }
}