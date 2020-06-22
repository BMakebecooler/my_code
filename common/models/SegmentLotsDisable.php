<?php


namespace common\models;


class SegmentLotsDisable extends \common\models\generated\models\SegmentLotsDisable
{
    public static function getLotsDisable($segmentId)
    {
        $return = [];
        $query = self::find()
            ->andWhere(['segment_id' => $segmentId]);

        foreach ($query->each() as $model){
            $return[] = $model->lot_id;
        }

        return $return;

    }
}