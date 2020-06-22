<?php


namespace common\helpers;


use common\models\SegmentCardsDisable;
use common\models\Log;
use common\models\SegmentLotsDisable;
use common\models\SegmentProduct;
use common\models\SegmentProductCard;

use Yii;
use yii\base\Exception;

class Segment
{
    //максимальное время, которое сегмент может быть закрыт для обновления
    const MAX_TIME_DISABLED = 6 * 60;

    const CARD_MODE = 'card';
    const LOT_MODE = 'lot';

    public static $countRegenerateSegmentsLimit = 20;

    //todo разная таблица связей сегментов и товаров. либо по карточкам либо по лотам
    public static $mode = self::CARD_MODE;
//    public static $mode = self::LOT_MODE;

    public static $filePath = '@frontend/web/uploads/';

    public static $qtySort = 'qty';

    public static $qtySortName = 'По доступным размерам';

    public static $attributesSegmentProductCards = [
        'card_id',
        'lot_id',
        'segment_id',
        'sort',
        'first',
        'qty'
    ];

    public static $paramProperties = [
        'etalon_clothing_size',
        'etalon_shoe_size',
        'etalon_sock_size',
        'etalon_jewelry_size',
        'etalon_textile_size',
        'etalon_pillow_size',
        'etalon_bed_linen_size',
        'etalon_bra_size',
        'etalon_cap_size',
        'color',
        'brand',
    ];

    public static $sortParams = [
        'default' => 'По популярности',
        'efir' => 'Недавно в эфире',
        'cheap' => 'Сначала дешевые',
        'expensive' => 'Сначала дорогие',
        'sale' => 'По размеру скидки',
//        'popular' => 'Хиты',
//        'recommend' => 'Рекомендуемые',
//        'lucky' => 'Мне повезет',
//        'quantity' => 'По размерам в наличии',
//        'stock' => 'Сток',
//        'new' => 'Новинки'
    ];

    public static function getSortParams()
    {
        $return = self::$sortParams;
        $return[self::$qtySort] = 'По доступным размерав';
        return $return;
    }

    public static function deleteProductCard($lotId, $segmentId)
    {
        $sql = "DELETE FROM " . SegmentProductCard::tableName() . " WHERE segment_id = :segment_id AND lot_id = :lot_id";
        Yii::$app->db->createCommand($sql, [
            ':segment_id' => $segmentId,
            ':lot_id' => $lotId,
        ])->query();
    }

    public static function deleteProduct($lotId, $segmentId)
    {

        $sql = "DELETE FROM " . SegmentProduct::tableName() . " WHERE segment_id = :segment_id AND product_id = :product_id";
        Yii::$app->db->createCommand($sql, [
            ':segment_id' => $segmentId,
            ':product_id' => $lotId,
        ])->query();

    }

    public static function addProductSegmentCard($segmentId, $products, $onlyCanSale = true)
    {
        $rows = [];

        foreach ($products as $data) {
            $cards = \common\models\Product::getProductCardsQuery($data['product_id'], $onlyCanSale);
            foreach ($cards->each() as $card) {
                $cardSizes = Size::getCardSizes($card->id, false);
                $qty = $cardSizes ? count($cardSizes) : 0;

                $rows[$card->id] = [
                    'card_id' => $card->id,
                    'lot_id' => $data['product_id'],
                    'segment_id' => $segmentId,
                    'sort' => $data['sort'],
                    'first' => $data['first'],
                    'qty' => $qty
                ];
            }
        }
        try {
            Yii::$app->db->createCommand()->batchInsert(SegmentProductCard::tableName(), self::$attributesSegmentProductCards, $rows)->execute();
        } catch (\yii\db\Exception $exception) {
            echo $exception->getMessage();
        }
        return count($rows);

    }

    public static function addProductCardSegment($data)
    {
        $rows = [];
        $cards = \common\models\Product::getProductCardsQuery($data['product_id'], true);

        foreach ($cards->each() as $card) {

            $cardSizes = Size::getCardSizes($card->id, false);
            $qty = $cardSizes ? count($cardSizes) : 0;


            $rows[$card->id] = [
                'card_id' => $card->id,
                'lot_id' => $data['product_id'],
                'segment_id' => $data['segment_id'],
                'sort' => $data['sort'],
                'first' => $data['first'],
                'qty' => $qty
            ];
        }
        try {
            Yii::$app->db->createCommand()->batchInsert(SegmentProductCard::tableName(), self::$attributesSegmentProductCards, $rows)->execute();
        } catch (\yii\db\Exception $exception) {
            echo $exception->getMessage();
        }
    }


    public static function addProductSegment($data)
    {
        if (!isset($data['segment_id']) && empty($data['segment_id'])) {
            return true;
        }

        if (!isset($data['product_id']) && empty($data['product_id'])) {
            return true;
        }

        //todo Времянка. поока не реализуем через batch insert
        $sql = "INSERT IGNORE INTO " . SegmentProduct::tableName() . "
            set
            `segment_id` = :segment_id,
            `product_id` = :product_id,
            `sort`  = :sort,
            `first` = :first
            ";

        Yii::$app->db->createCommand($sql, [
            ':segment_id' => $data['segment_id'],
            ':product_id' => $data['product_id'],
            ':sort' => $data['sort'],
            ':first' => $data['first'],
        ])->query();

        return true;
    }

    public static function deleteProductsCards($segmentId)
    {
        $sql = "DELETE FROM " . SegmentProductCard::tableName() . " WHERE segment_id = :segment_id";
        Yii::$app->db->createCommand($sql, [
            ':segment_id' => $segmentId,
        ])->query();
    }

    public static function deleteProducts($segmentId)
    {
        $sql = "DELETE FROM " . SegmentProduct::tableName() . " WHERE segment_id = :segment_id";

        Yii::$app->db->createCommand($sql, [
            ':segment_id' => $segmentId,
        ])->query();
    }

    public static function getLotsSort($firstLotNums)
    {
//        $firstLotNums = explode(',',$firstLotNums);
        $firstLotNums = explode("\n", $firstLotNums);
        $firstLotNums = ArrayHelper::clearArray($firstLotNums);
        $sortLots = [];

        if (count($firstLotNums)) {
            $firstLots = \common\models\Product::find()
                ->select(['id'])
                ->onlyActive()
                ->onlyLot()
                ->andWhere(['IN', 'new_lot_num', $firstLotNums])
                ->asArray()
                ->all();

            $i = count($firstLots) + 1;
            foreach ($firstLots as $row) {
                $sortLots[$row['id']] = $i--;
            }
        }
        return $sortLots;
    }

    //Пересчет товаров которын над (или не надо) отметить как скрываемые в каталоге
    public static function updateCatalogHiddenProducts()
    {
        //1) Отмечаем все товары как не требующий скрытия
        //2) Находим все активные подборки с флагом того что их товары необходимо скрывать из каталога, получаем список товаров и апдейтим их

        $unhideAffected = \common\models\Product::updateAll(['hide_from_catalog' => 0], ['hide_from_catalog' => 1]);

        if (App::isConsoleApplication()) {
            echo "Отменено скрытие из каталога (лоты + карточки) : " . $unhideAffected . PHP_EOL;
        }

        $segments = \common\models\Segment::find()
            ->where([
                'active' => 1,
                'hide_from_catalog' => 1,
            ])
            ->all();

        if (App::isConsoleApplication()) {
            echo "Обновление товаров подборок скрываемых из каталога. Сегментов для скрытия: " . count($segments) .
                ' [' . implode(', ', ArrayHelper::getColumn($segments, 'id')) . ']' . PHP_EOL;
        }

        if ($segments) {
            $productsIds = [];

            /** @var \common\models\Segment $segment */
            foreach ($segments as $segment) {
                if ($segmentProductsIds = $segment->getProductsIds()) {
                    $productsIds = ArrayHelper::merge($productsIds, $segmentProductsIds);
                }
            }

            if ($productsIds) {
                //Обновляем товары
                $productsIds = array_unique($productsIds);

                if (App::isConsoleApplication()) {
                    echo "Лотов для скрытия из каталога: " . count($productsIds) . PHP_EOL;
                }

                //Лоты
                \common\models\Product::updateAll(['hide_from_catalog' => 1], ['content_id' => \common\models\Product::LOT, 'id' => $productsIds]);
                //Карточки лотов
                $cardsAffected = \common\models\Product::updateAll(['hide_from_catalog' => 1], ['content_id' => \common\models\Product::CARD, 'parent_content_element_id' => $productsIds]);

                if (App::isConsoleApplication()) {
                    echo "Связанных карточек скрыто из каталога: " . $cardsAffected . PHP_EOL;
                }
            } else {
                if (App::isConsoleApplication()) {
                    echo "Нет товаров для скрытия из каталога" . PHP_EOL;
                }
            }
        }

        //Скрываем карточки без без картинок
//        Product::hideNoImageCards();

        return true;
    }

    public static function addAdditionalFilterParam($segment)
    {
        $params = [];
        $attributes = $segment->getAttributes();
        foreach ($attributes as $property => $values) {
            if (in_array($property, self::$paramProperties)) {
                $values = unserialize($values);
                if (is_array($values) && count($values)) {
                    $params = array_merge($params, $values);
                }
            }
        }

        return $params;
    }

    public static function sortByQty($id)
    {
        $segmentProducts = SegmentProduct::find()
            ->andWhere(['segment_id' => $id]);

        foreach ($segmentProducts->each() as $model) {
            $lotSizes = Size::getLotSizes($model->product_id, false);
            $qty = $lotSizes ? count($lotSizes) : 0;

            echo 'Update segment sort product ' . $model->product_id . ' qty ' . $qty . PHP_EOL;

            $model->qty = $qty;
            $model->save();

//            self::deleteProduct($data->product_id,$id);
//            self::addProductSegment($data->product_id,$id,$sort);
        }
    }

    public static function addSegmentLotsDisable($modelSegment, $data)
    {
        $count = 0;

        foreach ($data as $lotId) {
            $lot = \common\models\Product::findOne($lotId);
            if ($lot) {
                $model = SegmentLotsDisable::find()
                    ->andWhere(['lot_id' => $lotId])
                    ->andWhere(['segment_id' => $modelSegment->id])
                    ->one();

                if (!$model) {
                    $model = new SegmentLotsDisable();
                    $model->lot_id = $lotId;
                    $model->segment_id = $modelSegment->id;
                    $model->save();
                    $count++;
                }
            }
        }
        Log::add(
            $modelSegment,
            'Скрытие ' . $count . ' лотов для сегмента ' . $modelSegment->id
        );
    }

    public static function deleteSegmentLotsDisable($modelSegment, $data)
    {
        $count = 0;
        foreach ($data as $lotId) {
            $model = SegmentLotsDisable::find()
                ->andWhere(['lot_id' => $lotId])
                ->andWhere(['segment_id' => $modelSegment->id])
                ->one();

            if ($model) {
                $model->delete();
                $count++;
            }
        }
        Log::add(
            $modelSegment,
            'Отмена скрытия ' . $count . ' лотов для сегмента ' . $modelSegment->id
        );
    }

    public static function addSegmentCardsDisable($modelSegment, $data)
    {
        $count = 0;

        foreach ($data as $cardId) {
            $card = \common\models\Product::findOne($cardId);
            if ($card) {
                $model = SegmentCardsDisable::find()
                    ->andWhere(['card_id' => $cardId])
                    ->andWhere(['segment_id' => $modelSegment->id])
                    ->one();

                if (!$model) {
                    $model = new SegmentCardsDisable();
                    $model->card_id = $cardId;
                    $model->lot_id = $card->parent_content_element_id;
                    $model->segment_id = $modelSegment->id;
                    $model->save();
                    $count++;
                }
            }
        }
        Log::add(
            $modelSegment,
            'Скрытие ' . $count . ' цветомоделей для сегмента ' . $modelSegment->id
        );
    }

    public static function deleteSegmentCardsDisable($modelSegment, $data)
    {
        $count = 0;
        foreach ($data as $cardId) {
            $model = SegmentCardsDisable::find()
                ->andWhere(['card_id' => $cardId])
                ->andWhere(['segment_id' => $modelSegment->id])
                ->one();

            if ($model) {
                $model->delete();
                $count++;
            }

        }
        Log::add(
            $modelSegment,
            'Отмена скрытия ' . $count . ' цветомоделей для сегмента ' . $modelSegment->id
        );
    }

    public static function generateLotsFile($segmentId)
    {
        self::$filePath = \Yii::getAlias(self::$filePath);
        $segment = \common\models\Segment::findOne($segmentId);
        if ($segment) {

            $segmentLotsDisable = SegmentLotsDisable::getLotsDisable($segment->id);

            $fileName = $segment->id . '.csv';
            @unlink(self::$filePath . $fileName);
            $csvHandler = fopen(self::$filePath . $fileName, 'w');
            fputcsv($csvHandler, [
                'Id',
                'Lot Num',
            ]);
            if (self::$mode == self::CARD_MODE) {
                $segmentProductCards = SegmentProductCard::find()
                    ->andWhere(['segment_id' => $segment->id])
                    ->groupBy('lot_id');


                foreach ($segmentProductCards->each() as $segmentProductCard) {
                    $product = \common\models\Product::findOne($segmentProductCard->lot_id);
                    if ($product) {
                        if (!in_array($product->id, $segmentLotsDisable)) {
                            $csvRow = [$product->id, $product->new_lot_num];
                            fputcsv($csvHandler, $csvRow);
                        }
                    }
                }
            } else {
                $segmentProducts = SegmentProduct::find()
                    ->andWhere(['segment_id' => $segment->id])
                    ->groupBy('product_id');


                foreach ($segmentProducts->each() as $segmentProduct) {
                    $product = \common\models\Product::findOne($segmentProduct->product_id);
                    if ($product) {
                        if (!in_array($product->id, $segmentLotsDisable)) {
                            $csvRow = [$product->id, $product->new_lot_num];
                            fputcsv($csvHandler, $csvRow);
                        }
                    }
                }
            }
            fclose($csvHandler);

            return self::$filePath . $fileName;

        }
        return null;

    }

    public static function rebuildSegmentSplit($segmentId)
    {
        $modelSegment = \common\models\Segment::findOne($segmentId);
        if ($modelSegment) {

            Log::add(
                $modelSegment,
                'Старт расщипления лотов на карточки для сегмента ' . $modelSegment->id
            );

            Common::startTimer("rebuildSegmentSplit-" . $modelSegment->id);

            self::setDisableMode($modelSegment, \common\helpers\Common::BOOL_Y_INT);

            $segmentProducts = SegmentProduct::find()
                ->andWhere(['segment_id' => $segmentId]);

            $products = [];
            foreach ($segmentProducts->each() as $model) {
                $products[$model->id] = [
                    'product_id' => $model->product_id,
                    'sort' => $model->sort,
                    'first' => $model->first,
                ];
            }
            if (count($products)) {
                Log::add(
                    $modelSegment,
                    'Подсчет количества карточек файлового сегмента ' . $modelSegment->id . ' 
                    Количество карточек: ' . count($products)
                );
            } else {
                Log::add(
                    $modelSegment,
                    'Ошибка. Для файлового сегмента ' . $modelSegment->id . ' не найдено карточек',
                    Log::MESSAGE_TYPE_ERROR

                );
            }

            self::deleteProductsCards($segmentId);
            $count = self::addProductSegmentCard($segmentId, $products, false);

            if ($count > 0) {
                Log::add(
                    $modelSegment,
                    'Конец расщипления лотов на карточки для сегмента ' . $modelSegment->id . '. Количество товаров: ' . $count
                );
            } else {
                Log::add(
                    $modelSegment,
                    'Ошибка расщипления лотов на карточки для сегмента ' . $modelSegment->id . '. 0 Количество товаров',
                    Log::MESSAGE_TYPE_ERROR
                );
            }

            self::setDisableMode($modelSegment, \common\helpers\Common::BOOL_N_INT);

            $time = Common::getTimerTime("rebuildSegmentSplit-" . $modelSegment->id, false);

            Log::add(
                $modelSegment,
                'Время расщипления лотов на карточки сегмента : ' . round($time, 2) . 'c'
            );

            return $count;
        } else {
            Log::add(
                $modelSegment,
                'Ошибка. Не найден сегмент ' . $modelSegment->id,
                Log::MESSAGE_TYPE_ERROR
            );
            return false;
        }
    }

    public static function setDisableMode($model, $disabled = 1)
    {
        if ($model) {

            $sql = "UPDATE `" . \common\models\Segment::tableName() . "`
            SET  `disabled` = :disabled 
            WHERE `id` = :id";

            Yii::$app->db->createCommand($sql, [
                ':id' => $model->id,
                ':disabled' => $disabled,
            ])->execute();

            if ($disabled) {
                $text = 'Закрываем для редактирования сегмент ' . $model->id;
            } else {
                $text = 'Открываем для редактирования сегмент ' . $model->id;
            }

            Log::add(
                $model,
                $text
            );
        }

        return true;
    }

    public static function removeFirstLots($segmentId)
    {
        if (self::$mode == self::CARD_MODE) {
            SegmentProductCard::updateAll(['first' => 0], ['segment_id' => $segmentId]);
        } else {
            SegmentProduct::updateAll(['first' => 0], ['segment_id' => $segmentId]);
        }
    }

    public static function setPromoProducts(\common\models\Segment $segment)
    {
        $consoleApp = \common\helpers\App::isConsoleApplication();

        if (!$segment || !$segment->id) {
            throw new Exception('segment model does not exist');
        }
        if ($segment->generated) {
            throw new Exception('segment goods are generated from a file');
        }
        if ($consoleApp) {
            echo 'Start set segment products. Segment id:' . $segment->id . ', name:' . $segment->name . PHP_EOL;
        }

        Log::add(
            $segment,
            'Старт добавления товаров для сегмента ' . $segment->id
        );

        Common::startTimer("setPromoProducts-" . $segment->id);

        $products = $segment->buildProductsList();
        if (count($products)) {
            $segment->products = $products;
            Log::add(
                $segment,
                'Подсчет количества лотов, соответстующих критериям для 
                    автоматического сегмента ' . $segment->id . ' Количество лотов: ' . count($segment->products)
            );
        } else {
            $segment->products = [];
            Log::add(
                $segment,
                'Ошибка. Для автоматического сегмента ' . $segment->id . ' не найдено лотов, соответствующих критериям',
                Log::MESSAGE_TYPE_ERROR

            );
        }
        $count = 0;

        self::setDisableMode($segment, \common\helpers\Common::BOOL_Y_INT);

        if (self::$mode == self::CARD_MODE) {
            self::deleteProductsCards($segment->id);
            $count = self::addProductSegmentCard($segment->id, $segment->products);
            if ($consoleApp) {
                echo 'End set segment products. Segment id:' . $segment->id . ', name:' . $segment->name . ', count cards: ' . $count . PHP_EOL;
            }
            if ($count > 0) {
                Log::add(
                    $segment,
                    'Конец добавления карточек для автоматического сегмента ' . $segment->id . '. Карточек добавлено: ' . $count
                );
            } else {
                Log::add(
                    $segment,
                    'Ошибка добавления карточек для автоматического сегмента ' . $segment->id . '. 0 карточек добавлено',
                    Log::MESSAGE_TYPE_ERROR
                );
            }
        } else {
            self::deleteProducts($segment->id);
            foreach ($segment->products as $data) {

                //todo не выводить лишнюю информацию в консоль, пределеть через progress bar
//                    $this->stdout('Insert product:' . $data['product_id'] . ', segment id:' . $segment->id . ' sort: ' . $data['sort'] . ' first: ' . $data['first'], PHP_EOL);

                self::addProductSegment([
                    'product_id' => $data['product_id'],
                    'segment_id' => $segment->id,
                    'sort' => $data['sort'],
                    'first' => $data['first']
                ]);
                $count++;
            }
            if ($consoleApp) {
                echo 'End set segment products. Segment id:' . $segment->id . ', name:' . $segment->name . ', count lots: ' . $count . PHP_EOL;
            }
            if ($count > 0) {
                Log::add(
                    $segment,
                    'Конец добавления лотов для автоматического сегмента ' . $segment->id . '. Лотов добавлено: ' . $count
                );
            } else {
                Log::add(
                    $segment,
                    'Ошибка добавления лотов для автоматического сегмента ' . $segment->id . '. 0 лотов добавлено',
                    Log::MESSAGE_TYPE_ERROR
                );
            }
        }
        self::setDisableMode($segment, \common\helpers\Common::BOOL_N_INT);

        $time = Common::getTimerTime("setPromoProducts-" . $segment->id, false);

        Log::add(
            $segment,
            'Время генерации сегмента: ' . round($time, 2) . 'c'
        );

        return true;
    }

    public static function isDisabledMaxTimeExceeded($model)
    {
        $flag = false;
        $timeDiff = time() - $model->updated_at;
        if ($timeDiff > self::MAX_TIME_DISABLED) {
            self::setDisableMode($model, \common\helpers\Common::BOOL_N_INT);
            Log::add(
                $model,
                'Ошибка. Принудительная разблокировка зависшего сегмента ' . $model->id,
                Log::MESSAGE_TYPE_ERROR
            );
            $flag = true;
        }
        return $flag;
    }
}