<?php
/**
 * Created by PhpStorm.
 * User: andrei
 * Date: 2019-03-18
 * Time: 19:59
 */

namespace common\models\OnAir;


use common\helpers\Dates;
use modules\shopandshow\models\shop\ShopProduct;
use skeeks\cms\shop\models\ShopProductPrice;
use modules\shopandshow\models\mediaplan\AirDayProductTime;
use yii\base\Object;
use yii\data\ActiveDataProvider;
use \yii\db\Expression;

class ProductCategory extends Object
{
    /**
     * это id из старого кода
     */
    /*const ID_KITCHEN = 1621; //Кухня
    const ID_HOME = 1622; //Дом
    const ID_JEWELERY = 1623; //Украшения
    const ID_BEAUTY_AND_FITNESS = 1625; //
    const ID_FASHION = 1626; //Мода
    const ID_ELECTRONICS = 1627; //Электроника
    const ID_HEALTH = 1628; //Здоровье
    const ID_GARDEN = 1629; //Сад и огород*/

    const ID_FASHION = 1626; //Мода
    const ID_FOOTWEAR = 1649; //Обувь
    const ID_HOME = 1622; //Дом
    const ID_JEWELERY = 1623; //Украшения
    const ID_BEAUTY = 1932; //Красота
    const ID_KITCHEN = 1931; //Кухня
    const ID_HOBBY = 1958; //Хобби
    const ID_ELECTRONICS = 1937; //Электроника
    const ID_DRESSES = 1872; //Платья
    const ID_SHOES = 1781; //Туфли
    const ID_TROUSERS = 1673; //Брюки
    const ID_LAST_SIZE = 2126; //Последний размер

    /**
     *     const ID_FASHION = 1626; //Мода
     */

    /**
     * @param int $timestamp
     * @return AirDayProductTime[]
     * @throws \Throwable
     */
    public static function findByBeginDateTime($timestamp)
    {
        $order = [
            static::ID_FASHION,
            static::ID_JEWELERY,
            static::ID_HOME,
            static::ID_KITCHEN,
            static::ID_ELECTRONICS,
            static::ID_HOBBY,
            static::ID_BEAUTY,
            static::ID_FOOTWEAR,
        ];

        return AirDayProductTime::getDb()->cache(function ($db) use ($timestamp, $order) {
            return AirDayProductTime::find()
                ->alias('air')
                ->select([
                    'air.*',
                    'count_product' => 'count(DISTINCT lot_id)'
                ])
                ->leftJoin(['sp' => ShopProduct::tableName()], 'sp.id = air.lot_id')
                ->leftJoin(['sp_prices' => ShopProductPrice::tableName()], 'sp_prices.product_id = air.lot_id')
                ->where(['BETWEEN', 'begin_datetime', Dates::beginOfDate($timestamp), Dates::endOfDate($timestamp)])
                ->andWhere(['>', 'sp.quantity', 0])
                ->andWhere(['>', 'sp_prices.price', 0])
                ->groupBy('section_id')
                ->orderBy([new Expression('FIELD (section_id, ' . implode(',', $order) . ')')])
                ->having(['>', 'count_product', 2])
                ->all();
        }, MIN_10);
    }

    /**
     * @param $categoryName
     * @param ActiveDataProvider $activeDataProvider
     * @return $this
     */
    public function searchByCategoryName($categoryName, ActiveDataProvider $activeDataProvider)
    {
        $query = $activeDataProvider->query;

        /**
         * @var $query \yii\db\ActiveQuery
         */

        if (!$this->isBeforeYesterday() && !$this->isYesterday() && !$this->isToday()) {
            $this->defaultDate();
        }

        $query->andWhere('begin_datetime >= :begin_datetime AND end_datetime <= :end_datetime ', [
            ':begin_datetime' => $this->beginOfDate(),
            ':end_datetime' => $this->endOfDate(),
        ])
            ->andWhere('section_name = :section_name', [
                ':section_name' => $categoryName,
            ])
            ->orderBy('begin_datetime DESC');

        return $this;
    }

    /**
     * @param $categoryName
     * @param ActiveDataProvider $activeDataProvider
     * @return $this
     */
    public function search(ActiveDataProvider $activeDataProvider)
    {
        $query = $activeDataProvider->query;

        /**
         * @var $query \yii\db\ActiveQuery
         */

        if (!$this->isBeforeYesterday() && !$this->isYesterday() && !$this->isToday()) {
            $this->defaultDate();
        }

        $query->innerJoinWith(['shopContentElement']);

        $query->andWhere('begin_datetime >= :begin_datetime AND end_datetime <= :end_datetime ', [
            ':begin_datetime' => $this->beginOfDate(),
            ':end_datetime' => $this->endOfDate(),
        ]);

        if ($this->category) {
            $query->andWhere('section_id = :section_id', [
                ':section_id' => $this->category,
            ]);
        }

        $query->orderBy('begin_datetime DESC');
        $query->groupBy('lot_id');

        return $this;
    }

    /**
     * Поиск с группировкой по часам
     * @param $categoryName
     * @param ActiveDataProvider $activeDataProvider
     * @return $this
     */
    public function searchByCategoryNameHourGroup($categoryName, ActiveDataProvider $activeDataProvider)
    {
        $query = $activeDataProvider->query;

        /**
         * @var $query \yii\db\ActiveQuery
         */

        if (!$this->isBeforeYesterday() && !$this->isYesterday() && !$this->isToday()) {
            $this->defaultDate();
        }

        $query->innerJoinWith(['shopContentElement']);

        $query->andWhere('begin_datetime >= :begin_datetime AND end_datetime <= :end_datetime ', [
            ':begin_datetime' => $this->beginOfDate(),
            ':end_datetime' => $this->endOfDate(),
        ])
            ->andWhere('section_name = :section_name', [
                ':section_name' => $categoryName,
            ])
            ->groupBy('lot_id')
            ->orderBy('HOUR(FROM_UNIXTIME(begin_datetime)) ASC');

        return $this;
    }


    /**
     * Поиск с группировкой по часам
     * @param ActiveDataProvider $activeDataProvider
     * @return $this
     */
    public function searchByHourGroup(ActiveDataProvider $activeDataProvider)
    {
        $query = $activeDataProvider->query;

        /**
         * @var $query \yii\db\ActiveQuery
         */

        if (!$this->isBeforeYesterday() && !$this->isYesterday() && !$this->isToday()) {
            $this->defaultDate();
        }

//        $query->innerJoinWith(['shopContentElement']);
        $query->andWhere('begin_datetime >= :begin_datetime AND end_datetime <= :end_datetime ', [
            ':begin_datetime' => $this->beginOfDateNoAirBlock(),
            ':end_datetime' => $this->endOfDateNoAirBlock(),
        ]);

        if ($this->category) {
            $query->andWhere('section_id = :section_id', [
                ':section_id' => $this->category,
            ]);
        }

        /*if ($this->time) {
            $query->andWhere('id = :block_id', [
                ':block_id' => $this->time,
            ]);
        }*/

        //->andWhere('ss_mediaplan_schedule_items.type = :type', ['type' => MediaPlanScheduleItem::TYPE_CLIP])
        //->groupBy('section_id, HOUR(FROM_UNIXTIME(begin_datetime))')
//            ->orderBy('HOUR(FROM_UNIXTIME(begin_datetime)) ASC')

        $query->groupBy('begin_datetime, end_datetime');
        $query->orderBy('id ASC');

        return $this;
    }


}