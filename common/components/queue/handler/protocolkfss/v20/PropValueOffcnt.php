<?php

namespace common\components\queue\handler\protocolkfss\v20;

use common\components\queue\AbstractHandler;
use common\components\queue\HandlerInterface;

use common\models\Product;
use yii\db\Exception;


class PropValueOffcnt extends AbstractHandler implements HandlerInterface
{
    public function execute()
    {
        $guid = $this->data['OffcntGuid'];
        /** @var Product $product */
        $product = parent::getProductByGuid($guid);
        if($this->data['Property']) {
            foreach ($this->data['Property'] as $prop){
                $property = $product->getPropertyByGuid($prop['PropGuid']);
                if($property) {
                    $product->$property = $this->getValueByProp($prop);
                }
            }

            if (!$product->save(false)) {
                throw new Exception('Error save new properties product errors' . print_r($product->errors, true));
            }
        }
        return true;
    }

    /**
     * @param array $property
     *
     * @return mixed
     */
    public function getValueByProp(array $property)
    {
        switch ($property['PropGuid']){
            //NOT_PUBLIC
            case '62E18FAAAE9F1E5FE0538201090A587C':
                //Воспринимаем только bool (tinyint)
                $value = (int)($property['PropValue'] == 'Y');
                break;
            default:
                $value = $property['PropValue'];
        }

        return $value;
    }
}