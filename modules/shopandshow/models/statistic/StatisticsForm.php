<?php
/**
 * Created by PhpStorm.
 * User: Soskov_da
 * Date: 06.12.2017
 * Time: 13:47
 */

namespace modules\shopandshow\models\statistic;


use modules\shopandshow\models\mediaplan\AirBlock;
use modules\shopandshow\models\mediaplan\AirDayProductTime;

/**
 * Class StatisticsForm
 *
 * @property AirBlock $airBlock
 * @property AirDayProductTime $airDayProductTime
 * @property AirDayProductTime[] $airBlockProducts
 *
 * @package modules\shopandshow\models\statistic
 */
class StatisticsForm extends \yii\base\Model
{
    /** @var integer - идентификатор выбранного продукта в эфире */
    public $airBlockProductTimeId;
    /** @var integer идентификатор выбранного блока часа */
    public $airBlockId;

    /** @var integer временная метка для вывода расписания на произвольный день */
    public $timestamp;

    public function rules()
    {
        return [
            [['airBlockProductTimeId', 'airBlockId'], 'integer'],
        ];
    }

    public function init()
    {
        if (empty($this->airBlockId)) {
            $this->airBlockId = $this->airDayProductTime->block_id;
        }
        elseif (empty($this->airBlockProductTimeId)) {
            $this->airBlockProductTimeId = $this->airBlockProducts ? $this->airBlockProducts[0]->id : null;
        }

        $this->timestamp = $this->airBlock->begin_datetime;
    }

    public function getAirDayProductTime()
    {
        $airDayProductTime = AirDayProductTime::findOne($this->airBlockProductTimeId);
        return $airDayProductTime;
    }

    public function getAirBlock()
    {
        $airBlock = AirBlock::findOne(['block_id' => $this->airBlockId]);
        return $airBlock;
    }

    public function getAirBlockProducts()
    {
        return AirDayProductTime::find()
            ->where(['block_id' => $this->airBlockId])
            ->orderBy(['id' => SORT_ASC])
            ->all();
    }

    public function getScheduleList()
    {
        $scheduleList = AirBlock::getScheduleList($this->timestamp);

        return \common\helpers\ArrayHelper::map($scheduleList, 'block_id', function ($item) {
            return date('Y-m-d', $this->timestamp) . ' ' . $item['time'] . ' ' . $item['name'];
        });
    }

    public function getScheduleActiveBlockId()
    {
        $scheduleList = $this->getScheduleList();

        return array_reduce($scheduleList, function ($result, $item) {
            if ($item['active']) {
                return $item['block_id'];
            }
            return $result;
        }, null);
    }


}