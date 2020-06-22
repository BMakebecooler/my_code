<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 2019-03-27
 * Time: 17:58
 */

namespace common\models;


use yii\db\Exception;

class ProductParamProduct extends \common\models\generated\models\ProductParamProduct
{

    public static function create($product, $productParam)
    {
        $model = new self();
        $model->product_id = $product->id;
        $model->product_param_id = $productParam->id;
        if (!$model->save()) {
            echo 'Error save ProductParamProduct product_id ' . $product->id . ', param_id ' . $productParam->id.PHP_EOL;
            echo print_r($model->errors,true).PHP_EOL;
//            throw new Exception('Error save ProductParamProduct product_id ' . $product->id . ', param_id ' . $productParam->id);
        }

        return $model;
    }

}