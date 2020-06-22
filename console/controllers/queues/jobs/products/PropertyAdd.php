<?php
namespace console\controllers\queues\jobs\products;

use console\controllers\queues\jobs\Job;
use console\jobs\UpdateNewFieldsJob;
use modules\shopandshow\models\newEntities\products\PropertyAddList;

use Yii;

class PropertyAdd extends Job
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
            $guid = $this->data['Data']['OffcntGuid'] ?? null;

            //Нет ГУИДа - разговаривать не о чем
            if (!$guid){
                return true;
            }

            Yii::$app->queueProduct->push(new UpdateNewFieldsJob([
                'data' => $queue,
            ]));

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

        $guid = trim($data['OffcntGuid']);

        Job::dump('---ProductProperty----');
        Job::dump('Guid: '.$guid);
        Job::dump('Property count: '.sizeof($data['Props']));

        $productProps = new PropertyAddList();

        $product = $productProps->getOrCreateElement($guid);
        if ($product == false) {
            Job::dump(' failed to get product');
            return false;
        }

        $productProps->setCmsContentElement($product);
        $productProps->setPropertyList((array)$data['Props']);

        return $productProps->addData();
    }
}