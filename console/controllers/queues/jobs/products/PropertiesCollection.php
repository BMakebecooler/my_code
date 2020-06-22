<?php
namespace console\controllers\queues\jobs\products;

use console\controllers\queues\jobs\Job;
//use console\jobs\UpdateNewFieldsJob;
use modules\shopandshow\models\newEntities\products\PropertiesCollectionList;

use Yii;

class PropertiesCollection extends Job
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

            //TODO Раскоментить когда придет время )
//            Yii::$app->queueProduct->push(new UpdateNewFieldsJob([
//                'data' => $queue,
//            ]));

            return $this->addProps();
        }

        return false;
    }

    /**
     * @return bool
     */
    protected function addProps()
    {
        $info = $this->data['Info'];
        $data = $this->data['Data'];

        $guid = trim($data['Guid']);

        Job::dump('---Lot (PropertiesCollection)----');
        Job::dump('Guid: '.$guid);
        Job::dump('Property count: '. (!empty($data['PropertiesCollection']) ? sizeof($data['PropertiesCollection']) : 'EMPTY'));

        if (empty($data['PropertiesCollection'])){
            return true;
        }

        $productProps = new PropertiesCollectionList();

        $product = $productProps->getOrCreateElement($guid);
        if ($product == false) {
            Job::dump(' failed to get product');
            return false;
        }

        $productProps->setCmsContentElement($product);
        $productProps->setPropertyList((array)$data['PropertiesCollection']);

        return $productProps->addData();
    }
}