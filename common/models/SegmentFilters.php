<?php


namespace common\models;


class SegmentFilters extends \common\models\generated\models\SegmentFilters
{
    public static function getOperands()
    {
        $operands = [
            '>' => '>',
            '<' => '<',
            '>=' => '>=',
            '<=' => '<=',
            'IN' => 'IN',
            'NOT IN' => 'NOT IN',
            'IS NULL' =>  'IS NULL',
            'IS NOT NULL' => 'IS NOT NULL'
        ];

        return $operands;
    }

    public static function getTables()
    {
        $tables = [
            'cms_content_element' =>  'cms_content_element' ,
            'shop_product' => 'shop_product',
        ];

        return $tables;
    }

    public static function getFilters()
    {
        $data = self::find()->orderBy('name')->asArray()->all();

        foreach ($data as $row){
            $return[$row['id']] = $row['name'];
        }
        return $return;
    }
}