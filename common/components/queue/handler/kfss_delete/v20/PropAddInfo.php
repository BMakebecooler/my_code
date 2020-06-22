<?php

namespace common\components\queue\handler\kfss\v20;

use common\components\queue\AbstractHandler;
use common\components\queue\HandlerInterface;


use yii\db\Exception;

/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 2019-03-26
 * Time: 17:30
 */
class PropAddInfo extends AbstractHandler implements HandlerInterface
{

    public function execute()
    {
        $guid = $this->data->OffcntGuid;
        $product = parent::getProductByGuid($guid);
        if($this->data->Props) {
            foreach ($this->data->Props as $prop){
                $property = $product->getPropertyByGuid($prop->PropTypeGuid);
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