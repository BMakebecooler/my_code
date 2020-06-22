<?php

namespace common\models\filtered\mediaplan;

use modules\shopandshow\models\mediaplan\AirDayProductTime;
use yii\data\ActiveDataProvider;

class OnAirProducts extends OnAir
{


    public function init()
    {
        parent::init();
    }

    /**
     * @param $categoryName
     * @param ActiveDataProvider $activeDataProvider
     * @return $this
     */
    public function searchCategoryTimeProducts(ActiveDataProvider $activeDataProvider)
    {
        $query = $activeDataProvider->query;

        /**
         * @var $query \yii\db\ActiveQuery
         */

        if (!$this->isBeforeYesterday() && !$this->isYesterday() && !$this->isToday()) {
            $this->defaultDate();
        }

        $query->innerJoinWith(['shopContentElement', 'shopContentElement.price', 'shopContentElement.image']);
        $query->andWhere('ss_shop_product_prices.price > 2');

        $query->andWhere('begin_datetime >= :begin_datetime AND end_datetime <= :end_datetime ', [
            ':begin_datetime' => $this->beginOfDate(),
            ':end_datetime' => $this->endOfDate(),
        ]);

        if ($this->category) {
            $query->andWhere('section_id = :section_id', [
                ':section_id' => $this->category,
            ]);
        }

        $query->orderBy('begin_datetime ASC');
        $query->groupBy('lot_id');

//       echo($query->createCommand()->rawSql);

        return $this;
    }


    public function search(ActiveDataProvider $activeDataProvider)
    {
        $query = $activeDataProvider->query;

        /**
         * @var $query \yii\db\ActiveQuery
         */

        if (!$this->isBeforeYesterday() && !$this->isYesterday() && !$this->isToday()) {
            $this->defaultDate();
        }

        $idsOnAir = AirDayProductTime::find()
            ->andWhere('begin_datetime >= :begin_datetime AND begin_datetime <= :end_datetime ', [
                ':begin_datetime' => $this->beginOfDate(),
                ':end_datetime' => $this->endOfDate(),
            ])
            ->select('lot_id')
            ->indexBy('lot_id')
            ->groupBy('lot_id')
            ->asArray()
            ->all();

        if ($idsOnAir) {
            $idsOnAir = array_keys($idsOnAir);
            $query->andWhere(['bitrix_id' => $idsOnAir]);
        }

        if ($this->inStock == 1) {
//            $query->andWhere(['>=', 'shopProduct.quantity', 1]);
        }

        return $this;
    }
}