<?php

namespace modules\shopandshow\models\mediaplan;

use common\lists\Contents;
use common\lists\TreeList;
use common\models\cmsContent\CmsContentElement;
use modules\shopandshow\lists\Guids;
use modules\shopandshow\models\shop\ShopContentElement;

/**
 * This is the model class for table "ss_mediaplan_schedule_items".
 *
 * @property integer $id
 * @property string $created_at
 * @property integer $begin_datetime
 * @property integer $end_datetime
 * @property integer $lot_id
 * @property integer $section_id
 * @property integer $bitrix_section_id
 * @property integer $block_id
 * @property string $section_name
 * @property string $type
 * @property string $lot_guid
 *
 * @property ShopContentElement $shopContentElement
 * @property CmsContentElement $cmsContentElement
 * @property CmsContentElement[] $cmsContentElements
 */
class AirDayProductTime extends MediaPlan
{

    const TYPE_LIVE_REC = 'live-rec';
    const TYPE_CLIP = 'clip';

    /**
     * Количество лотов в категории
     * @var
     */
    public $count_product;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ss_mediaplan_air_day_product_time';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at'], 'safe'],
            [['begin_datetime', 'end_datetime', 'lot_id'], 'required'], //, 'section_id'
            [['begin_datetime', 'end_datetime', 'lot_id', 'section_id', 'bitrix_section_id', 'block_id'], 'integer'],
            [['section_name'], 'string', 'max' => 100],
            [['type'], 'string', 'max' => 50],
            [['lot_guid'], 'string', 'max' => 54],
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
            'begin_datetime' => 'Begin Datetime',
            'end_datetime' => 'End Datetime',
            'lot_id' => 'Lot ID',
            'section_id' => 'Section ID',
            'bitrix_section_id' => 'Bitrix Section ID',
            'block_id' => 'block_id',
            'section_name' => 'Section Name',
            'type' => 'Type',
            'lot_guid' => 'lot_guid',
        ];
    }

    private static $bitrixCategories = [];


    /**
     * @param $schedule
     * @return bool
     */
    public static function addProduct($schedule)
    {
        $beginDateTime = strtotime($schedule['beginDateTime']);
        $endDateTime = strtotime($schedule['endDateTime']);

        $scheduleItem = self::find()->where([
            'lot_id' => $schedule['lotId'],
            'begin_datetime' => $beginDateTime,
            'end_datetime' => $endDateTime,
            'type' => $schedule['type'],
            'bitrix_section_id' => $schedule['categoryBitrixId'],
        ])->one();

        /**
         * @var self $scheduleItem
         */
        if ($scheduleItem) {
            return false;
        } else {
            $scheduleItem = new static();
            $scheduleItem->created_at = date('Y-m-d H:i:s');
            $scheduleItem->begin_datetime = $beginDateTime;
            $scheduleItem->end_datetime = $endDateTime;
            $scheduleItem->lot_id = $schedule['lotId'];
            $scheduleItem->bitrix_section_id = $schedule['categoryBitrixId'];
            $scheduleItem->section_name = $schedule['categoryName'];
            $scheduleItem->type = $schedule['type'];

            if (isset($schedule['GUID'])) {
                $scheduleItem->lot_guid = $schedule['GUID'];
            }

            if (!isset(self::$bitrixCategories[$schedule['categoryBitrixId']])) {
                if ($tree = TreeList::getTreeByBitrixId($schedule['categoryBitrixId'])) {
                    self::$bitrixCategories[$schedule['categoryBitrixId']] = $tree->id;
                }
            }

            if (isset(self::$bitrixCategories[$schedule['categoryBitrixId']])) {
                $scheduleItem->section_id = self::$bitrixCategories[$schedule['categoryBitrixId']];
            }

            if (!$scheduleItem->save()) {
                var_dump($scheduleItem->getErrors());
//                die();
            }

            return true;

            return $scheduleItem->save();
        }
    }


    /**
     * @param $blockId
     * @param $blockLot
     * @return bool
     */
    public static function addProductByBlock($blockId, $blockLot)
    {

        if (isset($blockLot['factBegin']) && isset($blockLot['factEnd']) &&
            ($blockLot['factBegin'] && $blockLot['factEnd'])
        ) {
            $beginDateTime = strtotime($blockLot['factBegin']);
            $endDateTime = strtotime($blockLot['factEnd']);
        } else {
            $beginDateTime = strtotime($blockLot['beginDateTime']);
            $endDateTime = strtotime($blockLot['endDateTime']);
        }

        $scheduleItem = new static();
        $scheduleItem->created_at = date('Y-m-d H:i:s');
        $scheduleItem->begin_datetime = $beginDateTime;
        $scheduleItem->end_datetime = $endDateTime;
//        $scheduleItem->lot_id = $blockLot['lotId'];
        $scheduleItem->bitrix_section_id = $blockLot['categoryBitrixId'];
        $scheduleItem->block_id = $blockId;
        $scheduleItem->section_name = $blockLot['categoryName'];

        if (isset($blockLot['GUID'])) {
            $scheduleItem->lot_guid = $blockLot['GUID'];

            if ($lot = Guids::getEntityByGuid($blockLot['GUID'])) {
                $scheduleItem->lot_id = $lot->id;
            }
        }

        if (!$scheduleItem->lot_id && isset($blockLot['lotId'])) {
            $lot = Contents::getContentElementByBitrixId($blockLot['lotId'], [PRODUCT_CONTENT_ID, OFFERS_CONTENT_ID]);
            if ($lot) {
                $scheduleItem->lot_id = $lot->id;
            }
        }

        if (!isset(self::$bitrixCategories[$blockLot['categoryBitrixId']])) {
            $tree = TreeList::getTreeByBitrixId($blockLot['categoryBitrixId']);
            if ($tree) {
                self::$bitrixCategories[$blockLot['categoryBitrixId']] = $tree->id;
            }
        }

        if (isset(self::$bitrixCategories[$blockLot['categoryBitrixId']])) {
            $scheduleItem->section_id = self::$bitrixCategories[$blockLot['categoryBitrixId']];
        }

        if (!$scheduleItem->save()) {
            var_dump($scheduleItem->getErrors());
//                die();
        }

        return true;

        return $scheduleItem->save();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopContentElement()
    {
        return $this->hasOne(ShopContentElement::className(), ['id' => 'lot_id'])
            ->andWhere(['cms_content_element.content_id' => PRODUCT_CONTENT_ID]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCmsContentElement()
    {
        return $this->hasOne(CmsContentElement::className(), ['id' => 'lot_id'])
            ->andWhere(['cms_content_element.content_id' => PRODUCT_CONTENT_ID]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCmsContentElements()
    {
        return $this->hasMany(CmsContentElement::className(), ['id' => 'lot_id'])
            ->andWhere(['cms_content_element.content_id' => PRODUCT_CONTENT_ID]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAirBlock()
    {
        return $this->hasOne(AirBlock::className(), ['block_id' => 'block_id']);
    }

}
