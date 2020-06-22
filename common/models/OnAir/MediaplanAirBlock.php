<?php


    namespace common\models\OnAir;

    use common\models\generated\models\SsMediaplanAirBlocks;
    use common\models\OnAir\query\MediaplanAirBlockQuery;

    /**
     * Class MediaplanAirBlock
     * @package common\models\OnAir
     */
    class MediaplanAirBlock extends SsMediaplanAirBlocks
    {
        /**
         * @return MediaplanAirBlockQuery
         */
        public static function find()
        {
            return new MediaplanAirBlockQuery(get_called_class());
        }

        public function isActiveTime()
        {
            $time = time();

            return $this->begin_datetime <= $time && $this->end_datetime >= $time;
        }
    }