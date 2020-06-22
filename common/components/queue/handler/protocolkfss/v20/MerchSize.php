<?php


namespace common\components\queue\handler\protocolkfss\v20;


use common\components\queue\AbstractHandler;
use common\components\queue\HandlerInterface;
use common\models\ProductProperty;

class MerchSize extends AbstractHandler implements HandlerInterface
{
    public function execute()
    {
        $relatedSizes = [];
        $relatedSizesScales = [];

        foreach ($this->data->RelatedSizeGuid as $guid){
            $productPropertyRelated = ProductProperty::find()->byGuid($guid)->one();
            if($productPropertyRelated){
                $relatedSizes[] =  $productPropertyRelated->id;
            }
        }
        foreach ($this->data->RelatedSizeScaleGuid as $guid){
            $sizeScaleRelated = ProductProperty::find()->byGuid($guid)->one();
            if($productPropertyRelated){
                $relatedSizesScales[] =  $sizeScaleRelated->id;
            }
        }

//        echo '<pre>';
//        print_r($this->data);
//        echo '</pre>';
//        die();

        $guid = $this->data->Guid;
        $productProperty = ProductProperty::find()->byGuid($guid)->one();
        if (empty($productProperty)) {
            throw  new Exception('Error find product property by guid ' . $guid);
        }
    }
}