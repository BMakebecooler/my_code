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
class ReserveMod extends AbstractHandler implements HandlerInterface
{

    public function execute()
    {
        $guid = $this->data->Guid;
        $canSell = $this->data->CanSell;

        $product = parent::getProductByGuid($guid);

        $product->new_quantity = $canSell;
        if (!$product->save()) {
            throw new Exception('Error save new_quantity errors' . print_r($product->errors, true));
        }


        return true;
    }
}