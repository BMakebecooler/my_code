<?php

namespace common\components\queue\handler\kfss\v20;

use common\components\queue\AbstractHandler;
use common\components\queue\HandlerInterface;


use yii\db\Exception;


class PropValueOffcnt extends AbstractHandler implements HandlerInterface
{
    public function execute()
    {
        $guid = $this->data->OffcntGuid;
        $product = parent::getProductByGuid($guid);
        if($this->data->Property) {
            foreach ($this->data->Property as $prop){
                $property = $product->getPropertyByGuid($prop->PropGuid);
                if($property) {
                    $product->$property = $prop->PropValue;
                }
            }

            if (!$product->save()) {
                throw new Exception('Error save new properties product errors' . print_r($product->errors, true));
            }
        }
        return true;
    }
}