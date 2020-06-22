<?php

/**
 * Очередь "Справочник размеров"
 */

namespace console\controllers\queues\jobs\dicts;

use console\controllers\queues\jobs\Job;
use modules\shopandshow\lists\Guids;
use modules\shopandshow\models\newEntities\dicts\Content;

class Color extends Job
{

    const BITRIX_COLOR_LIST_GUID = '55EDB97686B0219EE0534301090A35B7';

    const CODE_BX = 'KFSS_COLOR_BX';
    const CODE = 'KFSS_COLOR';
    const COLOR_TYPE = 'COLOR_TYPE';

    const VALUE_MAP = [
        '55EDB97686B0219EE0534301090A35B7' => 'bitrix',
        '57E4C48A1395C7E6E0534301090A4F4A' => 'base',
        '57D21F433F92A03DE0534301090A5A44' => 'marketing',
        '57E4C48A1396C7E6E0534301090A4F4A' => 'filter'
    ];

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

        Job::dump('--------- Color --------------');

        //Так как присутствую товары с одинаковым маркетинговым цветом, но разными названиями картинок цвета (Солевая лампа, все белые, но: Куб, Звезда, и тп в параметре цвета изображения)
        //Принимаем и битриксовые цвета
        //[DEPRECATED] битрикосые цвета пропускаем
        if (sizeof($data['ColorListGuid']) == 1 && in_array(self::BITRIX_COLOR_LIST_GUID, $data['ColorListGuid'])) {
            Job::dump(' get Bitrix color');
            $code = self::CODE_BX;
//            Job::dump(' skip Bitrix color');
//            return true;
        }else{
            $code = self::CODE;
        }

        Job::dump('Guid: '.$data['Guid']);
        Job::dump('ColorName: '.$data['ColorName']);

        $newContent = new Content();

        $newContent->setAttributes([
            'name' => 'Цвет',
            'code' => $code, // $Info['Type']
            'contentType' => Content::CONTENT_TYPE_KFSS_INFO_COLORS,
            'description' => $info['SourceDetail'],
            'contentElements' => [
                'guid' => $data['Guid'],
                'name' => $data['ColorName'],
                'code' => $data['Guid'],
                'description' => $data['ColorDescr'],
                'active' => true,
                'relations' => !empty($data['ParentColorGuid']) ? array_unique((array)$data['ParentColorGuid']) : [],
                'relatedPropertiesModel' => [
                    self::COLOR_TYPE => array_map(function($item) {return self::VALUE_MAP[$item];}, (array)$data['ColorListGuid'])
                ]
            ],
            'contentProperties' => [
                [
                    'content_id' => CARD_CONTENT_ID,
                    'code' => $code,
                    'name' => 'Цвет',
                    'property_type' => 'L',
                    'list_type' => 'L',
                    'is_required' => 'N',
                    'component' => 'skeeks\cms\relatedProperties\propertyTypes\PropertyTypeElement',
                ],
                [
                    'code' => self::COLOR_TYPE,
                    'name' => 'Тип цвета',
                    'property_type' => 'L',
                    'list_type' => 'L',
                    'is_required' => 'N',
                    'is_multiple' => 'Y',
                    'component' => 'skeeks\cms\relatedProperties\propertyTypes\PropertyTypeElement',
                ],
            ]
        ]);

        return $newContent->addData();
    }
}