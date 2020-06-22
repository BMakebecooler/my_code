<?php
/**
 * Created by PhpStorm.
 * User: andrei
 * Date: 2019-03-18
 * Time: 21:15
 */

namespace common\models\OnAir;

use common\helpers\App;
use common\helpers\ArrayHelper;
use common\models\NewProduct;
use common\models\Product;
use common\models\query\CmsContentElementQuery;
use common\models\ShopProductStatistic;
use common\helpers\Dates;
use modules\shopandshow\models\mediaplan\AirDayProductTime;
use modules\shopandshow\models\shop\ShopProduct;
use skeeks\cms\helpers\UrlHelper;
use yii\base\Object;


/**
 * Class OnAir
 * @package common\models\OnAir
 */
class OnAirSchedule extends Object
{
    const LOT_DEFAULT_LIMIT = 20;

    const LOT_SLIDER_LIMIT = 80;

    public $date;

    private $_timeLine = [];

    public static function create($date)
    {
        return new static([
            'date' => $date
        ]);
    }

    public  function getCategory()
    {
        $order = [
            1626,
            1623,
            1622,
            1621,
            1627,
            1625,
            1629,
            1628,
        ];

        return AirDayProductTime::getDb()->cache(function ($db) use ( $order) {
            return AirDayProductTime::find()
                ->select(['ss_mediaplan_air_day_product_time.*', 'count(DISTINCT lot_id) AS count_product'])
//                ->leftJoin('shop_product', 'shop_product.id = ss_mediaplan_air_day_product_time.lot_id')
                ->leftJoin('ss_shop_product_prices', 'ss_shop_product_prices.product_id = ss_mediaplan_air_day_product_time.lot_id')
                ->andWhere(
                    'begin_datetime >= :begin_datetime 
                             AND begin_datetime <= :end_datetime 
                             AND new_quantity > 0
                             AND ss_shop_product_prices.price > 0', [
                    ':begin_datetime' => Dates::beginOfDate($this->date),
                    ':end_datetime' => Dates::endOfDate($this->date),
                ])
                ->groupBy('section_id')
                ->orderBy([new \yii\db\Expression('FIELD (section_id, ' . join(',', $order) . ')')])
                ->having('count_product > 2')
                ->all();
        }, MIN_10);
    }

    /**
     * //получаем эфирное время дня.
     * @return array
     */
    public function getTimeLine()
    {
        if(empty($this->_timeLine)) {
            $beginOfDate = Dates::beginOfDate($this->date);
            $endOfDate = strtotime('+1day -1second', $beginOfDate);

            $mediaPlanAirBlocks = MediaplanAirBlock::find()
                ->where([
                    'AND',
                    ['>=', 'begin_datetime', $beginOfDate,],
                    ['<=', 'end_datetime', $endOfDate],
                ])
                ->groupBy(['begin_datetime', 'end_datetime'])
                ->orderBy(['begin_datetime' => SORT_ASC])
                ->all();

            $timeLine = [];
            if($mediaPlanAirBlocks) {
                foreach ($mediaPlanAirBlocks as $mediaplanAirBlock) {
                    $timeLine[] = $this->populateTimeLine($mediaplanAirBlock);
                }
            }
            $this->_timeLine = $timeLine;
        }

        return $this->_timeLine;
    }

    public function getProductsByBlock($cardMod = false)
    {
        $cardMod = true;

        //эфирное время мапим по блокам
        $blockIds = ArrayHelper::map($this->getTimeLine(), 'block_id', 'hour_efir');

        //получаем лоты в этих блоках
        $lots = MediaplanAirDayProductTime::find()
            ->select([
                'lot_id',
                'block_id',
                Product::tableName().'.name',
                Product::tableName().'.new_lot_name'
            ])
            ->leftJoin(Product::tableName(),Product::tableName().'.id = '.MediaplanAirDayProductTime::tableName().'.lot_id')
            ->where(['IN', 'block_id', array_keys($blockIds)])
            ->orderBy(['begin_datetime' => SORT_ASC])
            ->asArray()
            ->all();

        $lotNmaes = [];
        foreach ($lots as $lot){
            $lotNmaes[$lot['lot_id']] = $lot['name'];
        }

        //получаем уникальные ключи лотов и по ним дергоме контент.
        $lotIds = [];
        foreach ($lots as $lot) {
            $lotIds[$lot['lot_id']] =  $lot['lot_id'];
        }

        /** @var CmsContentElementQuery $productsQuery */
        $productsQuery = Product::getQueryForFeed();

        if($cardMod){
            $productsQuery
                ->andWhere([
                    'IN',
                    Product::tableName() . '.parent_content_element_id',
                    $lotIds
                ])
//                ->canSale()
//            ->onlyPublic()
                ->onlyCard()
                ->onlyActive()
//            ->available()
                ->indexBy('id');
        }else {

//        $products = NewProduct::getQueryForFeed()

            $productsQuery
                ->andWhere([
                    'IN',
                    Product::tableName() . '.id',
                    $lotIds
                ])
                ->canSale()
//            ->onlyPublic()
//            ->onlyLot()
//            ->available()
                ->indexBy('id');
        }

            $products = $productsQuery->all();

            $bestsellers = ShopProductStatistic::find()
                ->where(['IN', 'id', $lotIds])
                ->onlyBestseller()
                ->select('id')
                ->column();

            $items = [];
            /** @var Product $product */
            foreach ($products as $product) {
                //$shopProduct = ShopProduct::getInstanceByContentElement($product);

                $data =  [
                    'id' => $product->id,
                    'hourEfir' => null,
                    'image' => $product->getThumbnail(),
                    'basePrice' => (int)$product->new_price, // $shopProduct->getBasePriceMoney(),
                    'maxPriceMoney' => (int)$product->new_price_old, //$shopProduct->getMaxPriceMoney(),
                    'url' => $product->getUrl(),
                    'name' => $product->name,
                    'isDiscount' => $product->hasDiscount(), //$shopProduct->isDiscount(),
                    'isBestseller' => in_array($product->id, $bestsellers),
                ];

                if($cardMod){
                    $data['lot_id'] = $product->parent_content_element_id;
                    $data['name'] = $lotNmaes[$product->parent_content_element_id] ?? $data['name'];
                    $items[$product->parent_content_element_id][] = $data;
                }else {
                    $items[$product->id] = $data;
                }
            }

            $products = [];
            if($cardMod){
                foreach ($lots as $lot) {
                    if(isset($items[$lot['lot_id']])){
                        foreach ($items[$lot['lot_id']] as $product){
                            if(!$product['image']){
                                continue;
                            }
                            $product['hourEfir'] = ArrayHelper::getValue($blockIds, $lot['block_id']);
                            $products[] = $product;
                        }
                    }
                }
            }else {
                foreach ($lots as $lot) {
                    $product = ArrayHelper::getValue($items, $lot['lot_id']);

                    if ($product) {
                        $product['hourEfir'] = ArrayHelper::getValue($blockIds, $lot['block_id']);
                        $products[] = $product;
                    }
                }
            }

            ArrayHelper::multisort($products, 'hourEfir');


        return $products;
    }

    /**
     * @param MediaplanAirBlock $schedule
     * @return array
     */
    protected function populateTimeLine(MediaplanAirBlock $schedule)
    {
        $isConsole = App::isConsoleApplication();

        $pathInfo = !$isConsole ? \Yii::$app->request->pathInfo : '';
        $all =  !$isConsole ? \Yii::$app->request->get('all') : null;

        return [
            'id' => $schedule->id,
            'active' => $schedule->isActiveTime() ? 'active' : '',
            'block_id' => $schedule->block_id,
            'url' => UrlHelper::construct($pathInfo, [
                'category' => $schedule->section_id,
                'block' => $schedule->block_id,
                'time' => $schedule->id,
                'all' => $all,
            ]),
            'tree_id' => $schedule->section_id,
            'name' => $schedule->section_name,
            'time' => date('H:i', $schedule->begin_datetime) . ' - ' . date('H:i', $schedule->end_datetime),
            'hour_efir' => (int)date('H', $schedule->begin_datetime),
            'block' => $schedule->block_id,
        ];

    }

    /**
     * @return array
     */
    public function make()
    {
        return [
            'timeLine' => $this->getTimeLine(),
            'products' => $this->getProductsByBlock(),
        ];
    }
}

