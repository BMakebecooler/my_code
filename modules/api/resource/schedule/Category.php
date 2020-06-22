<?php


namespace modules\api\resource\schedule;


use common\helpers\Dates;
use yii\data\ActiveDataProvider;

class Category extends \common\models\SsMediaplanAirBlocks
{
    public function fields()
    {
        return [
            'id' => 'section_id',
            'name' => 'section_name',
            'productsCounter' => function(){
                $catProducts = $this->getCategoryProductsForSale();
                return count($catProducts);
            }
        ];
    }
}