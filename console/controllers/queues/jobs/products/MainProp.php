<?php
namespace console\controllers\queues\jobs\products;

use console\controllers\queues\jobs\Job;
use modules\shopandshow\models\newEntities\products\MainPropList;

use console\jobs\UpdateNewFieldsJob;
use Yii;

class MainProp extends Job
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

            Yii::$app->queueProduct->push(new UpdateNewFieldsJob([
                'data' => $queue,
            ]));

            return $this->addProps();
        }

        return false;
    }

    /**
     * @return bool
     */
    protected function addProps()
    {
        $info = $this->data['Info'];
        $data = $this->data['Data'];

        $guid = trim($data['Guid']);

        if ($info['Type'] != 'MAIN_PROP') {
            \Yii::error("MainPropJob. Incorrect type input - {$info['Type']}: MSG: " . var_export($this->data, true), 'debug');
            //return true;

            if (!isset($data['BrandGuid'])) {
                //\Yii::error("NoBrandGuid. MSG: " . var_export($this->data, true), 'debug');
            }
            return true;
        }

        Job::dump('---ProductMainProp----');
        Job::dump('Guid: '.$guid);
        Job::dump('BrandGuid: '.$data['BrandGuid']);
        //Job::dump('GenderGuid: '.$data['GenderGuid']);
        //Job::dump('VendorGuid: '.$data['VendorGuid']);
        //Job::dump('SexGuid: '.$data['SexGuid']);
        //Job::dump('MerchGroupGuid: '.$data['MerchGroupGuid']);
        Job::dump('SeasonGuid: '.$data['SeasonGuid']);
        Job::dump('SizeGuid: '.$data['SizeGuid']);
        //Job::dump('ScaleAllGuid: '.$data['ScaleAllGuid']);
        Job::dump('ColorGuid: '.$data['ColorGuid']);
        //Job::dump('ColorListAllGuid: '.$data['ColorListAllGuid']);
        //Job::dump('Width: '.$data['Width']);
        //Job::dump('Height: '.$data['Height']);
        //Job::dump('Depth: '.$data['Depth']);
        Job::dump('Weight: '.$data['Weight']);
        //Job::dump('Value: '.$data['Value']);
        //Job::dump('Diameter: '.$data['Diameter']);
        //Job::dump('Power: '.$data['Power']);
        //Job::dump('Capacity: '.$data['Capacity']);
        Job::dump('ColorAdditionGuid: '.$data['ColorAdditionGuid']);

        $productMainProps = new MainPropList();

        $product = $productMainProps->getOrCreateElement($guid);
        if ($product == false) {
            Job::dump(' failed to get product');
            return false;
        }

        $productMainProps->setCmsContentElement($product);

        $productMainProps->BrandGuid = $data['BrandGuid'];
        $productMainProps->SeasonGuid = $data['SeasonGuid'];
        $productMainProps->SizeGuid = $data['SizeGuid'];
        $productMainProps->ColorGuid = $data['ColorGuid'];
        $productMainProps->ColorAdditionGuid = $data['ColorAdditionGuid'];
        $productMainProps->Weight = $data['Weight'];

        return $productMainProps->addData();
    }
}