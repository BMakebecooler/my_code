<?php

/**
 * Очередь "Справочник размеров"
 */

namespace console\controllers\queues\jobs\dicts;

use console\controllers\queues\jobs\Job;
use modules\shopandshow\lists\Guids;
use modules\shopandshow\models\newEntities\dicts\Content;

class MerchSize extends Job
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

            // В один Exchange ходит сразу 2 типа сообщений, роутим в нужный тут
            if ($this->data['Info']['Type'] == 'SIZE_SCALE') {
                $job = \Yii::createObject(SizeScale::class);

                return $job->execute($queue, $guid);
            }

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

        Job::dump('--- MerchSize ----');
        Job::dump('Guid: '.$data['Guid']);
        Job::dump('SizeName: '.$data['SizeName']);
        Job::dump('SizeCode: '.$data['SizeCode']);

        $newContent = new Content();

        $sizeScale = Guids::getEntityByGuid($data['SizeScaleGuid']);
        if (!$sizeScale) {
            Job::dump('Не найден родительский раздел '.$data['SizeScaleGuid']);

            return false;
        }

        $newContent->setAttributes([
            'name' => $sizeScale->name,
            'code' => $sizeScale->code,
            'guid' => $data['SizeScaleGuid'],
            'contentType' => Content::CONTENT_TYPE_KFSS_INFO_SIZES,
            'description' => $sizeScale->description,
            'contentElements' => [
                'guid' => $data['Guid'],
                'name' => $data['SizeName'] ? : $data['SizeCode'],
                'code' => $data['Guid'],
                'description' => $data['SizeDescr'],
                'active' => true,
                'priority' => $data['SizeInScaleOrder'],
            ],
        ]);

        return $newContent->addData();
    }
}