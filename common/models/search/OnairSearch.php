<?php

namespace common\models\search;

use common\helpers\Dates;
use modules\api\resource\MPAairBlock;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class OnairSearch extends Model
{

    public function search($params = [])
    {
        $dateTimeFrom = Dates::beginOfAirDate();//1533182400
        $dateTimeTo =  Dates::endEfirPeriod();//1533567600

        $query = MPAairBlock::find()->alias('air_blocks')
            ->andWhere('air_blocks.begin_datetime >= :begin_datetime AND air_blocks.end_datetime <= :end_datetime', [
                ':begin_datetime' => $dateTimeFrom,
                ':end_datetime' => $dateTimeTo,
            ])
            ->groupBy('air_blocks.begin_datetime, air_blocks.end_datetime')
            ->orderBy('air_blocks.begin_datetime ASC');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSizeParam' => 'per_page'
            ]
        ]);

        if ($params && !($this->load($params, '') && $this->validate())) {
            return $dataProvider;
        }

        return $dataProvider;
    }
}