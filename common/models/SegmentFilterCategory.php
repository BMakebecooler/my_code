<?php


namespace common\models;


class SegmentFilterCategory extends \common\models\generated\models\SegmentFilterCategory
{
    public static function getCategories()
    {
        $return = [];
        $data = self::find()->orderBy('name')->asArray()->all();

        foreach ($data as $row){
            $return[$row['id']] = $row['name'];
        }
        return $return;

    }
}