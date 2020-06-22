<?php

/**
 * php yii export/products/can-sale-to-analytics
 *
 *
 */

namespace console\controllers\export;


use common\models\BUFECommDayOnLine;
use common\models\Product;
use yii\console\Controller;
use yii\db\Exception;
use yii\helpers\Console;

class ProductsController extends Controller
{
    //Товары которые можем продать отправляем аналитикам
    public function actionCanSaleToAnalytics()
    {
        $productsPerQuery = 1000;
//        $productsPerQuery = 100;

        $products = Product::find()->onlyLot()->canSale();

        if ($products){
            $productsForExport = [];

            $dt = date('Y-m-d');

            /** @var Product $product */
            $lotsInExport = 0;
            foreach ($products->each() as $product) {
                $offers = Product::getProductOffersCanSale($product->id);

                if ($offers){
                    $lotsInExport++;
                    foreach ($offers as $offer) {
                        $productsForExport[] = ['dt' => $dt, 'OFFCNT_ID' => $offer->kfss_id];
                    }
                }
            }

            $this->stdout("Products for export = " . count($productsForExport) . " [lots={$lotsInExport}]" . PHP_EOL);

            if ($productsForExport){
                $exportBlocks = array_chunk($productsForExport, $productsPerQuery);

                $count = count($exportBlocks);
                $counterStep = $count / 100; //каждый 1 процента, сколько это в штуках

                $this->stdout("Blocks for export = {$count}" . PHP_EOL);

                $counterGlobal = 0;
                $counter = 0;
                Console::startProgress(0, $count);
                foreach ($exportBlocks as $exportBlock) {
                    $counterGlobal++;
                    $counter++;

                    if ($counter >= $counterStep || $counterGlobal == $count) {
                        $counter = 0;
                        Console::updateProgress($counterGlobal, $count);
                    }

                    try{
                        \Yii::$app->dbStat->createCommand()->batchInsert(BUFECommDayOnLine::tableName(), ['dt', 'OFFCNT_ID'], $exportBlock)->execute();
                    }catch (Exception $e){
                        var_dump($e->getMessage());
                    }
                }
            }
        }

        $this->stdout("Done" . PHP_EOL, Console::FG_GREEN);

        return true;
    }
}