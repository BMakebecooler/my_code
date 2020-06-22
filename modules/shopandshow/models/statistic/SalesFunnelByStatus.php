<?php

namespace modules\shopandshow\models\statistic;

use common\helpers\Dates;
use modules\shopandshow\models\shop\ShopOrder;
use modules\shopandshow\models\shop\ShopOrderChange;
use modules\shopandshow\models\shop\ShopOrderStatus;
use yii\base\Model;
use yii\data\ArrayDataProvider;
use yii\web\JsExpression;

class SalesFunnelByStatus extends Model
{
    const GET_ALL = 'all';

    public $dateTo;
    public $dateFrom;

    public $sourceDetail;
    public $userType;
    public $orderSource;

    /*
N	Принят, ожидается оплата	            Заказ принят, но пока не обрабатывается (например, заказ только что создан или ожидается оплата зака...
Q	Отправлен в очередь	 
B	Заказ пришел в удаленную систему	    когда заказ виден в админке удаленной системы
D	Редактируется	                        Заказ еще формируется на стороне клиента
G	Проверен	                            Заказ прошел проверку
R	Готов к формированию отправлений	    Заказ готов к формированию отправлений
T	Отправлен	                            отправлены посылки
P	Отправка заказа завершилась неудачей	 
F	Выполнен	                            Заказ доставлен и оплачен
C	Отменен	 
*/

    //Основная задача - выбрать заказы за период и сгруппировать по статусам подсчитывая различную статистику
    public $statusesForReport = [
        ShopOrderStatus::STATUS_WAIT_PAY,
        //ShopOrderStatus::STATUS_SUCCESS,
        ShopOrderStatus::STATUS_CHECKED,
        ShopOrderStatus::STATUS_TRAVEL,
        ShopOrderStatus::STATUS_COMPLETED,
        ShopOrderStatus::STATUS_CANCELED,
    ];

    public function init()
    {

        if (!$this->dateFrom) {
            $this->dateFrom = date('Y-m-d', time() - DAYS_30);
        }

        if (!$this->dateTo) {
            $this->dateTo = date('Y-m-d');
        }

        if (!$this->sourceDetail) {
            $this->sourceDetail = self::GET_ALL;
        }

        if (!$this->userType) {
            $this->userType = self::GET_ALL;
        }

        if (!$this->orderSource) {
            $this->orderSource = self::GET_ALL;
        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['dateTo', 'dateFrom', 'sourceDetail', 'userType', 'orderSource'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'dateFrom' => 'Дата С',
            'dateTo' => 'Дата По',
            'sourceDetail' => 'Версия сайта',
            'userType' => 'Тип пользователя',
            'orderSource' => 'Как создан заказ',
        ];
    }

    /**
     * Получение данных для отчета по воронке продаж в разрезе статусов заказов
     * @return ArrayDataProvider
     */
    public function getByStatusDataProvider()
    {
        //Вилка дат определяет только даты создания заказов, статусы все, без ограничений по времени

        //Подзапрос выборки нужных нам заказов (по вилке дат)
        $getOrdersIdsSubQuery = ShopOrder::find()
            ->select('id')
            ->andWhere(['>=', 'created_at', Dates::beginOfDate(strtotime($this->dateFrom))])
            ->andWhere(['<=', 'created_at', Dates::endOfDate(strtotime($this->dateTo))]);

        //Подзапрос схлопывающий дубли статусов для одного и того же заказа
        $getOrdersStatusesSubQuery = ShopOrderChange::find()
            ->where(['shop_order_id' => $getOrdersIdsSubQuery])
            ->groupBy(['shop_order_id', 'status_code']);

        $data = ShopOrder::find()
            ->alias('orders')
            ->select([
                'COUNT(1) AS num',
                'status_changelog.status_code',
                'SUM(orders.price) AS sum'
            ])
            ->andWhere([
                'or',
                ['and', ['status_changelog.type' => 'ORDER_ADDED'], ['status_changelog.status_code' => ShopOrderStatus::STATUS_WAIT_PAY]],
                ['and', ['status_changelog.type' => 'ORDER_STATUS_CHANGED'], ['not', ['status_changelog.status_code' => null]]],
            ])
            ->innerJoin(['status_changelog' => $getOrdersStatusesSubQuery], "orders.id=status_changelog.shop_order_id")
            ->groupBy(['status_changelog.status_code'])
            ->asArray();

        //* ДопФильтры *//

        //Мобильный / Не мобильный
        if ($this->sourceDetail != self::GET_ALL) {
            $mobileOrdersSources = [
                ShopOrder::SOURCE_DETAIL_MOBILE,
                ShopOrder::SOURCE_DETAIL_FAST_ORDER_MOBILE,
                ShopOrder::SOURCE_DETAIL_ONE_CLICK_MOBILE,
            ];

            if ($this->userType == ShopOrder::SOURCE_DETAIL_FAST_ORDER) {
                $data->andWhere(['orders.source_detail' => $mobileOrdersSources]);
            } else {
                $data->andWhere(['not', ['orders.source_detail' => $mobileOrdersSources]]);
            }
        }

        //Быстрый / Не быстрый
        if ($this->userType != self::GET_ALL) {
            if ($this->userType == ShopOrder::SOURCE_DETAIL_FAST_ORDER) {
                $data->andWhere(['orders.source_detail' => ShopOrder::SOURCE_DETAIL_FAST_ORDER]);
            } else {
                $data->andWhere(['not', ['orders.source_detail' => ShopOrder::SOURCE_DETAIL_FAST_ORDER]]);
            }
        }

        //Сайт / Телефон
        if ($this->orderSource != self::GET_ALL) {
            if ($this->orderSource == ShopOrder::SOURCE_SITE) {
                $data->andWhere(['orders.source' => ShopOrder::SOURCE_SITE]);
            } else {
                $data->andWhere(['not', ['orders.source' => ShopOrder::SOURCE_SITE]]);
            }
        }

        return new ArrayDataProvider([
            'allModels' => $data->all()
        ]);
    }

    /**
     * Из исходных данных по статусам заказов готовит пригодный для HighCharts вид данных
     * @param array $data
     * @return array
     */
    public function getSeriesForHighcharts(array $data)
    {
        $seriesData = [];

        $ordersStatuses = ShopOrderStatus::find()->select('name, code, color')->asArray()->indexBy('code')->all();

        //Серии разбиваем по статусам (категориям)
        $byStatus = [];

        if ($data) {
            foreach ($data as $datum) {
                if (!isset($byStatus[$datum['status_code']])) {
                    $byStatus[$datum['status_code']] = [
                        'name' => $ordersStatuses[$datum['status_code']]['name'],
                        //'color'  => $datum['status_color'],
                        'num' => 0,
                        'sum' => 0,
                    ];
                }
                $byStatus[$datum['status_code']]['num'] += $datum['num'];
                $byStatus[$datum['status_code']]['sum'] += $datum['sum'];
            }
        }

        //Данные переформатировали и произвели подсчеты, собираем в формат для HC

        //Храним данные первого статуса для отсчета как от "0" метки
        $firstStatusOrdersData = [
            'orders_num' => 0,
            'orders_sum' => 0
        ];

        //Массив для хранения временных данных предыдущего статуса
        $prevStatusOrdersData = $firstStatusOrdersData;
        foreach ($this->statusesForReport AS $reportStatusCode) {

            $ordersNum = !empty($byStatus[$reportStatusCode]['num']) ? $byStatus[$reportStatusCode]['num'] : 0;
            $ordersNumDiffFromPrev = $prevStatusOrdersData['orders_num'] ? $prevStatusOrdersData['orders_num'] - $ordersNum : 0;
            $ordersSum = !empty($byStatus[$reportStatusCode]['sum']) ? $byStatus[$reportStatusCode]['sum'] : 0;
            $ordersSumFormated = \Yii::$app->formatter->asDecimal($ordersSum);

            $ordersNumPercentFromFirst = \Yii::$app->formatter
                ->asPercent($firstStatusOrdersData['orders_num'] ? $ordersNum / $firstStatusOrdersData['orders_num'] : 1, 2);

            $ordersSumPercentFromFirst = \Yii::$app->formatter
                ->asPercent($firstStatusOrdersData['orders_sum'] ? $ordersSum / $firstStatusOrdersData['orders_sum'] : 1, 2);

            $ordersNumPercentFromPrev = \Yii::$app->formatter
                ->asPercent($prevStatusOrdersData['orders_num'] ? 1 - ($ordersNum / $prevStatusOrdersData['orders_num']) : 0, 2);

            $ordersSumPercentFromPrev = \Yii::$app->formatter
                ->asPercent($prevStatusOrdersData['orders_sum'] ? 1 - ($ordersSum / $prevStatusOrdersData['orders_sum']) : 0, 2);

            //* Для информационного блока *//

            //запишем данные первого статуса попутно проверив наличие данных в принципе
            if (!empty($byStatus[$reportStatusCode]) && empty($firstStatusOrdersData['orders_num'])) {
                $firstStatusOrdersData['orders_num'] = $ordersNum;
                $firstStatusOrdersData['orders_sum'] = $ordersSum;
            }

            $prevStatusOrdersData['orders_num'] = $ordersNum;
            $prevStatusOrdersData['orders_sum'] = $ordersSum;

            //* /Для информационного блока *//

            $seriesData[] = [
                'name' => "{$ordersStatuses[$reportStatusCode]['name']} [{$reportStatusCode}]<br>-{$ordersNumDiffFromPrev} заказов<br>-{$ordersNumPercentFromPrev} в штуках<br>-{$ordersSumPercentFromPrev} в руб.",
                'y' => $ordersNum,
                'status_top_info' => "<table class='sales-funnel-top-info'><tr><td>{$ordersNum}</td><td>{$ordersNumPercentFromFirst}</td></tr> <tr><td>{$ordersSumFormated}</td><td>{$ordersSumPercentFromFirst}</td></tr></table>",
            ];
        }

        $series = [
            [
                'name' => 'Заказы по статусам',
                'colorByPoint' => true,
                'data' => $seriesData,
            ]
        ];

        return $series;
    }

    /**
     * Получение массива опций для highcharts
     * @param $series
     * @param $drilldownSeries
     * @return array
     */
    public function getHighchartsConfig($series, $options = [])
    {
        $optionsDefault = [
            'options' => [
                'chart' => [
                    'type' => 'column',
                ],
                'title' => ['text' => 'Воронка заказов по статусам'],
                'xAxis' => [
                    'title' => ['text' => 'Статус'],
                    'type' => 'category',
                ],
                'yAxis' => [
                    [
                        'title' => ['text' => 'Кол-во заказов, шт.'],
                    ]
                ],
                'plotOptions' => [
                    'series' => [
                        'dataLabels' => [
                            'enabled' => true,
                            'useHTML' => true,
                            'formatter' => new JsExpression('function(){ return this.point.status_top_info; }')
                        ],
                    ],
                ],
                'legend' => [
                    'enabled' => false
                ],
                'series' => $series,
                'tooltip' => [
                    //'visible'   => false,
                    //'formatter' => new JsExpression('function(){ return this.point.status_top_info; }')
                ],
            ]
        ];

        return array_replace_recursive($optionsDefault, $options);
    }
}

?>