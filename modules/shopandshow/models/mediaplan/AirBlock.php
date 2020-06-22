<?php

namespace modules\shopandshow\models\mediaplan;

use common\helpers\ArrayHelper;
use common\lists\TreeList;
use common\models\cmsContent\CmsContentElement;
use modules\shopandshow\lists\Guids;
use modules\shopandshow\lists\Onair;

/**
 * This is the model class for table "ss_mediaplan_air_blocks".
 *
 * @property integer $id
 * @property string $created_at
 * @property integer $block_id
 * @property integer $block_repeat_id
 * @property integer $begin_datetime
 * @property integer $end_datetime
 * @property integer $duration
 * @property integer $section_id
 * @property integer $bitrix_section_id
 * @property string $type
 * @property string $title
 * @property string $subcategory
 * @property string $section_name
 * @property string $section_color
 *
 * @property CmsContentElement[] $cmsContentElements
 */
class AirBlock extends MediaPlan
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ss_mediaplan_air_blocks';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at'], 'safe'],
            [['block_id', 'begin_datetime', 'end_datetime', 'section_id'], 'required'],
            [['block_id', 'block_repeat_id', 'begin_datetime', 'end_datetime', 'duration', 'section_id', 'bitrix_section_id'], 'integer'],
            [['type'], 'string', 'max' => 50],
            [['title', 'subcategory', 'section_name', 'section_color'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Created At',
            'block_id' => 'Block ID',
            'block_repeat_id' => 'Block Repeat ID',
            'begin_datetime' => 'Begin Datetime',
            'end_datetime' => 'End Datetime',
            'duration' => 'Duration',
            'section_id' => 'Section ID',
            'bitrix_section_id' => 'Bitrix Section ID',
            'type' => 'Type',
            'title' => 'Title',
            'subcategory' => 'Subcategory',
            'section_name' => 'Section Name',
            'section_color' => 'Section Color',
        ];
    }


    private static $bitrixCategories = [];


    /**
     * @param $block
     * @return bool
     */
    public static function addBlock($block)
    {

        $beginDateTime = strtotime($block['beginDateTime']);
        $endDateTime = strtotime($block['endDateTime']);

        $blockItem = self::find()->where([
            'block_id' => $block['blockId'],
//            'begin_datetime' => $beginDateTime, //Если у блока поменяют дату, то он не найдется и будет создан дубликат с другим временем, не гуд
//            'end_datetime' => $endDateTime,
            'type' => $block['type'],
//            'bitrix_section_id' => $block['categoryBitrixId'],
        ])->one();

        /**
         * @var self $blockItem
         */
        if (!$blockItem) {
            $blockItem = new static();
            $blockItem->created_at = date('Y-m-d H:i:s');
        }

        $blockItem->begin_datetime = $beginDateTime;
        $blockItem->end_datetime = $endDateTime;
        $blockItem->block_id = $block['blockId'];
        $blockItem->block_repeat_id = $block['blockRepeatId'];
        $blockItem->duration = $block['duration'];
        $blockItem->bitrix_section_id = $block['categoryBitrixId'];
        $blockItem->section_name = $block['categoryName'];
        $blockItem->section_color = $block['categoryColor'];
        $blockItem->type = $block['type'];
        $blockItem->title = $block['title'];
        $blockItem->subcategory = $block['subcategory'];

        if (!isset(self::$bitrixCategories[$block['categoryBitrixId']])) {
            $tree = TreeList::getTreeByBitrixId($block['categoryBitrixId']);
            if ($tree) {
                self::$bitrixCategories[$block['categoryBitrixId']] = $tree->id;
            }
        }

        if (false && isset($block['categoryGUID']) && $block['categoryGUID']) {

            $guidsCategories[CONST_SITE_SHIK] = [
                '5F1E9D01B2274E20E0538201090AC063' => '56DF7DA728613941E0534301090A877E', //Кухня
                '5F1E9D01B2284E20E0538201090AC063' => '56DF7DA727CE3941E0534301090A877E', //Дом
                '5F1E9D01B2294E20E0538201090AC063' => '56DF7DA7284F3941E0534301090A877E', //Мода
                '5F1E9D01B22A4E20E0538201090AC063' => '56DF7DA727CB3941E0534301090A877E', //Красота
                '5F1E9D01B22B4E20E0538201090AC063' => '56DF7DA728423941E0534301090A877E', //Электроника
                '5F1E9D01B22C4E20E0538201090AC063' => '56DF7DA728233941E0534301090A877E', //Ювелирка
                '5F1E9D01B22D4E20E0538201090AC063' => '5F835C9605370F48E0538201090ADE3B', //Разное
            ];

            $guidsCategories[CONST_SITE_SS] = [
                '5F1E9D01B2274E20E0538201090AC063' => '56DF7DA728613941E0534301090A877E', //Кухня
                '5F1E9D01B2284E20E0538201090AC063' => '56DF7DA727CE3941E0534301090A877E', //Дом
                '5F1E9D01B2294E20E0538201090AC063' => '56DF7DA7284F3941E0534301090A877E', //Мода
                '5F1E9D01B22A4E20E0538201090AC063' => '56DF7DA727CB3941E0534301090A877E', //Красота
                '5F1E9D01B22B4E20E0538201090AC063' => '56DF7DA728423941E0534301090A877E', //Электроника
                '5F1E9D01B22C4E20E0538201090AC063' => '56DF7DA728233941E0534301090A877E', //Ювелирка
                '5F1E9D01B22D4E20E0538201090AC063' => '5F835C9605370F48E0538201090ADE3B', //Разное
            ];

            if (\Yii::$app->appComponent->isSiteShik()) {
                $guidsCategories = $guidsCategories[CONST_SITE_SHIK];
            } else {
                $guidsCategories = $guidsCategories[CONST_SITE_SS];
            }

            if (isset($block['categoryGUID']) && isset($guidsCategories[$block['categoryGUID']])) {
                if ($tree = Guids::getEntityByGuid($guidsCategories[$block['categoryGUID']])) {
                    self::$bitrixCategories[$block['categoryGUID']] = $tree->id;
                }
            }
        }

        if (isset(self::$bitrixCategories[$block['categoryBitrixId']])) {
            $blockItem->section_id = self::$bitrixCategories[$block['categoryBitrixId']];
        }

        if (false && isset($block['categoryGUID']) && isset(self::$bitrixCategories[$block['categoryGUID']])) {
            $blockItem->section_id = self::$bitrixCategories[$block['categoryGUID']];
        }

        if (!$blockItem->save()) {

            var_dump($block);
            var_dump($blockItem->getErrors());
//                die();
        }

        return true;

        //return $blockItem->save();
    }

    /**
     * Удаляет блоки из БД
     * @param array $blocks
     */
    public static function removeBlocks(array $blocks)
    {
        foreach ($blocks as $blockId) {
            AirDayProductTime::deleteAll(['block_id' => $blockId]);
            AirBlock::deleteAll(['block_id' => $blockId]);
        }
    }


    public function getTitle()
    {
        return $this->title;
    }

    public function isActiveTime()
    {
        $time = time();

        return $this->begin_datetime <= $time && $this->end_datetime >= $time;
    }


    public static function getScheduleList($timestamp = null)
    {
        $timestamp = $timestamp ?: time();

        $key = 'statistics-schedule-' . \common\helpers\Dates::beginOfDate($timestamp);

        return \Yii::$app->cache->getOrSet($key, function () use ($timestamp) {
            return self::getScheduleListData($timestamp);
        }, HOUR_1);
    }

    public static function getScheduleListData($timestamp)
    {
        $airBlocks = Onair::getScheduleList($timestamp);

        $schedules = [];

        /** @var  $schedule AirBlock */
        foreach ($airBlocks as $schedule) {
            $schedules[] = [
                'id' => $schedule->id,
                'active' => ($schedule->isActiveTime()) ? 'active' : '',
                'block_id' => $schedule->block_id,
                'tree_id' => $schedule->section_id,
                'name' => $schedule->getCategoryName(),
                'time' => $schedule->getBeginTime() . ' - ' . $schedule->getEndTime(),
                'begin_time' => $schedule->getBeginTime(),
                'begin_datetime' => $schedule->begin_datetime
            ];
        }

        return $schedules;
    }

    public static function getScheduleListForPeriod($dateTimeFrom, $dateTimeTo)
    {
        $key = 'statistics-schedule-' . $dateTimeFrom . '-' . $dateTimeTo;

        return \Yii::$app->cache->getOrSet($key, function () use ($dateTimeFrom, $dateTimeTo) {
            return self::getScheduleListForPeriodData($dateTimeFrom, $dateTimeTo);
        }, HOUR_1);
    }

    public static function getScheduleListForPeriodData($dateTimeFrom, $dateTimeTo)
    {
        $airBlocks = Onair::getScheduleList($dateTimeFrom, $dateTimeTo);

        $schedules = [];

        /** @var  $schedule AirBlock */
        foreach ($airBlocks as $schedule) {
            $schedules[] = [
                'id' => $schedule->id,
                'active' => ($schedule->isActiveTime()) ? 'active' : '',
                'block_id' => $schedule->block_id,
                'tree_id' => $schedule->getCategoryId(),
                'name' => $schedule->getCategoryName(),
                'time' => $schedule->getBeginTime() . ' - ' . $schedule->getEndTime(),
                'begin_time' => $schedule->getBeginTime(),
                'begin_datetime' => $schedule->begin_datetime
            ];
        }

        return $schedules;
    }

    public static function getAvailSections()
    {
        $key = 'airblock-avail-sections';

        return \Yii::$app->cache->getOrSet($key, function () {
            $airBlockQuery = AirBlock::find()
                ->select(['section_id', 'section_name'])
                ->where('section_name IS NOT NULL')
                ->groupBy(['section_id', 'section_name'])
                ->orderBy('section_id');

            return ArrayHelper::map($airBlockQuery->asArray()->all(), 'section_id', 'section_name');
        }, HOUR_1);
    }

    public function getAirDayProductTime()
    {
        return $this->hasMany(AirDayProductTime::className(), ['block_id' => 'block_id']);
    }

    public function getCmsContentElements()
    {
        return $this->hasMany(CmsContentElement::className(), ['id' => 'lot_id'])
            ->andWhere(['cms_content_element.content_id' => PRODUCT_CONTENT_ID])
            ->via('airDayProductTime');
    }
}
