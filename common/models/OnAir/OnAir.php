<?php
/**
 * Created by PhpStorm.
 * User: andrei
 * Date: 2019-03-18
 * Time: 21:15
 */

namespace common\models\OnAir;

use common\helpers\ArrayHelper;
use common\models\NewProduct;
use DateInterval;
use DateTime;
use common\helpers\Common;
use common\helpers\Dates;
use common\models\generated\models\SsMediaplanAirBlocks as AirBlock;
use modules\shopandshow\models\shop\ShopProduct;
use skeeks\cms\helpers\UrlHelper;


/**
 * Class OnAir
 * @package common\models\OnAir
 */
class OnAir extends Common
{
    const LOT_DEFAULT_LIMIT = 20;

    public static function findCategoryByDate($date)
    {


        /**
         * SELECT `ss_mediaplan_air_day_product_time`.*, count(DISTINCT lot_id) AS count_product
         * FROM `ss_mediaplan_air_day_product_time`
         * LEFT JOIN `shop_product` ON shop_product.id = ss_mediaplan_air_day_product_time.lot_id
         * LEFT JOIN `ss_shop_product_prices`
         * ON ss_shop_product_prices.product_id = ss_mediaplan_air_day_product_time.lot_id
         * WHERE begin_datetime >= 1555966800 AND begin_datetime <= 1556053200 AND shop_product.quantity > 0
         * AND ss_shop_product_prices.price > 0 GROUP BY `section_id` HAVING count_product
         * > 2
         * ORDER BY FIELD (section_id, 1626,1623,1622,1621,1627,1625,1629,1628)
         */
    }

    /**
     * @param $date
     * @return array|AirBlock[]|MediaplanAirBlock[]
     */
    public static function findTimeLineByDate($date)
    {
        $beginOfDate = Dates::beginOfDate(strtotime($date));
        $endOfDate = strtotime('+1day -1second', $beginOfDate);

        return MediaplanAirBlock::find()
            ->where([
                'AND',
                ['>=', 'begin_datetime', $beginOfDate,],
                ['<=', 'end_datetime', $endOfDate],
            ])
            ->groupBy(['begin_datetime', 'end_datetime'])
            ->orderBy(['begin_datetime' => SORT_ASC])
            ->all();
    }

    public static function normalizeTimeLine($timeLine)
    {
        $schedules = [];
        foreach ($timeLine as $schedule) {
            $schedules[] = [
                'id' => $schedule->id,
                'active' => $schedule->isActiveTime() ? 'active' : '',
                'block_id' => $schedule->block_id,
                'url' => UrlHelper::construct(\Yii::$app->request->pathInfo, [
                    'category' => $schedule->section_id,
                    'block' => $schedule->block_id,
                    'time' => $schedule->id,
                    'all' => \Yii::$app->request->get('all'),
                ]),
                'tree_id' => $schedule->section_id,
                'name' => $schedule->section_name,
                'time' => date('H:i', $schedule->begin_datetime) . ' - ' . date('H:i', $schedule->end_datetime),
                'hour_efir' => (int)date('H', $schedule->begin_datetime),
                'block' => $schedule->block_id,
            ];
        }
        return $schedules;

    }

    /**
     * Поиск товаров по блоку или категории
     *
     * @param null $blockId
     * @param null $sectionId
     * @param int $limit
     * @return array|NewProduct[]|\modules\shopandshow\models\mediaplan\AirBlock[]|\yii\db\ActiveRecord[]
     */
    public static function findProduct($blockId = null, $sectionId = null, $limit = self::LOT_DEFAULT_LIMIT)
    {
        $lotIds = self::findLotIdsByCondition([
            'blockId' => $blockId,
            'sectionId' => $sectionId
        ], $limit);

        return self::findAllProductByLotId($lotIds);
    }

    /**
     * Поиск товаров по времени
     * @param DateTime $min
     * @param DateTime $max
     * @param int $limit
     * @return array|NewProduct[]|\modules\shopandshow\models\mediaplan\AirBlock[]|\yii\db\ActiveRecord[]
     */
    public static function findProductByDateRange(DateTime $min, DateTime $max, $limit = self::LOT_DEFAULT_LIMIT)
    {
        $lotIds = self::findLotIdsByCondition([
            'dateRange' => [
                'min' => $min->format('U'),
                'max' => $max->format('U'),
            ]
        ], $limit);

        return self::findAllProductByLotId($lotIds);
    }

    /**
     * @param array $lotIds
     * @return array|NewProduct[]|\modules\shopandshow\models\mediaplan\AirBlock[]|\yii\db\ActiveRecord[]
     */
    public static function findAllProductByLotId(array $lotIds = [])
    {
        if (empty($lotIds)) {
            return [];
        }
        return NewProduct::getQueryForFeed()
            ->where([
                'IN',
                NewProduct::tableName() . '.id',
                $lotIds
            ])
            ->onlyPublic()
            ->onlyLot()
            ->available()
            ->all();
    }

    /**
     * Получаем id  лотов в эфире по заданным фильтрам
     * @param array $condition
     * ```php
     *  $condition = [
     *      'dateRange' => [
     *          'min' , 'max'
     *      ],
     *      blockId => (int),
     *      sectionId => (int),
     *  ]
     * ```
     * @param int $limit
     * @return array
     */
    public static function findLotIdsByCondition(array $condition = [], $limit = self::LOT_DEFAULT_LIMIT)
    {
        $query = MediaplanAirDayProductTime::find()
            ->select(['lot_id'])
            ->distinct()
            ->innerJoinWith(['mediaplanAirBlock']);

        /**
         * фильтр по времени показа
         */
        if (key_exists('dateRange', $condition)) {
            $query->beginDatetimeBetween($condition['dateRange']['min'], $condition['dateRange']['max']);
        }

        /**
         * по блоку
         */
        if (!empty($condition['blockId'])) {
            $query->block($condition['blockId']);
        }

        /**
         * по секции | категории
         */
        if (!empty($condition['sectionId'])) {
            $query->section($condition['sectionId']);
        }

        if ($limit) {
            $query->limit($limit);
        }

        return $query
            ->orderBy([
                MediaplanAirDayProductTime::tableName() . '.begin_datetime' => SORT_ASC
            ])
            ->column();
    }

    /**
     * @param null $blockId
     * @param null $sectionId
     * @param int $limit
     * @return array|NewProduct[]|\modules\shopandshow\models\mediaplan\AirBlock[]|\yii\db\ActiveRecord[]
     * @throws \Exception
     */
    public static function findProductByBlockIdAndSectionId(
        $blockId = null,
        $sectionId = null,
        $limit = self::LOT_DEFAULT_LIMIT
    ) {
        if (empty($blockId) && empty($sectionId)) {

            $curDateBegin = (new DateTime())
                ->format('Y-m-d H:00:00');

            $curDateEnd = (new DateTime())
                ->add(new DateInterval('PT1H'))
                ->format('Y-m-d H:00:00');

            $products = OnAir::findProductByDateRange(new DateTime($curDateBegin), new DateTime($curDateEnd), $limit);

        } elseif ($sectionId && !$blockId) {
            $products = OnAir::findProduct(null, $sectionId, $limit);
        } else {
            $products = OnAir::findProduct($blockId, null, $limit);
        }
        return $products;
    }

    /**
     * @param $date
     * @throws \Exception
     */
    public static function schedule($date = null)
    {
        if(!$date) {
            $date = time();
        }
        //получаем эфирное время дня.
        $timeLine = self::normalizeTimeLine(self::findTimeLineByDate($date));

        //эфирное время мапим по блокам
        $blockIds = ArrayHelper::map($timeLine, 'block_id', 'hour_efir');

        //получаем лоты в этих блоках
        $lots = MediaplanAirDayProductTime::find()
            ->select(['lot_id', 'block_id'])
            ->where(['IN', 'block_id', array_keys($blockIds)])
            ->orderBy(['begin_datetime' => SORT_ASC]) 
            ->asArray()
            ->all();

        //получаем уникальные ключи лотов и по ним дергоме контент.
        $lotIds = [];
        foreach ($lots as $lot) {
            $lotIds[$lot['lot_id']] =  $lot['lot_id'];
        }

        $products = NewProduct::getQueryForFeed()
            ->where([
                'IN',
                NewProduct::tableName() . '.id',
                $lotIds
            ])
            ->onlyPublic()
            ->onlyLot()
            ->available()
            ->indexBy('id')
            ->all();


        foreach ($products as $product) {
            $shopProduct = ShopProduct::getInstanceByContentElement($product);

        }

    }
}

