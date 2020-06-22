<?php
namespace console\controllers\queues\jobs\products;

use console\controllers\queues\jobs\Job;
use console\jobs\UpdateNewFieldsJob;
use modules\shopandshow\lists\Guids;
use modules\shopandshow\models\newEntities\products\MediaList;
use Yii;

class MediaContent extends Job
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

            Yii::$app->queueProduct->push(new UpdateNewFieldsJob([
                'data' => $queue,
            ]));

            return $this->addMediaContent();
        }

        return false;
    }

    /**
     * @return bool
     */
    protected function addMediaContent()
    {
        $info = $this->data['Info'];
        $data = $this->data['Data'];

        $guid = trim($data['OffcntGuid']);

        Job::dump('---ProductMediaContent----');
        Job::dump('Guid: '.$guid);
        Job::dump('Media count: '.sizeof($data['Media']));

        $productMedia = new MediaList();

        $product = $productMedia->getOrCreateElement($guid);
        if ($product == false) {
            Job::dump(' failed to get product');
            return false;
        }

        $productMedia->setCmsContentElement($product);
        $productMedia->setMediaList((array)$data['Media']);

        return $productMedia->addData();
    }
}