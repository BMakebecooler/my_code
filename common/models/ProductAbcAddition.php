<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 2019-05-06
 * Time: 12:25
 */

namespace common\models;


use yii\db\Exception;

class ProductAbcAddition extends \common\models\generated\models\ProductAbcAddition
{


    public static function import()
    {
        self::deleteAll();
        foreach (BUFECommDop::find()->orderBy('sum_sale_loc_pos DESC')->each() as $index => $each) {
            /** @var BUFECommDop $each */

            $sourceProduct = NewProduct::find()
                ->byCode($each->LotCode_1)
                ->onlyLot()
                ->onlyActive()
                ->onlyPublic()
                ->hasQuantity()
                ->imageIdNotNull()
                ->priceMoreThanZero()
                ->one();

            $product = NewProduct::find()
                ->byCode($each->LotCode_2)
                ->onlyLot()
                ->onlyActive()
                ->onlyPublic()
                ->hasQuantity()
                ->imageIdNotNull()
                ->priceMoreThanZero()
                ->one();


            if ($sourceProduct && $product) {
                $model = new self();
                $model->source_id = $sourceProduct->id;
                $model->product_id = $product->id;
                $model->order = $index + 1;
                if (!$model->save()) {
                    throw new Exception('Error import product abc');
                }
            }

        }
    }

}