<?php


namespace common\components\queue\handler\protocolkfss\v20;


use common\components\queue\AbstractHandler;
use common\components\queue\HandlerInterface;
use common\models\ProductProperty;

class SizeScale extends AbstractHandler implements HandlerInterface
{
    public function execute()
    {
        $guid = $this->data->Guid;
        $productProperty = ProductProperty::find()->byGuid($guid)->one();
        $productProperty = ProductProperty::find()->byGuid($guid)->one();
        if (empty($productProperty)) {
            throw  new Exception('Error find product property by guid ' . $guid);
        }
    }
}