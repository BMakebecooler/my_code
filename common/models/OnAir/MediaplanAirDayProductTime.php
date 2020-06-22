<?php


    namespace common\models\OnAir;

    use common\models\generated\models\SsMediaplanAirDayProductTime;
    use common\models\OnAir\query\MediaplanAirDayProductTimeQuery;

    /**
     * Class MediaplanAirDayProductTime
     * @package common\models\OnAir
     *
     * @property MediaplanAirBlock $mediaplanAirBlock
     */
    class MediaplanAirDayProductTime extends SsMediaplanAirDayProductTime
    {
        /**
         * @return MediaplanAirDayProductTimeQuery
         */
        public static function find()
        {
            return new MediaplanAirDayProductTimeQuery(get_called_class());
        }

        /**
         * @return \yii\db\ActiveQuery
         */
        public function getMediaplanAirBlock()
        {
            return $this->hasOne(MediaplanAirBlock::class, ['block_id' => 'block_id']);
        }
    }