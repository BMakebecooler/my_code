<?php

namespace console\controllers\queues\jobs\dicts;

use console\controllers\queues\jobs\Job;
use modules\shopandshow\models\newEntities\dicts\Content;
use yii\helpers\Inflector;

class Brand extends Job
{
    const CODE = 'KFSS_BRAND';

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
            'code' => self::CODE,
            'contentType' => Content::CONTENT_TYPE_KFSS_INFO,
            'description' => $info['SourceDetail'],
            'contentElements' => [
                'guid' => $data['Guid'],
                'name' => $data['BrandName'],
                'code' => Inflector::slug($data['BrandName']),
                'description' => $data['BrandDescr'],
                'active' => $data['IsActive'],
            ],
            'contentProperties' => [
                'content_id' => PRODUCT_CONTENT_ID,
                'code' => self::CODE,
                'name' => 'Брэнд',
                'property_type' => 'L',
                'list_type' => 'L',
                'is_required' => 'N',
                'component' => 'skeeks\cms\relatedProperties\propertyTypes\PropertyTypeElement',
            ]
        ]);

        return $newContent->addData();
    }
}