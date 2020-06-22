<?php

namespace modules\shopandshow\models\statistic;

use common\helpers\Dates;
use modules\shopandshow\models\shop\ShopOrder;
use modules\shopandshow\models\shop\ShopOrderStatus;
use skeeks\cms\models\CmsUser;
use yii\base\Model;
use yii\data\ArrayDataProvider;

class UserStatistics extends Model
{
    const GET_ALL = 'all';

    public $dateFrom;
    public $dateTo;

    public $orderSource;

    public function init()
    {

        if (!$this->dateFrom) {
            $this->dateFrom = date('Y-m-d', time() - DAYS_7);
        }

        if (!$this->dateTo) {
            $this->dateTo = date('Y-m-d');
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
            [['dateTo', 'dateFrom', 'orderSource'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'dateFrom' => 'Дата заказа С',
            'dateTo' => 'Дата заказа По',
            'orderSource' => 'Источник заказа',
        ];
    }

    /**
     * Получение пользователей и их заказов сгруппированных по статусам заказов с подсчетом статистики в группе
     * @return ArrayDataProvider
     */
    public function getOrdersByStatusData()
    {
        $query = CmsUser::find()
            ->select([
                'users.id AS user_id',
                'users.name AS user_name',
                'orders.status_code',
                'COUNT(1) AS orders_num',
                'SUM(orders.price) AS orders_sum',
                'MAX(orders.created_at) AS last_order_date'
            ])
            ->alias('users')
            ->andWhere([
                'or',
                ['not in', 'users.source_detail', [ShopOrder::SOURCE_DETAIL_FAST_ORDER, ShopOrder::SOURCE_DETAIL_FAST_ORDER_MOBILE]],
                ['users.source_detail' => null]
            ])
            ->andWhere(['!=', 'name', 'Быстрый заказ'])//не самое удачное решение фильтровать по имени,
            ->innerJoin(ShopOrder::tableName() . ' AS orders', "orders.user_id=users.id")
            ->andWhere(['>=', 'orders.created_at', Dates::beginOfDate(strtotime($this->dateFrom))])
            ->andWhere(['<=', 'orders.created_at', Dates::endOfDate(strtotime($this->dateTo))])
            ->groupBy('users.id, status_code')
            ->asArray();

        //Сайт / Телефон
        if ($this->orderSource != self::GET_ALL) {
            if ($this->orderSource == ShopOrder::SOURCE_SITE) {
                $query->andWhere(['orders.source' => ShopOrder::SOURCE_SITE]);
            } else {
                $query->andWhere(['not', ['orders.source' => ShopOrder::SOURCE_SITE]]);
            }
        }

        return new ArrayDataProvider([
            'allModels' => $query->all(),
            'pagination'    => false
        ]);
    }

    /**
     * Получение статистических данных по заказам и их выкупу разобранных по пользовательно
     * @return ArrayDataProvider
     */
    public function getOrdersCompleteData(ArrayDataProvider $dataProvider)
    {
        $usersOrdersByStatus = $dataProvider->getModels();

        //Разложим по пользоватлям и сгруппируем выполненные и не выполненные заказы
        $byCompleteStatus = [];

        if ($usersOrdersByStatus) {
            foreach ($usersOrdersByStatus as $row) {

                if (!isset($byCompleteStatus[$row['user_id']])) {
                    $byCompleteStatus[$row['user_id']] = [
                        'user_id' => $row['user_id'],
                        'user_name' => $row['user_name'],
                        'orders_total_num' => 0,
                        'orders_total_sum' => 0,
                        'orders_complete_num' => 0,
                        'orders_complete_sum' => 0,
                        'orders_complete_percent_of_total' => 0,
                        'orders_notcomplete_num' => 0,
                        'orders_notcomplete_sum' => 0,
                    ];
                }

                $orderStatusKey = $row['status_code'] == ShopOrderStatus::STATUS_COMPLETED ? 'orders_complete' : 'orders_notcomplete';

                $byCompleteStatus[$row['user_id']]["{$orderStatusKey}_num"] += $row['orders_num'];
                $byCompleteStatus[$row['user_id']]["{$orderStatusKey}_sum"] += $row['orders_sum'];

                $byCompleteStatus[$row['user_id']]['orders_total_num'] += $row['orders_num'];
                $byCompleteStatus[$row['user_id']]['orders_total_sum'] += $row['orders_sum'];
            }

            //Данные сгруппированы, считаем проценты
            foreach ($byCompleteStatus as $userId => $statData) {
                $byCompleteStatus[$userId]['orders_complete_percent_of_total'] = $statData['orders_complete_num'] / $statData['orders_total_num'] * 100;
            }
        }

        return new ArrayDataProvider([
            'allModels' => $byCompleteStatus,
            'sort'  => [
                'attributes'    => [
                    'user_id',
                    'orders_total_num' => [
                        'asc' => ['orders_total_num' => SORT_ASC],
                        'desc' => ['orders_total_num' => SORT_DESC],
                        'default' => SORT_DESC
                    ],
                    'orders_total_sum' => [
                        'asc' => ['orders_total_sum' => SORT_ASC],
                        'desc' => ['orders_total_sum' => SORT_DESC],
                        'default' => SORT_DESC
                    ],
                    'orders_complete_num' => [
                        'asc' => ['orders_complete_num' => SORT_ASC],
                        'desc' => ['orders_complete_num' => SORT_DESC],
                        'default' => SORT_DESC
                    ],
                    'orders_complete_sum' => [
                        'asc' => ['orders_complete_sum' => SORT_ASC],
                        'desc' => ['orders_complete_sum' => SORT_DESC],
                        'default' => SORT_DESC
                    ],
                    'orders_complete_percent_of_total' => [
                        'asc' => ['orders_complete_percent_of_total' => SORT_ASC],
                        'desc' => ['orders_complete_percent_of_total' => SORT_DESC],
                        'default' => SORT_DESC
                    ],
                ],
                'defaultOrder' => [
                    'orders_complete_percent_of_total' => SORT_DESC,
                ],
            ]
        ]);
    }

    /**
     * Экпорт в Excel отчета по заказам клиентов и их выкупу
     */
    public function exportOrdersComplete(ArrayDataProvider $dataProvider){
        \PhpOffice\PhpSpreadsheet\Settings::setLocale('ru');

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        $spreadsheet->getProperties()
            ->setTitle('Выкуп заказов клиентами')
            ->setSubject('Выкуп заказов клиентами');

        $spreadsheet->getActiveSheet()
            ->setTitle('Выкуп заказов клиентами');

        $spreadsheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);

        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'UserId')
            ->setCellValue('B1', 'Имя клиента')
            ->setCellValue('C1', 'Всего заказов (кол-во)')
            ->setCellValue('D1', 'Всего заказов (сумма)')
            ->setCellValue('E1', 'Выкуплено заказов (кол-во)')
            ->setCellValue('F1', 'Выкуплено заказов (сумма)')
            ->setCellValue('G1', 'Процент выкупа')
            ->setCellValue('H1', 'Не выкуплено заказов (кол-во)')
            ->setCellValue('I1', 'Не выкуплено заказов (сумма)');

        $data = ($this->getOrdersCompleteData($dataProvider))->allModels;

        foreach(range('A', 'I') as $colId) {
            $sheet->getColumnDimension($colId)->setAutoSize(true);
        }

        $sheet->fromArray($data, null, 'A2', true);
        $sheet->setAutoFilter($spreadsheet->getActiveSheet()->calculateColumnWidths()->calculateWorksheetDimension());

        $filename = "users_complete_orders_({$this->dateFrom}_-_{$this->dateTo}).xls"; //save our workbook as this file name

        // Redirect output to a client’s web browser (Xlsx)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xls');
        $writer->save('php://output');

        return;
    }

    /**
     * Подсчет общей статистики из полученных данных
     * @param ArrayDataProvider $dataProvider
     * @return array
     */
    public function getOrdersCompleteTotalStat(ArrayDataProvider $dataProvider){

        $stat = [
            'orders_num'    => [
                'total' => 0,
                'complete' => 0,
                'notcomplete' => 0,
            ],
            'orders_sum'    => [
                'total' => 0,
                'complete' => 0,
                'notcomplete' => 0,
            ],
            'complete_percent' => [
                'by_num'    => 0,
                'by_sum'    => 0,
            ]
        ];

        $models = $dataProvider->allModels;

        foreach ($models as $userId => $model) {
            $stat['orders_num']['total'] += $model['orders_total_num'];
            $stat['orders_num']['complete'] += $model['orders_complete_num'];
            $stat['orders_num']['notcomplete'] += $model['orders_notcomplete_num'];

            $stat['orders_sum']['total'] += $model['orders_total_sum'];
            $stat['orders_sum']['complete'] += $model['orders_complete_sum'];
            $stat['orders_sum']['notcomplete'] += $model['orders_notcomplete_sum'];
        }

        $stat['complete_percent']['by_num'] = $stat['orders_num']['total'] ? ($stat['orders_num']['complete'] / $stat['orders_num']['total'] * 100) : 0;
        $stat['complete_percent']['by_sum'] = $stat['orders_sum']['total'] ? ($stat['orders_sum']['complete'] / $stat['orders_sum']['total'] * 100) : 0;

        return $stat;
    }
}