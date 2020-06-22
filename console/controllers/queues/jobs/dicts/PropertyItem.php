<?php

/**
 * Очередь "Справочник "Значения свойства типа "Перечисление""
 */

namespace console\controllers\queues\jobs\dicts;

use console\controllers\queues\jobs\Job;
use modules\shopandshow\lists\Guids;
use modules\shopandshow\models\newEntities\dicts\ContentElement;

class PropertyItem extends Job
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

            return $this->addPropertyEnum();
        }

        return false;
    }

    /**
     * @return bool
     */
    protected function addPropertyEnum()
    {
        $info = $this->data['Info'];
        $data = $this->data['Data'];

        Job::dump('--- PropertyEnum ----');
        Job::dump('Guid: '.$data['Guid']);
        Job::dump('PropertyGuid: '.$data['PropertyGuid']);
        Job::dump('EnumValue: '.$data['EnumValue']);

        if (empty($data['PropertyGuid'])) {
            Job::dump(' empty PropertyGuid');
            return false;
        }

        $contentElement = new ContentElement();

        $contentElement->setAttributes([
            'guid' => $data['Guid'],
            'content_guid' => $data['PropertyGuid'],
            'value' => $data['EnumValue']
        ]);

        return $contentElement->addData();
    }
}