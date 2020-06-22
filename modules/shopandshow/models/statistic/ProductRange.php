<?php

namespace modules\shopandshow\models\statistic;

use common\components\mongo\Query;
use common\helpers\ArrayHelper;

/**
 * Class ProductRange
 * @package modules\shopandshow\models\statistic
 */
class ProductRange extends \yii\base\Model
{

    public $dateFrom;
    public $dateTo;

    public function init()
    {
        parent::init();

        if (!$this->dateFrom) {
            $this->dateFrom = date('Y-m-d', time() - DAYS_14);
        }

        if (!$this->dateTo) {
            $this->dateTo = date('Y-m-d');
        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['dateFrom', 'dateTo'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
        ];
    }

    /**
     * Формируем csv
     * @return string
     */
    public function getDataForExportCsv()
    {
        $query = new Query();
        $query->from('statistic_product_range_log');
        $query->andFilterCompare('date', date('Y-m-d 00:00', strtotime('- 7 days')), '>=');
        $query->orderBy('sort ASC');

        $dataLog = $query->all();

        $data = $dates = [];

        foreach ($dataLog as $item) {
            $data[$item['date']][(int)$item['sort']] = [
                'product_id' => $item['product_id'],
                'k_stock' => $item['k_stock'],
                'k_1' => $item['k_1'],
            ];
        }

        $dates = array_keys($data);

        $csvHeaders = [];

        foreach ($dates as $header) {
            $csvHeaders[] = 'date: ' . $header;
            $csvHeaders[] = 'k_stock';
            $csvHeaders[] = 'k_1';
            $csvHeaders[] = '';
        }

        $result = join(';', ArrayHelper::arrayToString($csvHeaders)) . PHP_EOL;

        for ($x = 1; $x <= 1000; $x++) {
            $csvRow = [];

            foreach ($dates as $date) {

                if (empty($data[$date][$x])) {
                    break;
                }

                $csvRow[] = $data[$date][$x]['product_id'];
                $csvRow[] = $data[$date][$x]['k_stock'];
                $csvRow[] = $data[$date][$x]['k_1'];
                $csvRow[] = '';
            }

            $result .= join(';', ArrayHelper::arrayToString($csvRow)) . PHP_EOL;
        }

        $result = iconv('UTF8', 'CP1251', $result);

        return $result;
    }
}