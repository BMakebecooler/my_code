<?php

namespace modules\shopandshow\models\services;

use yii\helpers\Json;
use yii\httpclient\Client;
use common\lists\TreeList;
use common\models\api\ga\v4\GoogleAnalytics;
use miloschuman\highcharts\SeriesDataHelper;
use modules\shopandshow\models\mediaplan\AirBlock;
use modules\shopandshow\models\mediaplan\AirDayProductTime;
use modules\shopandshow\models\shares\SsShare;
use modules\shopandshow\models\shop\ShopBasket;
use modules\shopandshow\models\shop\ShopOrder;
use skeeks\cms\components\Cms;

class Sms extends \yii\base\Model
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


    public function getHighchartsData()
    {

        $dateStart = date('Y-m-01', strtotime($this->month));
        $dateEnd = date('Y-m-t', strtotime($this->month));

        $sql = <<<SQL
SELECT DAY((created_at)) as day, count(*) AS cnt
FROM sms
WHERE  created_at >= :created_at_start  AND  created_at <= :created_at_end
GROUP BY DAY((created_at))
SQL;

        $data = \Yii::$app->db->createCommand($sql, [
            ':created_at_start' => ($dateStart),
            ':created_at_end' => ($dateEnd),
        ])->queryAll();


        $series[1]['name'] = 'Кол-во отправок смс в день ';
        $series[1]['data'] = [];

        foreach ($data as $item) {
            $series[1]['data'][] = (int)$item['cnt'];
        }

        return [
            'options' => [

                'chart' => [
                    'type' => 'line'
                ],

                'title' => ['text' => 'Кол-во смс'],

                'yAxis' => [
                    'title' => ['text' => 'Кол-во смс']
                ],

                'series' => array_values($series)
            ]
        ];
    }
    
}