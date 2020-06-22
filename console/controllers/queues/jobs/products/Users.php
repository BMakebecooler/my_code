<?php
namespace console\controllers\queues\jobs\products;

use console\controllers\queues\jobs\Job;
use modules\shopandshow\lists\Guids;
use modules\shopandshow\models\newEntities\products\UsersList;

class Users extends Job
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

            return $this->addUsers();
        }

        return false;
    }

    /**
     * @return bool
     */
    protected function addUsers()
    {
        $info = $this->data['Info'];
        $data = $this->data['Data'];

        $guid = trim($data['OffcntGuid']);

        Job::dump('---ProductUsers----');
        Job::dump('Guid: '.$guid);
        Job::dump('Users count: '.sizeof($data['Users']));

        $productUsers = new UsersList();

        $product = $productUsers->getOrCreateElement($guid);
        if ($product == false) {
            Job::dump(' failed to get product');
            return false;
        }

        $productUsers->setCmsContentElement($product);
        $productUsers->setUsersList((array)$data['Users']);

        return $productUsers->addData();
    }
}