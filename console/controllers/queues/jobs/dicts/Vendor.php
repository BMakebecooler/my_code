<?php

namespace console\controllers\queues\jobs\dicts;

use console\controllers\queues\jobs\Job;
use modules\shopandshow\models\newEntities\dicts\Content;

class Vendor extends Job
{

    /**
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

            return $this->addNewContent();
        }

        return false;
    }

    /**
     * @return bool
     */
    protected function addNewContent()
    {
        $info = $this->data['Info'];
        $data = $this->data['Data'];

        $newContent = new Content();

        $newContent->setAttributes([
            'name' => $info['Type'],
            'contentType' => Content::CONTENT_TYPE_KFSS_INFO,
            'description' => $info['SourceDetail'],
            'contentElements' => [
                'guid' => $data['Guid'],
                'name' => $data['VendorName'],
                'description' => $data['VendorDescr'],
                'active' => $data['IsActive'],
            ]
        ]);

        return $newContent->addData();
    }
}