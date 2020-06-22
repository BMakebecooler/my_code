<?php

namespace common\models\filtered\mediaplan;

use common\helpers\Dates;
use modules\shopandshow\lists\Products;
use modules\shopandshow\models\mediaplan\AirBlock;
use modules\shopandshow\models\shop\ShopContentElement;
use yii\base\Model;

class OnAir extends Model
{

    /**
     * Виды выбранных дат
     */
    const TYPE_SELECT_DATE_BEFORE_YESTERDAY = 1;
    const TYPE_SELECT_DATE_YESTERDAY = 2;
    const TYPE_SELECT_DATE_TODAY = 3;

    public $sort = "popularity";
    public $inStock = 1;

    public $date = null;
    public $category = null;
    public $time = null;
    public $typeDate = self::TYPE_SELECT_DATE_TODAY;

    protected $timestamp = null;

    /**
     * @var AirBlock
     */
    protected $airBlock = null;


    public function init()
    {
        if ($sort = \Yii::$app->request->get('sort')) {
            $this->sort = $sort;
        }

        $this->defaultDate();

        if ($date = \Yii::$app->request->get('date')) {
            $this->getDateFromType((int)$date);
        }

        if ($category = \Yii::$app->request->get('category')) {
            $category = preg_replace('/[^0-9]/', '', $category);
            if (!empty($category)) {
                $this->category = $category;
            }
        }

        if ($time = \Yii::$app->request->get('time')) {
            $this->time = $time;

            $this->airBlock = AirBlock::findOne($this->time);
        }

        $this->defaultDate();
        $this->timestamp = strtotime($this->date);

        $this->setSelectDateType();

        parent::init();
    }

    /**
     * Установить вид выбранной даты
     */
    protected function setSelectDateType()
    {
        $this->typeDate = self::TYPE_SELECT_DATE_TODAY;

        if ($this->isBeforeYesterday()) {
            $this->typeDate = self::TYPE_SELECT_DATE_BEFORE_YESTERDAY;

        } elseif ($this->isYesterday()) {
            $this->typeDate = self::TYPE_SELECT_DATE_YESTERDAY;
        } elseif ($this->isToday()) {

        }
    }

    /**
     * @param $typeDate
     * @return false|string
     */
    protected function getDateFromType($typeDate)
    {
        $this->typeDate = $typeDate;

        switch ($typeDate) {
            case self::TYPE_SELECT_DATE_BEFORE_YESTERDAY:
                return $this->date = date('Y-m-d', strtotime('- 2 days'));
            case self::TYPE_SELECT_DATE_YESTERDAY:
                return $this->date = date('Y-m-d', strtotime('- 1 days'));
            case self::TYPE_SELECT_DATE_TODAY:
            default:
                return $this->date = date('Y-m-d');
        }
    }

    /**
     * @return false|string
     */
    public function getDayText()
    {
        switch ($this->typeDate) {
            case self::TYPE_SELECT_DATE_BEFORE_YESTERDAY:
                return 'Позавчера';
            case self::TYPE_SELECT_DATE_YESTERDAY:
                return 'Вчера';
            case self::TYPE_SELECT_DATE_TODAY:
            default:
                return 'Сегодня';
        }
    }


    public function rules()
    {
        return [
            [['sort', 'date'], 'string'],
            [['inStock', 'perPage', 'category', 'time'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [];
    }

    public function defaultDate()
    {
        $date = date('Y-m-d');
        //$date = '01.03.2018';
        return $this->date = $date;
    }

    /**
     *
     * @return false|int
     */
    public function beginOfDate()
    {

        if ($this->airBlock) {
            return $this->airBlock->begin_datetime;
        }

        $beginOfDay = Dates::beginOfDate($this->timestamp);

        return $beginOfDay;
    }

    /**
     *
     * @return false|int
     */
    public function beginOfAirDate()
    {

        if ($this->airBlock) {
            return $this->airBlock->begin_datetime;
        }

        $beginOfDay = Dates::beginOfAirDate($this->timestamp);

        return $beginOfDay;
    }


    /**
     * @return false|string
     */
    public function beginDate()
    {
        $date = date('d.m.Y', $this->beginOfDate());

        return $date;
    }

    /**
     *
     * @return false|int
     */
    public function endOfDate()
    {

        if ($this->airBlock) {
            return $this->airBlock->end_datetime;
        }

        $endOfDay = Dates::endOfDate($this->timestamp);

        return $endOfDay;
    }

    /**
     *
     * @return false|int
     */
    public function endOfAirDate()
    {

        if ($this->airBlock) {
            return $this->airBlock->end_datetime;
        }

        $endOfDay = Dates::endOfAirDate($this->timestamp);

        return $endOfDay;
    }

    /**
     *
     * @return false|int
     */
    public function beginOfDateNoAirBlock()
    {
        $beginOfDay = Dates::beginOfDate($this->timestamp);

        return $beginOfDay;
    }


    /**
     *
     * @return false|int
     */
    public function endOfDateNoAirBlock()
    {
        $endOfDay = Dates::endOfDate($this->timestamp);

        return $endOfDay;
    }

    /**
     * Просматриваем вчерашний день
     * @return bool
     */
    public function isYesterday()
    {
        return $this->date === date('Y-m-d', strtotime('-1 days'));
    }

    /**
     * Просматриваем сегодняшний день
     * @return bool
     */
    public function isToday()
    {
        return $this->date === date('Y-m-d');
    }

    /**
     * Признак позавчерашнего дня
     * @return bool
     */
    public function isBeforeYesterday()
    {
        return $this->date === date('Y-m-d', strtotime('-2 days'));
    }

    /**
     * Получить товар в эфире
     * @return bool|\common\models\cmsContent\CmsContentElement|ShopContentElement|null|\yii\db\ActiveRecord
     */
    public function getProductOnAir()
    {
        return (new Products())->getOnairProduct();
    }
}