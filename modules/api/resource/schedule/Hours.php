<?php


namespace modules\api\resource\schedule;


class Hours extends \common\models\SsMediaplanAirBlocks
{
    public function fields()
    {
        return [
            'id' => 'block_id',
            'time' => function(){
                return $this->getTimePeriod();
            },
            'title' => 'section_name',
            'active' => function(){
                return (bool)($this->begin_datetime < time() && $this->end_datetime > time());
            }
        ];
    }
}