<?php

namespace common\models;

use common\helpers\Dates;
use common\helpers\ArrayHelper;
use common\models\query\SsMediaplanAirBlocksQuery;

class SsMediaplanAirBlocks extends \common\models\generated\models\SsMediaplanAirBlocks
{
    public $dayId = 0;
    public $categoryId = 0;
    public $hourTime = 0;

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['dayId', 'categoryId', 'hourTime'], 'integer'],
            [['dayId'], 'required'],
        ]);
    }

    public static function find()
    {
        return new SsMediaplanAirBlocksQuery(get_called_class());
    }

    public function getCategoriesByDayTime()
    {
        return $this->find()
            ->byDay(Dates::getDaytimeFromId($this->dayId))
            ->groupBy('section_id')
            ->orderBy('begin_datetime')
            ->all();
    }

    public function getSchedule(){
        $scheduleQuery = $this->find()
            ->byDay(Dates::getDaytimeFromId($this->dayId))
            ->orderBy('begin_datetime');

        if ($this->categoryId){
            $scheduleQuery->byCategoryId($this->categoryId);
        }

        return $scheduleQuery->all();
    }

    //Товары определеннй категории в дней
    public function getCategoryProductsForSale()
    {
        if ($airProducts = $this->getCategoryAirProducts()){
            return Product::find()
                ->canSale()
                ->andWhere(['cms_content_element.id' => \common\helpers\ArrayHelper::getColumn($airProducts, 'lot_id')])
                ->all();
        }

        return [];
    }

    //Товары которые есть в расписании. Без какой либо фильтрации и проверок
    private function getCategoryAirProducts()
    {
        return SsMediaplanAirDayProductTime::find()
            ->byDay($this->begin_datetime)
            ->byCategoryId($this->section_id)
            ->all();
    }

    public function getTimePeriod()
    {
        return date("H:i", $this->begin_datetime) . ' - ' . (date("H:i", $this->end_datetime));
    }
}
