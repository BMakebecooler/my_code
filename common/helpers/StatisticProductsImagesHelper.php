<?php


namespace common\helpers;


use common\models\Product;
use common\models\StatisticProductsImages;

class StatisticProductsImagesHelper
{

//    public static $filePath = '/app/frontend/web/uploads/';

    public static $filePath = '@frontend/web/uploads/';

    public static $fileName = 'lots_cards_without_photo.csv';


    public static function getCountAllCardsWithoutImages($idLot = null)
    {
        $query = Product::find()
            ->onlyCard()
            ->imageIdIsNull()
            ->onlyActive();

        if ($idLot) {
            $query->andWhere([Product::tableName() . '.parent_content_element_id' => $idLot]);
        }

        return $query->count();
    }

    public static function getCountAllCardsWithoutImagesStock($idLot = null)
    {
        $query = Product::find()
            ->onlyCard()
            ->imageIdIsNull()
            ->onlyActive()
            ->hasQuantityNew()
            ->priceMoreThanZeroNew()
            ->onlyPublicForCardsNew()
            ->treeNotNull();

        if ($idLot) {
            $query->andWhere([Product::tableName() . '.parent_content_element_id' => $idLot]);
        }

        return $query->count();
    }

    public static function setProductsWithoutImagesCount()
    {
        if (App::isConsoleApplication()) {
            echo 'Получаем все карточки без фото', PHP_EOL;
        }

        $countAll = StatisticProductsImagesHelper::getCountAllCardsWithoutImages();
        $countAllStock = StatisticProductsImagesHelper::getCountAllCardsWithoutImagesStock();

        if (App::isConsoleApplication()) {
            echo 'Сохраняем данные в базу ', PHP_EOL;
        }

        $model = new StatisticProductsImages();
        $model->count_all = $countAll;
        $model->count_all_stock = $countAllStock;
        $model->save();
    }


    /**
     * метод генерации файла с номерами лотов у которых есть карточки без фото
     */
    public static function generateFileLotsWithoutImages()
    {
        self::$filePath = \Yii::getAlias(self::$filePath);

        $query = Product::find()
            ->select([
                'parent_element.new_lot_num',
                Product::tableName() . '.parent_content_element_id'
            ])
            ->onlyCard()
            ->imageIdIsNull()
            ->onlyActive()
            ->onlyActiveParent()
//            ->limit(200)
            ->groupBy(Product::tableName() . '.parent_content_element_id');

        $count = $query->count();
        if (App::isConsoleApplication()) {
            echo "Count Lots {$count} " . PHP_EOL;
        }

//        $csv = "Номер лота,Количестов карточек без фото,количество карточек в наличие \n";//Column headers
        @unlink(self::$filePath . self::$fileName);
        $csvHandler = fopen(self::$filePath . self::$fileName, 'w');
        fputcsv($csvHandler, [
            'Lot Num',
            'Count without photo',
            'Count without photo Stock'
        ]);
//        fwrite($csvHandler, $csv);

        foreach ($query->each() as $card) {
            $card->parent_content_element_id;

            $countStock = self::getCountAllCardsWithoutImagesStock($card->parent_content_element_id);
            $countAll = self::getCountAllCardsWithoutImages($card->parent_content_element_id);

//            if (App::isConsoleApplication()) {
//                echo "Lot Id {$card->parent_content_element_id}, num {$card->new_lot_num} count _all: {$countAll} count_all_stock: {$countStock}" . PHP_EOL;
//            }

            $csvRow = [$card->new_lot_num, $countAll, $countStock];
            fputcsv($csvHandler, $csvRow);
//            fwrite($csvHandler, $csv);
        }
        if (App::isConsoleApplication()) {
            echo 'Файл ' . self::$fileName . ' отчета был сгенерирован ', PHP_EOL;
        }

        fclose($csvHandler);

    }
}