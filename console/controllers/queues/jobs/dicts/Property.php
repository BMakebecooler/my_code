<?php

/**
 * Очередь "Справочник дополнительные свойства"
 */

namespace console\controllers\queues\jobs\dicts;

use console\controllers\queues\jobs\Job;
use modules\shopandshow\lists\Guids;
use modules\shopandshow\models\newEntities\dicts\Content;

class Property extends Job
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

            return $this->addProperty();
        }

        return false;
    }

    /**
     * @return bool
     */
    protected function addProperty()
    {
        $info = $this->data['Info'];
        $data = $this->data['Data'];

        Job::dump('--- Property ----');
        Job::dump('Guid: '.$data['Guid']);
        Job::dump('PropertyName: '.$data['PropertyName']);
        Job::dump('PropertyDescr: '.$data['PropertyDescr']);

        if (empty($data['PropertyName'])) {
            Job::dump(' empty PropertyName');
            return false;
        }

        if (Guids::getEntityByGuid($data['Guid'])) {
            return true;
        }

        $code = $this->generateCode($data['PropertyName']);

        $newContent = new Content();

        $newContent->setAttributes([
            'contentType' => Content::CONTENT_TYPE_KFSS_INFO_PROPERTY,
            'description' => $info['SourceDetail'],
            'guid' => $data['Guid'],
            'name' => $data['PropertyName'],
            'code' => $code,
            'active' => isset($data['IsDeleted']) && $data['IsDeleted'] ? false : true,
            'contentProperties' => [
                'content_id' => KFSS_PRODUCT_CONTENT_ID,
                'code' => $code,
                'name' => $data['PropertyName'],
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
        return 'KFSS_'.mb_strtoupper(str_replace('-', '_', \common\helpers\Strings::translit($string)));
    }
}