<?php

namespace modules\shopandshow\models\shop\stock\forms;

use common\lists\TreeList;
use skeeks\cms\components\Cms;

class Stock extends \yii\base\Model
{

    public $date;

    public $dateFrom;

    public $dateTo;

    public static $stockTypes = ['A', 'B', 'C', 'D'];

    const SEGMENT_NOT_STOCK = 'NOT_STOCK';
    const SEGMENT_TOTAL_STOCK = 'TOTAL_STOCK';
    const SEGMENT_TOTAL = 'TOTAL';

    /**
     * Названия для сегментов
     * @var array
     */
    public static $segmentsLabels = [
        self::SEGMENT_NOT_STOCK => 'Не сток',
        self::SEGMENT_TOTAL_STOCK => 'Сток',
        self::SEGMENT_TOTAL => 'Общее'
    ];

    public function init()
    {
        parent::init();

        if (!$this->date) {
            $this->date = date('Y-m-d');
        }

        if (!$this->dateFrom) {
            $this->dateFrom = date('Y-m-d', time() - DAYS_14);
        }

        if (!$this->dateTo) {
            $this->dateTo = date('Y-m-d');
        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['date'], 'required'],
            [['date', 'dateFrom', 'dateTo'], 'string'],
            [['dateFrom', 'dateTo'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'date' => 'Дата',
            'dateFrom' => 'Дата С',
            'dateTo' => 'Дата По',
        ];
    }

    /**
     * Возможные типы стоков
     * @return array
     */
    public function getStockTypes()
    {
        return self::$stockTypes;
    }

    /**
     * Список категорий для дополнительного отчета
     * @return \skeeks\cms\models\Tree[]
     */
    public function getCategories()
    {
        $trees = TreeList::getTreeById(TreeList::CATALOG_ID)->getChildren()->onCondition(['active' => Cms::BOOL_Y])->all();

        $trees[] = TreeList::getTreeByCode('tovary-dlya-dachi');
        $trees[] = TreeList::getTreeByCode('kosmetika');

        return $trees;
    }


    public function getPeriodBegin($date)
    {
        return strtotime(sprintf('%s 00:00:00', $date));
    }

    public function getPeriodEnd($date)
    {
        return strtotime(sprintf('%s 23:59:59', $date));
    }

    public static function getSegmentLabel($segmentId)
    {
        return isset(self::$segmentsLabels[$segmentId]) ?
            self::$segmentsLabels[$segmentId] : (in_array($segmentId, self::$stockTypes) ? "Сегмент {$segmentId}" : $segmentId);
    }


}