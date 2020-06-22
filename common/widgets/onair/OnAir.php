<?php

/**
 * Виджет для товаров в эфире
 */

namespace common\widgets\onair;

use common\helpers\User;
use common\lists\Contents;
use common\models\filtered\mediaplan\OnAirCategories;
use modules\shopandshow\models\mediaplan\AirBlock;
use modules\shopandshow\models\mediaplan\AirDayProductTime;
use modules\shopandshow\models\shop\ShopContentElement;
use skeeks\cms\helpers\UrlHelper;
use yii\base\Widget;
use yii\data\ActiveDataProvider;

class OnAir extends Widget
{

    public $viewFile = '@template/widgets/OnAir/onair-page';


    /**
     * @var OnAirCategories
     */
    protected static $onAirFilterCategories;

    /**
     * @var int
     */
    public $limitProduct = 20;

    /**
     * Показывать по умолчанию товары текущего часа
     * @var bool
     */
    public $showProductCurrentHour = false;

    /**
     * Признак что категории и расписание не нужны
     * @var bool
     */
    public $isOnlyProductOnAir = false;


    /**
     * Товар в эфире
     * @var null
     */
    public $onAirProduct = null;

    /**
     * При необходимости сюда можно передавать любые данные
     * @var array
     */
    public $data = [];

    /**
     * @var AirDayProductTime[]
     */
    protected $categories;

    /**
     * @var AirBlock[]
     */
    protected $scheduleList;

    /**
     * @var ShopContentElement
     */
    protected static $productOnAir = null;

    public function init()
    {
        parent::init();

        self::$onAirFilterCategories = self::$onAirFilterCategories ?: new OnAirCategories();
        self::$onAirFilterCategories->load(\Yii::$app->request->get());

        if ($this->isOnlyProductOnAir) {
            return true;
        }

        $this->categories = self::$onAirFilterCategories->getCategories();
        $this->scheduleList = $this->getDataScheduleList();
    }

    public function run()
    {
        return $this->render($this->viewFile);
    }

    /**
     * @return array
     */
    public function getCategories()
    {
        $categories[] = [
            'active' => (!self::$onAirFilterCategories->category) ? 'active' : '',
            'url' => UrlHelper::construct([
                \Yii::$app->request->pathInfo,
                'date' => self::$onAirFilterCategories->typeDate,
                'all' => 1,
            ]),
            'name' => 'Все',
            'count' => '',
            'id' => 0,
            'date' => self::$onAirFilterCategories->typeDate,
            'all' => 1,
        ];

        foreach ($this->categories as $category) {
            $categories[] = [
                'active' => $category->getCategoryId() == self::$onAirFilterCategories->category,
                'url' => UrlHelper::construct([\Yii::$app->request->pathInfo,
                    'category' => $category->getCategoryId(),
                    'date' => self::$onAirFilterCategories->typeDate,
//                    'time' => self::$onAirFilterCategories->time,
                    'all' => 1,
                ]),
                'name' => $category->getCategoryName(),
                'count' => $category->count_product,
                'id' => $category->id,
                'category' => $category->getCategoryId(),
                'date' => self::$onAirFilterCategories->typeDate,
//                    'time' => self::$onAirFilterCategories->time,
                'all' => 1,
            ];
        }

        return $categories;
    }

    public function getDisplayDate()
    {
        return self::$onAirFilterCategories->beginDate();
    }

    public function getScheduleList($activeIsNowOnly = false)
    {
        $schedules = [];

        foreach ($this->scheduleList as $schedule) {
            $schedules[] = [
                'id' => $schedule->id,
                'active' => ($schedule->isActiveTime() || ($schedule->id == self::$onAirFilterCategories->time && !$activeIsNowOnly)) ? 'active' : '',
                'block_id' => $schedule->block_id,
                'url' => UrlHelper::construct(\Yii::$app->request->pathInfo, [
                    'category' => self::$onAirFilterCategories->category,
                    'block' => $schedule->block_id,
                    'date' => self::$onAirFilterCategories->typeDate,
                    'time' => $schedule->id,
                    'all' => \Yii::$app->request->get('all'),
                ]),
                'tree_id' => $schedule->getCategoryId(),
                'name' => $schedule->getCategoryName(),
                'time' => $schedule->getBeginTime() . ' - ' . $schedule->getEndTime(),
                'hour_efir' => (int)date('H', $schedule->begin_datetime),
                'block' => $schedule->block_id,
                'date' => self::$onAirFilterCategories->typeDate,
            ];
        }

        return $schedules;
    }

    /**
     *
     * @return array|AirBlock[]
     */
    public function getDataScheduleList()
    {
        $airBlock = AirBlock::find()->alias('air_blocks');

        /**
         * @var $query \yii\db\ActiveQuery
         */

        if (
            !self::$onAirFilterCategories->isBeforeYesterday() &&
            !self::$onAirFilterCategories->isYesterday() &&
            !self::$onAirFilterCategories->isToday()
        ) {
            self::$onAirFilterCategories->defaultDate();
        }

//        $query->innerJoinWith(['shopContentElement']);
        $airBlock->andWhere('air_blocks.begin_datetime >= :begin_datetime AND air_blocks.end_datetime <= :end_datetime', [
            ':begin_datetime' => self::$onAirFilterCategories->beginOfDateNoAirBlock(),
            ':end_datetime' => self::$onAirFilterCategories->endOfDateNoAirBlock(),
        ]);

        if (self::$onAirFilterCategories->category) {
            $airBlock->andWhere('air_blocks.section_id = :section_id', [
                ':section_id' => self::$onAirFilterCategories->category,
            ]);
        }


/*        if (User::isDeveloper()) {
            $airBlock->andWhere('begin_datetime <= :time', [
                ':time' => time(),
            ]);
        }*/

        $airBlock->groupBy('air_blocks.begin_datetime, air_blocks.end_datetime');
        $airBlock->orderBy('air_blocks.begin_datetime ASC');

        //Добавим проверку наличия товаров для указанного блока
        if (true){
            $airBlock->innerJoin(AirDayProductTime::tableName().' AS air_day_products',
                "air_day_products.block_id=air_blocks.block_id");
        }

        return $airBlock->all();

        return AirBlock::getDb()->cache(function ($db) use ($airBlock) {
            return $airBlock->all();
        }, MIN_30);
    }

    /**
     * Признак товара в эфире
     * @return bool
     */
    public function isProductOnAir()
    {
        return (bool)$this->getOnAirProduct();
    }

    /**
     * Получить товар в эфире
     * @return \modules\shopandshow\models\shop\ShopContentElement|null
     */
    public function getOnAirProduct()
    {
        if ($this->onAirProduct) {
            return Contents::getContentElementById((int)$this->onAirProduct);
        }

        return self::$productOnAir === null ? self::$productOnAir = self::$onAirFilterCategories->getProductOnAir() : self::$productOnAir;
    }


    /**
     * @param ActiveDataProvider $activeDataProvider
     * @return $this
     */
    public function getProducts(ActiveDataProvider $activeDataProvider, $useFilterParams = true)
    {
        $query = $activeDataProvider->query;

        $blockId = (int)\Yii::$app->request->get('block');

        $date = \Yii::$app->request->get('date');
        $category = \Yii::$app->request->get('category');
        $time = \Yii::$app->request->get('time');

        if (!$useFilterParams){
            $blockId = $date = $category = $time = null;
        }

        /**
         * @var $query \yii\db\ActiveQuery
         */

        if (
            !self::$onAirFilterCategories->isBeforeYesterday() &&
            !self::$onAirFilterCategories->isYesterday() &&
            !self::$onAirFilterCategories->isToday()
        ) {
            self::$onAirFilterCategories->defaultDate();
        }

        $query->joinWith([
            'mediaPlanScheduleItem',
        ]);


        /**
         * @todo Временно шило с блоком
         */

        $query->innerJoin('ss_mediaplan_air_blocks', 'ss_mediaplan_air_blocks.block_id = ss_mediaplan_air_day_product_time.block_id');
        $query->addSelect("HOUR(FROM_UNIXTIME(`ss_mediaplan_air_blocks`.`begin_datetime`)) AS hour_efir");

        if ( //Режим работы по умолчанию
            $this->showProductCurrentHour &&
            !$blockId &&
            !$date &&
            !$category &&
            !$time &&
            !((date('H') >= date('22') && date('H:i:s') <= date('23:59:59')))
        ) {
            $curDateBegin = (new \DateTime())->format("Y-m-d H:00:00");
            $curDateEnd = (new \DateTime())->add(new \DateInterval('PT1H'))->format('Y-m-d H:00:00');

            $query->andWhere('ss_mediaplan_air_blocks.begin_datetime >= :begin_datetime AND ss_mediaplan_air_blocks.end_datetime <= :end_datetime ', [
                ':begin_datetime' => (new \DateTime($curDateBegin))->format("U"),
                ':end_datetime' => (new \DateTime($curDateEnd))->format('U'),
            ]);

        } elseif (!$blockId) {
            $curDateBegin = (new \DateTime())->setTimestamp(self::$onAirFilterCategories->beginOfDateNoAirBlock())->format("Y-m-d H:00:00");
            $curDateEnd = (new \DateTime())->setTimestamp(self::$onAirFilterCategories->endOfDateNoAirBlock())->format('Y-m-d H:00:00');

            $query->andWhere('ss_mediaplan_air_blocks.begin_datetime >= :begin_datetime AND ss_mediaplan_air_blocks.end_datetime <= :end_datetime ', [
                ':begin_datetime' => (new \DateTime($curDateBegin))->format("U"),
                ':end_datetime' => (new \DateTime($curDateEnd))->format('U'),
            ]);
        }

        if (!$blockId && self::$onAirFilterCategories->category) {
            $query->andWhere('ss_mediaplan_air_day_product_time.section_id = :section_id', [
                ':section_id' => self::$onAirFilterCategories->category,
            ]);
        }

        if ($blockId) {
            $query->andWhere('ss_mediaplan_air_day_product_time.block_id = :block_id', [
                ':block_id' => $blockId,
            ]);
        }

        if (!$query->orderBy) {
            $query->orderBy(['ss_mediaplan_air_blocks.begin_datetime' => SORT_ASC, 'ss_mediaplan_air_day_product_time.id' => SORT_ASC]);
        }

        if (!$query->groupBy) {
            $query->groupBy('ss_mediaplan_air_day_product_time.lot_id, ss_mediaplan_air_day_product_time.block_id');
        }


        if ($this->limitProduct) {
            $query->limit($this->limitProduct);
        }

        return $this;
    }

    public function getOnAirFilterCategories()
    {
        return self::$onAirFilterCategories;
    }

    public function isSimilarProduct()
    {
        return true;
    }

    /**
     * @return array|ShopContentElement[]|\yii\db\ActiveRecord[]
     */
    public function getSimilarProducts()
    {
        return self::$productOnAir->getSimilarProducts();
    }

    /**
     * @return int
     */
    public function getPageSize(): int
    {
        $all = \Yii::$app->request->get('all');

        return ($all) ? 100 : $this->limitProduct;
    }
}