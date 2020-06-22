<?php

namespace common\models\filtered\mediaplan;

use modules\shopandshow\lists\Onair as OnairList;
use modules\shopandshow\models\mediaplan\AirDayProductTime;
use yii\data\ActiveDataProvider;

class OnAirCategories extends OnAir
{

    public function init()
    {
        parent::init();
    }

    /**
     * @return array|AirDayProductTime[]
     */
    public function getCategories()
    {
        return OnairList::categories($this->timestamp);
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