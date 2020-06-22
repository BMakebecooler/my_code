<?php

namespace common\components\queue\handler\protocolkfss\v20;

use common\components\queue\AbstractHandler;
use common\components\queue\HandlerInterface;
use modules\shopandshow\models\newEntities\products\PricesList;
use yii\db\Exception;

/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 2019-03-26
 * Time: 17:30
 */
class OffcntPrice extends AbstractHandler implements HandlerInterface
{

    public function execute()
    {
        return true;

        $guid = $this->data->OffcntGuid;
        $product = parent::getProductByGuid($guid);

        $shopTypePrice = new PricesList();
        $shopTypePrice = $shopTypePrice->ensureShopTypePrice($this->data->PriceMainGuid);
        if (!$shopTypePrice) {
            throw new Exception('Error find shopTypePrice guid ' .$this->data->PriceMainGuid );
        }
        $product->new_price = $shopTypePrice->id;
        if (!$product->save()) {
            throw new Exception('Error save new_price errors' . print_r($product->errors, true));
        }


        return true;
    }
}