<?php

namespace modules\shopandshow\components\highcharts;

use common\helpers\ArrayHelper;
use miloschuman\highcharts\SeriesDataHelper as MSeriesDataHelper;
use yii\base\InvalidConfigException;

/**
 * Класс для более расширенного применения ключей в сериях
 * User: koval
 * Date: 27.09.18
 * Time: 1:30
 */
class SeriesDataHelper extends MSeriesDataHelper
{

    /**
     * Processes the source data and returns the result.
     * @return array the processed data
     */
    public function process()
    {
        if (empty($this->data)) {
            throw new InvalidConfigException('Missing required "data" property.');
        }

        $this->normalizeColumns();

        // return simple array for single-column configs
        if (count($this->columns) === 1) {
            $column = $this->columns[0];
            $data = ArrayHelper::getColumn($this->data->models, $column[0]);
            return array_map($column[1], $data);
        }

        // use two-dimensional array for multi-column configs
        $data = [];
        foreach ($this->data->models as $model) {
            $row = [];
            foreach ($this->columns as $index => $column) {
                $row[$column[0]] = call_user_func($column[1], $model[$column[0]]);
            }

            $data[] = $row;
        }

        return $data;
    }

}