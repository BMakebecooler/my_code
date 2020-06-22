<?php
namespace console\controllers\queues\jobs\products;

use console\controllers\queues\jobs\Job;
use modules\shopandshow\lists\Guids;
use modules\shopandshow\models\newEntities\products\LinkList;

class Links extends Job
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
            $guid = $this->data['Data']['OffcntGuid'];

            return $this->addLinks();
        }

        return false;
    }

    /**
     * @return bool
     */
    protected function addLinks()
    {
        $info = $this->data['Info'];
        $data = $this->data['Data'];

        $guid = trim($data['OffcntGuid']);

        Job::dump('---ProductLinks----');
        Job::dump('Guid: '.$guid);
        Job::dump('Link count: '.sizeof($data['Link']));

        $productLinks = new LinkList();

        $product = $productLinks->getOrCreateElement($guid);
        if ($product == false) {
            Job::dump(' failed to get product');
            return false;
        }

        $productLinks->setCmsContentElement($product);
        $productLinks->setLinksList((array)$data['Link']);

        return $productLinks->addData();
    }
}