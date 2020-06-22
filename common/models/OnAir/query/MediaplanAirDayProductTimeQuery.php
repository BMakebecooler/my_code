<?php


    namespace common\models\OnAir\query;


    use common\models\generated\query\SsMediaplanAirBlocksQuery;

    class MediaplanAirDayProductTimeQuery extends SsMediaplanAirBlocksQuery
    {
        protected $tableName;

        public function init ()
        {
            parent::init(); // TODO: Change the autogenerated stub
            $this->tableName = $this->modelClass::tableName();
        }

        /**
         * @param $min
         * @param $max
         *
         * @return MediaplanAirDayProductTimeQuery
         */
        public function beginDatetimeBetween($min, $max)
        {
            return $this->andWhere(['between', "{$this->tableName}.begin_datetime", $min, $max]);
        }

        /**
         * @param $id
         *
         * @return MediaplanAirDayProductTimeQuery
         */
        public function block($id)
        {
            return $this->andWhere([
                "{$this->tableName}.block_id" => (int) $id
            ]);
        }
        /**
         * @param $id
         *
         * @return MediaplanAirDayProductTimeQuery
         */
        public function section($id)
        {
            return $this->andWhere([
                "{$this->tableName}.section_id" => (int) $id
            ]);
        }
    }