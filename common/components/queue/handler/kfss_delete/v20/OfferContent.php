<?php

namespace common\components\queue\handler\kfss\v20;

use common\components\queue\AbstractHandler;
use common\components\queue\HandlerInterface;
use common\helpers\Strings;



use yii\db\Exception;



class OfferContent extends AbstractHandler implements HandlerInterface
{
    public function execute()
    {
        $type = $this->data->Type ?? null;
        if($type == 'LOT') {

            $guid = $this->data->Guid;
            $product = parent::getProductByGuid($guid);

            $lotData = Strings::parsProductName($this->data->Name);
            if($lotData['NUM']) {
                $product->new_lot_num = $lotData['NUM'];
            }
            if($lotData['NAME']) {
                $product->new_lot_name = $lotData['NAME'];
            }

            if (!$product->save()) {
                throw new Exception('Error save new_price errors' . print_r($product->errors, true));
            }

            return true;
        }else {
            return false;
        }

    }

}