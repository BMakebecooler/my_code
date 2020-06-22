<?php
namespace console\controllers\queues\jobs\products;

use console\controllers\queues\jobs\Job;
use modules\shopandshow\lists\Guids;
use modules\shopandshow\models\newEntities\products\Classificator as entityClassificator;

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

    /**
     * @return bool
     */
    protected function addClassificator()
    {
        $info = $this->data['Info'];
        $data = $this->data['Data'];

        $guid = trim($data['Guid']);

        Job::dump('---ProductClassificator----');
        Job::dump('Guid: '.$guid);
        Job::dump('Class count: '.sizeof($data['CLASS']));

        $productClassificator = new entityClassificator();

        $product = $productClassificator->getOrCreateElement($guid);
        if ($product == false) {
            Job::dump(' failed to get product');
            return false;
        }

        $productClassificator->setCmsContentElement($product);
        $productClassificator->classificators = (array)$data['CLASS'];

        return $productClassificator->addData();
    }
}