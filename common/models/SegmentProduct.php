<?php


namespace common\models;


class SegmentProduct extends \common\models\generated\models\SegmentProduct
{
    public static function getProductsCount($segment_id)
    {
        $count = SegmentProduct::find()
            ->select(['product_id'])
            ->andWhere(['=','segment_id',$segment_id])
            ->count();

        return $count;
    }

    public static function getProducts($segmentId,$limit = null)
    {
        if($limit){
            $data = SegmentProduct::find()
                ->select(['product_id'])
                ->andWhere(['segment_id' => $segmentId])
                ->limit($limit)
                ->asArray()
                ->all();
        }else {
            $data = SegmentProduct::find()
                ->select(['product_id'])
                ->andWhere(['segment_id' => $segmentId])
                ->asArray()
                ->all();
        }
        $return = [];
        foreach ($data as $row){
            $return[] = $row['product_id'];
        }
        return $return;
    }
}