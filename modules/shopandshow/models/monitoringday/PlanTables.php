<?php

namespace modules\shopandshow\models\monitoringday;

use common\lists\TreeList;
use skeeks\cms\components\Cms;

class PlanTables extends \yii\base\Model
{
    public $date_from;
    public $date_to;
    public $showCts = 0;
    public $useOffset = 0;
    public $dateTimeOffset = 0;

    public function init()
    {
        parent::init();

        if (!$this->date_from) {
            $this->date_from = date('Y-m-d');
        }

        if (!$this->date_to) {
            $this->date_to = date('Y-m-d');
        }

    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['date_from', 'date_to'], 'required'],
            [['date_from', 'date_to'], 'string'],
            [['showCts', 'useOffset'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'date_from' => 'Дата с',
            'date_to' => 'Дата по',
            'showCts' => 'Товар ЦТС отдельной строкой (если дата с = дата по)',
            'useOffset' => 'Использовать время с 08 до 08',
        ];
    }

    public function isOneDay()
    {
        return $this->date_from == $this->date_to;
    }

    /**
     * Список категорий для дополнительного отчета
     * @return \skeeks\cms\models\Tree[]
     */
    public function getCategories()
    {
        return TreeList::getTreeById(TreeList::CATALOG_ID)->getChildren()->onCondition(['active' => Cms::BOOL_Y])->all();
    }

    /**
     * @return array
     */
    public function getBasketCtsSales()
    {
        if ($this->isOneDay()) {
            $plan = new Plan(['date' => $this->date_from, 'dateTimeOffset' => $this->dateTimeOffset]);
            return $plan->getBasketCtsSales();
        }

        return [];
    }

    /**
     * @return \Generator string date Y-m-d
     */
    public function dateGenerator()
    {
        $dateTimeFrom = strtotime($this->date_from);
        $dateTimeTo = strtotime($this->date_to);


        for ($dateTime = $dateTimeFrom; $dateTime <= $dateTimeTo; $dateTime += DAYS_1) {
            yield date('Y-m-d', $dateTime);
        }
    }

    /**
     * Формирует список категорий эфира, если указана одна дата, в противном случае генерит простой список часов дня
     * @return array
     */
    public function getOnairCategories()
    {
        if ($this->isOneDay()) {
            $plan = new Plan(['date' => $this->date_from, 'dateTimeOffset' => $this->dateTimeOffset]);
            return $plan->getOnairCategories();
        }

        $categories = [];

        for ($i = 0; $i < 24; $i++) {
            $index = 1000 * (strtotime($this->date_from) + $i*3600 + $this->dateTimeOffset);
            $hour = $i + $this->dateTimeOffset/HOUR_1;
            if ($hour > 23) $hour -= 24;
            $categories[$index] = sprintf('%02d:00', $hour);
        }

        return $categories;
    }

    /**
     * Данные по стоимости доставки заказов, сгруппированные по часу
     * @return array
     */
    public function getOrdersDeliveryData()
    {
        $result = [];

        foreach ($this->dateGenerator() as $date) {
            $plan = new Plan(['date' => $date, 'dateTimeOffset' => $this->dateTimeOffset]);
            $planSales = $plan->getOrdersDeliveryData();

            foreach ($planSales as $sales) {
                @$result[$this->getKeyForDate($sales['order_date'])] += $sales['delivery_price'];
            }
        }

        return $result;
    }

    /**
     * @param $treeId
     * @param null $isOnAir
     * @return array
     *
     * @throws \Exception
     * @throws \Throwable
     */
    public function getBasketProductSalesData($treeId, $isOnAir = null)
    {
        $result = [];

        foreach ($this->dateGenerator() as $date) {
            $plan = new Plan(['date' => $date, 'dateTimeOffset' => $this->dateTimeOffset, 'showCts' => $this->showCts]);
            $planSales = $plan->getBasketProductSalesData($treeId, $isOnAir);

            foreach ($planSales as $sales) {
                @$result[$this->getKeyForDate($sales['order_date'])] += $sales['product_price'];
            }
        }

        return $result;
    }

    private function getKeyForDate($dateTime)
    {
        if ($this->isOneDay()) {
            return $dateTime;
        }

        // подставляем ко всем таймстемпам один и тот же день, но разное время
        return strtotime(sprintf('%s %s', $this->date_from, date('H:i:s', $dateTime)));
    }

    private function getPeriodBegin($date)
    {
        return strtotime(sprintf('%s 00:00:00', $date)) + $this->dateTimeOffset;
    }

    private function getPeriodEnd($date)
    {
        return strtotime(sprintf('%s 23:59:59', $date)) + $this->dateTimeOffset;
    }
}