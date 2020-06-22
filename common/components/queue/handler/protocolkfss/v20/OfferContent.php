<?php

namespace common\components\queue\handler\protocolkfss\v20;

use common\components\queue\AbstractHandler;
use common\components\queue\HandlerInterface;
use common\helpers\Strings;



use yii\db\Exception;



class OfferContent extends AbstractHandler implements HandlerInterface
{
    public function execute()
    {
        $type = $this->data['Type'] ?? null;
        if($type == 'LOT') {

            $guid = $this->data['Guid'];

            if (!$guid){
                \Yii::error("Empty product GUID! Data: " . var_export($this->data, true), __METHOD__);
                return false;
            }

            $product = parent::getProductByGuid($guid);

            $lotData = Strings::parsProductName($this->data['Name']);
            if(!empty($lotData['NUM'])) {
                $product->new_lot_num = $lotData['NUM'];
            }
            if(!empty($lotData['NAME'])) {
                $product->new_lot_name = $lotData['NAME'];
            }

            if (!$product->save(false)) {
                throw new Exception('Error save OfferContent errors' . print_r($product->errors, true));
            }

            return true;
        }else {
            return false;
        }

    }

}