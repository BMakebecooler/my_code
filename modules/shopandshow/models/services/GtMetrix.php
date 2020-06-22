<?php

namespace modules\shopandshow\models\services;

class GtMetrix extends \yii\base\Model
{

    public $month;

    public function init()
    {
        parent::init();

        if (!$this->month) {
            $this->month = date('Y-m');
        }

    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['month'], 'required'],
            [['month'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'month' => 'Месяц',
        ];
    }

    /**
     * Получить время загрузки страницы
     * @param $time
     * @return float
     */
    protected function getOnloadTime($time)
    {
        return round(((int)$time) / 1000);
    }

    public function getHighchartsData()
    {

        $dateStart = date('Y-m-01', strtotime($this->month));
        $dateEnd = date('Y-m-t', strtotime($this->month));

        $sql = <<<SQL
    SELECT DAY(FROM_UNIXTIME(created_at))as d, test_url, MAX(gt_onload_time) AS time
    FROM `ss_gt_metriks`
    WHERE  created_at >= :created_at_start AND created_at <= :created_at_end
    GROUP BY test_url
SQL;

        $data = \Yii::$app->db->createCommand($sql, [
            ':created_at_start' => strtotime($dateStart),
            ':created_at_end' => strtotime($dateEnd),
        ])->queryAll();

        $dataSeries = [];
        $drilldownSeries = [];

        foreach ($data as $item) {

            $serie = [
                'y' => $this->getOnloadTime($item['time']),
                'drilldown' => $item['test_url'],
                'name' => $item['test_url'],
            ];

            $dataSeries[] = $serie;

            $sql = <<<SQL
    SELECT id AS d, (gt_onload_time) AS time
    FROM `ss_gt_metriks`
    WHERE  created_at >= :created_at_start AND created_at <= :created_at_end AND test_url =:test_url
    ORDER BY id ASC
    -- GROUP BY DAY(FROM_UNIXTIME(created_at))
SQL;

            $drilldownData = \Yii::$app->db->createCommand($sql, [
                ':created_at_start' => strtotime($dateStart),
                ':created_at_end' => strtotime($dateEnd),
                ':test_url' => $item['test_url'],
            ])->queryAll();

            $data = array_map(function ($v) {
                return [(int)$v['d'], $this->getOnloadTime($v['time'])];
            }, $drilldownData);

            $drilldownSeries[$item['test_url']] = [
                "name" => $item['test_url'],
                "id" => $item['test_url'],
                "data" => $data
            ];
        }

        $series = [[
            'name' => 'Загрузка по страницам',
            'data' => $dataSeries,
            'colorByPoint' => true,
        ]];

        return [
            'options' => [

                'chart' => [
                    'type' => 'column'
                ],

                'title' => ['text' => 'Загрузка сайта'],

                'yAxis' => [
                    'title' => ['text' => 'Загрузка сайта']
                ],

                'xAxis' => [
                    'type' => 'category'
                ],

                'legend' => [
                    'enabled' => false
                ],

                'series' => $series,

                'drilldown' => [
                    'series' => array_values($drilldownSeries)
                ]
            ]
        ];
    }
}