<?php

namespace modules\shopandshow\lists;

use modules\shopandshow\models\shares\SsShare;
use skeeks\cms\components\Cms;
use yii\db\ActiveQuery;
use \yii\db\Expression as DbExpression;

class Shares
{

    /**
     * @param $code
     * @return SsShare
     */
    public static function getShareByCode($code)
    {
        return SsShare::findOne(['code' => $code]);
    }

    /**
     * @param $id
     * @return SsShare
     */
    public static function getById($id)
    {
        return SsShare::findOne($id);
    }

    /**
     * @param string $type
     * @return ActiveQuery
     */
    private static function getShareByTypeEfirQuery($type = SsShare::BANNER_TYPE_CTS)
    {
        $scheduleDate = SsShare::getDate();

        $query = SsShare::find()
            ->joinWith('product')
            ->andWhere(['banner_type' => $type])
            ->andWhere(['not', ['ss_shares.image_id' => null]])
            ->andWhere(['not', ['ss_shares.active' => Cms::BOOL_N]])
            ->andWhere('begin_datetime <= :time AND end_datetime >= :time', [
                ':time' => $scheduleDate,
            ]);

        if ($type == SsShare::BANNER_TYPE_CTS) {
            $onAirSchedule = array_filter(
                (new \common\widgets\onair\OnAir())->getScheduleList(),
                function ($schedule) {
                    return $schedule['tree_id'] != null;
                }
            );
            if (count($onAirSchedule) > 0) {
                // сортировка по расписанию эфира
                $arTrees = array_unique(array_map(function ($s) {
                    return $s['tree_id'];
                }, $onAirSchedule));
                $query->orderBy([
                    new DbEXpression('FIELD (`ss_shares`.`schedule_tree_id`, ' . implode(',', $arTrees) . ')'),
                    new DbEXpression('`ss_shares`.`begin_datetime` ASC'),
                    new DbEXpression('`ss_shares`.`id` DESC')
                ]);
            }
        }
        return $query;
    }

    /**
     * Получить акцию по типу с учетом эфира
     * @param string $type
     * @return array|null|\yii\db\ActiveRecord|SsShare
     */
    public static function getShareByTypeEfir($type = SsShare::BANNER_TYPE_CTS)
    {
        return SsShare::getDb()->cache(function ($db) use ($type) {
            return (self::getShareByTypeEfirQuery($type))->limit(1)->one();
        }, HOUR_1);
    }

    /**
     * Получить акции по типу с учетом эфира
     * @param string $type
     * @param int $limit количество баннеров
     * @return array|\yii\db\ActiveRecord[]|SsShare[]
     */
    public static function getSharesByTypeEfir($type = SsShare::BANNER_TYPE_CTS, $limit = 5)
    {
        return SsShare::getDb()->cache(function ($db) use ($type, $limit) {
            return (self::getShareByTypeEfirQuery($type))->limit($limit)->all();
        }, HOUR_1);
    }

    /**
     * Получить баннер цтс с продуктом на указанную дату (Y-m-d)
     * @param $date
     * @return array|SsShare|null
     */
    public static function getCtsProduct($date)
    {
        return SsShare::find()
            ->joinWith('product')
            ->andWhere(['banner_type' => SsShare::BANNER_TYPE_CTS])
            ->andWhere(['not', ['ss_shares.image_id' => null]])
            ->andWhere(['not', ['ss_shares.active' => Cms::BOOL_N]])
            ->andWhere('begin_datetime <= :time AND end_datetime >= :time', [
                ':time' => strtotime(sprintf('%s 12:00:00', $date)),
            ])->one();
    }
}