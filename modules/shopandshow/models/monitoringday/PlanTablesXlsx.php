<?php
namespace modules\shopandshow\models\monitoringday;

use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;
use yii\base\Model;

class PlanTablesXlsx extends Model
{
    /** @var PlanTables  */
    public $model;

    private $row = 1;
    private $onairCategories = [];
    private $categories = [];

    public function __construct(PlanTables $model, $config = [])
    {
        $this->model = $model;

        parent::__construct($config);
    }

    public function init()
    {
        parent::init();

        $this->loadCategories();
    }

    public function download()
    {

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="tables.xlsx"');

        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0

        $spreadsheet = $this->createSheet();

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');

        \Yii::$app->end();
    }

    /**
     * создает xlsx документ
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function createSheet()
    {
        \PhpOffice\PhpSpreadsheet\Settings::setLocale('ru');

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        $spreadsheet->getProperties()
            ->setTitle('Табличный отчет за период с '.$this->model->date_from.' по '.$this->model->date_to)
            ->setSubject('Табличный отчет за период с '.$this->model->date_from.' по '.$this->model->date_to);

        $spreadsheet->getActiveSheet()
            ->setTitle('Табличный отчет');

        $spreadsheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);

        $this->render($spreadsheet);

        return $spreadsheet;
    }

    /**
     * Рендерит наполнение таблицы
     * @param \PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet
     * @throws \Exception
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \Throwable
     */
    private function render(\PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet)
    {
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->getColumnDimension('A')->setWidth(20);

        $col = 1;
        $sheet->setCellValueByColumnAndRow($col, $this->getRow(), 'ОТЧЕТ ЗА ПЕРИОД С '.$this->model->date_from. ' ПО '.$this->model->date_to);
        $sheet->getStyleByColumnAndRow($col, $this->getRow())->getFont()->setBold(true)->getColor()->setARGB(Color::COLOR_RED);
        $this->nextRow();

        if ($this->model->showCts && $this->model->isOneDay()) {
            $this->renderCts($spreadsheet);
            $this->nextRow();
        }

        $tables = [
            ['onair' => null, 'title' => 'Все товары'],
            ['onair' => true, 'title' => 'Товары в эфире'],
            ['onair' => false, 'title' => 'Товары НЕ в эфире'],
        ];

        foreach ($tables as $table) {
            $this->renderTable($spreadsheet, $table);
            $this->nextRow();
        }

    }

    /**
     * Формирует табличку с цтс
     * @param \PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function renderCts(\PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet)
    {
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValueByColumnAndRow(1, $this->getRow(), 'Товар Цтс');
        $sheet->getStyleByColumnAndRow(1, $this->getRow())->getFont()->setSize(20);
        $sheet->getRowDimension($this->getRow())->setRowHeight(25);
        $this->nextRow();

        $this->renderHeader($spreadsheet);

        $cts = \modules\shopandshow\lists\Shares::getCtsProduct($this->model->date_from);
        $ctsSales = \common\helpers\ArrayHelper::map($this->model->getBasketCtsSales(), 'order_date', 'product_price');

        $col = 1;
        $sheet->setCellValueByColumnAndRow($col, $this->getRow(), $cts ? $cts->product->name : '(цтс не найден)');

        foreach ($this->onairCategories as $onairTime => $onairCategory) {
            $col++;
            $sheet->setCellValueByColumnAndRow($col, $this->getRow(), $ctsSales[$onairTime/1000] ?? 0);
        }
        $col++;
        $sheet->setCellValueByColumnAndRow($col, $this->getRow(), $ctsSales ? array_sum($ctsSales) : 0);
        $sheet->getStyleByColumnAndRow(2, $this->getRow(), $col, $this->getRow())->getNumberFormat()->setFormatCode('# ##0');

        $this->nextRow();
    }

    /**
     * Формирует табличку с данными
     * @param \PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet
     * @param $table
     * @throws \Exception
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \Throwable
     */
    private function renderTable(\PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet, $table)
    {
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValueByColumnAndRow(1, $this->getRow(), $table['title']);
        $sheet->getStyleByColumnAndRow(1, $this->getRow())->getFont()->setSize(20);
        $sheet->getRowDimension($this->getRow())->setRowHeight(25);
        $this->nextRow();

        $this->renderHeader($spreadsheet);

        $onAirSum = [];

        foreach ($this->categories as $category) {
            $col = 1;
            $products = $this->model->getBasketProductSalesData($category->id, $table['onair']);

            $sheet->setCellValueByColumnAndRow($col, $this->getRow(), $category->name);

            foreach ($this->onairCategories as $onairTime => $onairCategory) {
                $col++;
                $onAirSum[$onairTime] = ($onAirSum[$onairTime] ?? 0) + ($products[$onairTime/1000] ?? 0);
                $sheet->setCellValueByColumnAndRow($col, $this->getRow(), $products[$onairTime/1000] ?? 0);
            }
            $col++;
            $sheet->setCellValueByColumnAndRow($col, $this->getRow(), $products ? array_sum($products) : 0);
            $sheet->getStyleByColumnAndRow(2, $this->getRow(), $col, $this->getRow())->getNumberFormat()->setFormatCode('# ##0');
            $this->nextRow();
        }

        // доставка
        if ($table['onair'] === null) {
            $col = 1;
            $deliverySum = $this->model->getOrdersDeliveryData();
            $sheet->setCellValueByColumnAndRow($col, $this->getRow(), 'Доставка');
            foreach ($this->onairCategories as $onairTime => $onairCategory) {
                $col++;
                $sheet->setCellValueByColumnAndRow($col, $this->getRow(), @$deliverySum[$onairTime/1000]);
            }
            $col++;
            $sheet->setCellValueByColumnAndRow($col, $this->getRow(), $deliverySum ? array_sum($deliverySum) : 0);
            $sheet->getStyleByColumnAndRow(2, $this->getRow(), $col, $this->getRow())->getNumberFormat()->setFormatCode('# ##0');
            $this->nextRow();
        }

        $col = 1;
        $sheet->setCellValueByColumnAndRow($col, $this->getRow(), 'Сумма');
        foreach ($this->onairCategories as $onairTime => $onairCategory) {
            $col++;
            $sheet->setCellValueByColumnAndRow($col, $this->getRow(), $onAirSum[$onairTime]);
        }
        $col++;
        $sheet->setCellValueByColumnAndRow($col, $this->getRow(), $onAirSum ? array_sum($onAirSum) : 0);
        $sheet->getStyleByColumnAndRow(2, $this->getRow(), $col, $this->getRow())->getNumberFormat()->setFormatCode('# ##0');

        $this->nextRow();
    }

    /**
     * Формирует шапку таблицы
     * @param \PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function renderHeader(\PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet)
    {
        $sheet = $spreadsheet->getActiveSheet();

        $col = 1;
        $sheet->setCellValueByColumnAndRow($col, $this->getRow(), 'Рубрика');
        foreach ($this->onairCategories as $onairCategory) {
            $col++;
            $sheet->setCellValueByColumnAndRow($col, $this->getRow(), $onairCategory);
        }
        $col++;
        $sheet->setCellValueByColumnAndRow($col, $this->getRow(), 'Сумма');

        $row = $sheet->getStyleByColumnAndRow(1, $this->getRow(), $col, $this->getRow());
        $row->getFont()->setBold(true);
        $row->getAlignment()->setWrapText(true)->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension($this->getRow())->setRowHeight(50);

        $this->nextRow();
    }

    /**
     * Загружает список категорий из модели
     */
    private function loadCategories()
    {
        $this->onairCategories = array_map(function($row) {
            return str_replace('<br>', PHP_EOL, $row);
        }, $this->model->getOnairCategories());

        $this->categories = $this->model->getCategories();
    }

    /**
     * получает текущую строку
     * @return int
     */
    private function getRow()
    {
        return $this->row;
    }

    /**
     * устанавливает указатель на следующую строку
     */
    private function nextRow()
    {
        $this->row++;
    }
}