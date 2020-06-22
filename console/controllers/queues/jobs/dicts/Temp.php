<?php
namespace console\controllers\queues\jobs\dicts;

use console\controllers\queues\jobs\Job;

class Temp extends Job
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
            // В один Exchange ходит сразу несколько типов сообщений, роутим в нужный тут
            if ($this->data['Info']['Type'] == 'COLOR') {
                $guid = $this->data['Data']['Guid'];

                $job = \Yii::createObject(Color::class);

                return $job->execute($queue, $guid);
            }
            elseif ($this->data['Info']['Type'] == 'MERCH_SIZE') {
                $guid = $this->data['Data']['Guid'];

                $job = \Yii::createObject(MerchSize::class);

                return $job->execute($queue, $guid);
            }
            elseif ($this->data['Info']['Type'] == 'SIZE_SCALE') {
                $guid = $this->data['Data']['Guid'];

                $job = \Yii::createObject(SizeScale::class);

                return $job->execute($queue, $guid);
            }
            elseif ($this->data['Info']['Type'] == 'PROPERTY_TYPE') {
                return true;
            }
            elseif ($this->data['Info']['Type'] == 'CLASSIFICATOR_TREE') {
                return true;
            }
            elseif ($this->data['Info']['Type'] == 'CLASSIFICATOR_NODE') {
                $guid = $this->data['Data']['Guid'];

                $job = \Yii::createObject(\console\controllers\queues\jobs\tree\Classificator::class);

                return $job->execute($queue, $guid);
            }
            elseif ($this->data['Info']['Type'] == 'PROPERTY') {
                $guid = $this->data['Data']['Guid'];

                $job = \Yii::createObject(Property::class);

                return $job->execute($queue, $guid);
            }
            elseif ($this->data['Info']['Type'] == 'PROPERTY_ITEM') {
                $guid = $this->data['Data']['Guid'];

                $job = \Yii::createObject(PropertyItem::class);

                return $job->execute($queue, $guid);
            }
            elseif ($this->data['Info']['Type'] == 'SEASON') {
                $guid = $this->data['Data']['Guid'];

                $job = \Yii::createObject(Season::class);

                return $job->execute($queue, $guid);
            }
            elseif ($this->data['Info']['Type'] == 'BRAND') {
                $guid = $this->data['Data']['Guid'];

                $job = \Yii::createObject(Brand::class);

                return $job->execute($queue, $guid);
            }
            elseif ($this->data['Info']['Type'] == 'OFFER_CONTENT') {
                $guid = $this->data['Data']['Guid'];

                $job = \Yii::createObject(\console\controllers\queues\jobs\products\Product::class);

                return $job->execute($queue, $guid);
            }
            elseif ($this->data['Info']['Type'] == 'OFFCNT_CLASS') {
                $guid = $this->data['Data']['Guid'];

                $job = \Yii::createObject(\console\controllers\queues\jobs\products\Classificator::class);

                return $job->execute($queue, $guid);
            }
            elseif ($this->data['Info']['Type'] == 'MAIN_PROP') {
                $guid = $this->data['Data']['Guid'];

                $job = \Yii::createObject(\console\controllers\queues\jobs\products\MainProp::class);

                return $job->execute($queue, $guid);
            }
            elseif ($this->data['Info']['Type'] == 'MEDIA_CONTENT') {
                $guid = $this->data['Data']['OffcntGuid'];

                $job = \Yii::createObject(\console\controllers\queues\jobs\products\MediaContent::class);

                return $job->execute($queue, $guid);
            }
            elseif ($this->data['Info']['Type'] == 'PROP_VALUE_OFFCNT') {
                $guid = $this->data['Data']['OffcntGuid'];

                $job = \Yii::createObject(\console\controllers\queues\jobs\products\Property::class);

                return $job->execute($queue, $guid);
            }
            elseif ($this->data['Info']['Type'] == 'OFFCNT_PRICE') {
                $guid = $this->data['Data']['OffcntGuid'];

                $job = \Yii::createObject(\console\controllers\queues\jobs\products\Price::class);

                return $job->execute($queue, $guid);
            }
            elseif ($this->data['Info']['Type'] == 'CL_RESERV_OFFCNT') {
                $guid = $this->data['Data']['Guid'];

                $job = \Yii::createObject(\console\controllers\queues\jobs\shop\Reserve::class);

                return $job->execute($queue, $guid);
            }
            // Очередь SKU в торговых предложениях
            elseif ($this->data['Info']['Type'] == 'OFFCNT_MO') {
                return true;
            }
            elseif ($this->data['Info']['Type'] == 'OFFCNT_USR_LNK') {
                $guid = $this->data['Data']['OffcntGuid'];

                $job = \Yii::createObject(\console\controllers\queues\jobs\products\Users::class);

                return $job->execute($queue, $guid);
            }
            elseif ($this->data['Info']['Type'] == 'OFFCNT_LNK') {
                $guid = $this->data['Data']['OffcntGuid'];

                $job = \Yii::createObject(\console\controllers\queues\jobs\products\Links::class);

                return $job->execute($queue, $guid);
            }
            else {
                echo 'not supported type: ' . $this->data['Info']['Type'] . PHP_EOL;
            }
        }

        return false;
    }
}