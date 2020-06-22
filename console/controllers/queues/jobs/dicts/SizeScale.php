<?php

/**
 * Очередь "Справочник размерных шкал"
 */

namespace console\controllers\queues\jobs\dicts;

use console\controllers\queues\jobs\Job;
use modules\shopandshow\models\newEntities\dicts\Content;

class SizeScale extends Job
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
            if ($this->data['Info']['Type'] == 'MERCH_SIZE') {
                $job = \Yii::createObject(MerchSize::class);

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

        Job::dump('--- SizeScale ----');
        Job::dump('Guid: '.$data['Guid']);
        Job::dump('ScaleName: '.$data['ScaleName']);
        Job::dump('ScaleDescr: '.$data['ScaleDescr']);

        $code = $this->generateCode($data['ScaleName']);

        $newContent = new Content();

        $newContent->setAttributes([
            'contentType' => Content::CONTENT_TYPE_KFSS_INFO_SIZES,
            'description' => $info['SourceDetail'],
            'guid' => $data['Guid'],
            'name' => $data['ScaleName'],
            'code' => $code,
            'active' => isset($data['IsDeleted']) && $data['IsDeleted'] ? false : true,
            'contentProperties' => [
                'content_id' => OFFERS_CONTENT_ID,
                //'code' => $code,
                'name' => $data['ScaleName'],
                'property_type' => 'L',
                'list_type' => 'L',
                'is_required' => 'N',
                'component' => 'skeeks\cms\relatedProperties\propertyTypes\PropertyTypeElement',
            ]
        ]);

        return $newContent->addData();
    }

    protected function generateCode($string)
    {
        $string = trim(str_replace('Bitrix.', '', $string));
        return 'KFSS_'.mb_strtoupper(str_replace('-', '_', \common\helpers\Strings::translit($string)));
    }
}