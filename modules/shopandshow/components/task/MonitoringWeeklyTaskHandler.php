<?php

namespace modules\shopandshow\components\task;

use common\models\cmsContent\CmsContentElement;
use modules\shopandshow\models\monitoringday\PlanWeekly;
use modules\shopandshow\models\shop\ShopProduct;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

/**
 * Class MonitoringWeeklyTaskHandler
 */
class MonitoringWeeklyTaskHandler extends BaseTaskHandler
{
    const RUR_FORMAT = '#,##0'."[\$ руб.-419]";

    /**
     * @var string - дата начала формирования отчета
     */
    public $date_from;
    /**
     * @var string - дата окончания формирования отчета
     */
    public $date_to;
    /**
     * @var string - емейл для отправки резульататов отчета
     */
    public $email;

    /**
     * @return bool
     */
    public function handle()
    {
        $spreadsheet = $this->createSheet();

        return $this->sendToEmail($spreadsheet);
    }

    /**
     * Иницализация движка, установка заголовков, наполнение содержимым
     * @return \PhpOffice\PhpSpreadsheet\Spreadsheet
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function createSheet()
    {
        \PhpOffice\PhpSpreadsheet\Settings::setLocale('ru');

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        $spreadsheet->getProperties()
            ->setTitle($this->getSubject())
            ->setSubject($this->getSubject());

        $spreadsheet->getActiveSheet()
            ->setTitle('Еженедельный отчет');

        $spreadsheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);

        $this->render($spreadsheet);

        return $spreadsheet;
    }

    /**
     * рендер содержимого xls файла
     * @param \PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet
     */
    private function render($spreadsheet)
    {

        $model = \Yii::createObject(PlanWeekly::className());
        \Yii::configure($model, ['date_from' => $this->date_from, 'date_to' => $this->date_to]);
        $model->initData();

        $sheet = $spreadsheet->getActiveSheet();

        $col = 0;
        //$sheet->freezePane('C9');
        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getColumnDimension('B')->setWidth(30);
        foreach ($model->plans as $plan) {
            $col++;
            $sheet->getColumnDimensionByColumn($col * 2 + 1)->setWidth(12);
            $sheet->getColumnDimensionByColumn($col * 2 + 2)->setWidth(12);
        }
        $col++;
        $sheet->getColumnDimensionByColumn($col * 2 + 1)->setWidth(20);
        $sheet->getColumnDimensionByColumn($col * 2 + 2)->setWidth(20);

        $row = 1;
        $sheet->setCellValue("C{$row}", 'ОТЧЕТ, ' . $model::formatDate($model->date_from) . ' - ' . $model::formatDate($model->date_to));
        $sheet->getCell("C{$row}")->getStyle()->getFont()->getColor()->setARGB(Color::COLOR_RED);
        $sheet->getCell("C{$row}")->getStyle()->getFont()->setBold(true)->setSize(14);
        $sheet->getCell("C{$row}")->getStyle()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->mergeCells("C{$row}:P{$row}");


        $row++;
        $cellBigDataSum = [9, $row];
        $sheet->setCellValue("C{$row}", 'Средний доход в день за последние ' . PlanWeekly::BIG_DATA_DAYS .' дней: ');
        $sheet->getStyle("C{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue("I{$row}", $model->getBigData('orders_avg_sum'));
        $sheet->getStyle("I{$row}")->getNumberFormat()->setFormatCode(self::RUR_FORMAT);
        $sheet->getStyle("I{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->mergeCells("C{$row}:H{$row}");
        $sheet->mergeCells("I{$row}:J{$row}");
        $sheet->getStyle("C{$row}:P{$row}")->getFont()->setSize(11)->setBold(true)->getColor()->setARGB(Color::COLOR_RED);

        $col = 0;
        $row++;
        $rowBorderBegin = $row;
        $sheet->setCellValue("A{$row}", 'дата');
        $sheet->mergeCells("A{$row}:B{$row}");
        foreach ($model->plans as $plan) {
            $col++;
            $sheet->setCellValueExplicitByColumnAndRow($col * 2 + 1, $row, $model::formatDate($plan->date), DataType::TYPE_STRING);
            $sheet->getCellByColumnAndRow($col * 2 + 1, $row)->getStyle()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->mergeCellsByColumnAndRow($col * 2 + 1, $row, $col * 2 + 2, $row);
        }

        $col = 0;
        $row++;
        $sheet->setCellValue("A{$row}", 'день недели');
        $sheet->mergeCells("A{$row}:B{$row}");
        foreach ($model->plans as $plan) {
            $col++;
            $sheet->setCellValueExplicitByColumnAndRow($col * 2 + 1, $row, $model::getDayOfWeek($plan->date, false), DataType::TYPE_STRING);
            $sheet->mergeCellsByColumnAndRow($col * 2 + 1, $row, $col * 2 + 2, $row);
        }
        $sheet->getStyle("M{$row}:P{$row}")->getFont()->getColor()->setARGB(Color::COLOR_RED);
        $col++;
        $sheet->setCellValueByColumnAndRow($col*2 + 1, $row, 'Итого');
        $sheet->getStyleByColumnAndRow($col*2 + 1, $row)->getFont()->setSize(11)->setBold(true);

        $col = 0;
        $row++;
        $sheet->setCellValue("A{$row}", 'Итого, в день сайт (план)');
        $sheet->mergeCells("A{$row}:B{$row}");
        foreach ($model->plans as $plan) {
            $col++;
            $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, $plan->sum_plan);
            $sheet->mergeCellsByColumnAndRow($col * 2 + 1, $row, $col * 2 + 2, $row);
        }
        $col++;
        $cellSumPlan = [$col * 2 + 2, $row];
        $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, 'План');
        $sheet->setCellValueByColumnAndRow($col * 2 + 2, $row, $this->getSumFormula([3, $row], [$col * 2, $row]));
        $sheet->getStyle("C{$row}:R{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:R{$row}")->getFont()->setSize(11);
        $sheet->getStyle("C{$row}:R{$row}")->getNumberFormat()->setFormatCode(self::RUR_FORMAT);

        $col = 0;
        $row++;
        $sheet->setCellValue("A{$row}", 'Итого, в день сайт (факт)');
        $sheet->mergeCells("A{$row}:B{$row}");
        foreach ($model->plans as $plan) {
            $col++;
            $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, $model->getData('orders_sum', $plan->date));
            $sheet->mergeCellsByColumnAndRow($col * 2 + 1, $row, $col * 2 + 2, $row);
        }
        $col++;
        $cellSumFact = [$col * 2 + 2, $row];
        $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, 'Факт');
        $sheet->setCellValueByColumnAndRow($col * 2 + 2, $row, $this->getSumFormula([3, $row], [$col * 2, $row]));
        $sheet->getStyle("C{$row}:R{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:R{$row}")->getFont()->setSize(11);
        $sheet->getStyle("C{$row}:R{$row}")->getNumberFormat()->setFormatCode(self::RUR_FORMAT);

        $col = 0;
        $row++;
        $sheet->setCellValue("A{$row}", '% от плана (сайт) - факт');
        $sheet->mergeCells("A{$row}:B{$row}");
        foreach ($model->plans as $plan) {
            $col++;
            $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, $this->getDivFormula([$col * 2 + 1, $cellSumFact[1]], [$col * 2 + 1, $cellSumPlan[1]]));
            $sheet->mergeCellsByColumnAndRow($col * 2 + 1, $row, $col * 2 + 2, $row);
        }
        $col++;
        $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, '% выполнения');
        $sheet->setCellValueByColumnAndRow($col * 2 + 2, $row, $this->getDivFormula($cellSumFact, $cellSumPlan));
        $sheet->getStyle("C{$row}:R{$row}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE);
        $sheet->getStyle("C{$row}:R{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:R{$row}")->getFont()->setSize(11);

        $col = 0;
        $row++;
        $sheet->setCellValue("A{$row}", '% по сравнению со средним значением дохода за последние 4 недели');
        $sheet->mergeCells("A{$row}:B{$row}");
        foreach ($model->plans as $plan) {
            $col++;
            $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, $this->getDivFormula([$col * 2 + 1, $cellSumFact[1]], $cellBigDataSum));
            $sheet->mergeCellsByColumnAndRow($col * 2 + 1, $row, $col * 2 + 2, $row);
        }
        $sheet->getStyle("C{$row}:R{$row}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE);
        $sheet->getStyle("C{$row}:R{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:R{$row}")->getFont()->setSize(11);

        $col = 0;
        $row++;
        $sheet->setCellValue("A{$row}", 'Сумма доставки');
        $sheet->mergeCells("A{$row}:B{$row}");
        foreach ($model->plans as $plan) {
            $delivery = $model->getData('orders_delivery_sum', $plan->date);
            $col++;
            $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, $delivery);
            $sheet->mergeCellsByColumnAndRow($col * 2 + 1, $row, $col * 2 + 2, $row);
        }
        $col++;
        $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, 'Доставка');
        $sheet->setCellValueByColumnAndRow($col * 2 + 2, $row, $this->getSumFormula([3, $row], [$col * 2, $row]));
        $sheet->getStyle("C{$row}:R{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:R{$row}")->getFont()->setSize(11);
        $sheet->getStyle("C{$row}:R{$row}")->getNumberFormat()->setFormatCode(self::RUR_FORMAT);


        /*$row++;
        $sheet->setCellValue("A{$row}", 'прогноз - красные зоны');
        $sheet->mergeCells("A{$row}:B{$row}");
        $sheet->getStyle("A{$row}:P{$row}")->getFont()->setSize(11);*/


        $col = 0;
        $row++;
        $sheet->setCellValue("A{$row}", 'Маржа (руб)');
        $sheet->mergeCells("A{$row}:B{$row}");
        foreach ($model->plans as $plan) {
            $col++;
            $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, $model->getData('marge', $plan->date));
            $sheet->mergeCellsByColumnAndRow($col * 2 + 1, $row, $col * 2 + 2, $row);
        }
        $col++;
        $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, 'Маржа (руб)');
        $sheet->setCellValueByColumnAndRow($col * 2 + 2, $row, $this->getSumFormula([3, $row], [$col * 2, $row]));
        $sheet->getStyle("C{$row}:R{$row}")->getNumberFormat()->setFormatCode(self::RUR_FORMAT);
        $sheet->getStyle("C{$row}:R{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:R{$row}")->getFont()->setSize(11);


        $col = 0;
        $row++;
        $sheet->setCellValue("A{$row}", 'Маржа (%)');
        $sheet->mergeCells("A{$row}:B{$row}");
        foreach ($model->plans as $plan) {
            $col++;
            $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, $this->getDivFormula([$col * 2 + 1, $row - 1], [$col * 2 + 1, $cellSumFact[1]]));
            $sheet->mergeCellsByColumnAndRow($col * 2 + 1, $row, $col * 2 + 2, $row);
        }
        $col++;
        $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, 'Маржа (%)');
        $sheet->setCellValueByColumnAndRow($col * 2 + 2, $row, $this->getDivFormula([$col * 2 + 2, $row - 1], [$col * 2 + 2, $cellSumFact[1]]));
        $sheet->getStyle("C{$row}:R{$row}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE);
        $sheet->getStyle("C{$row}:R{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:R{$row}")->getFont()->setSize(11);

        if ($model->getPlansEfir()) {
            $col = 0;
            $row++;
            $sheet->setCellValue("A{$row}", 'Итого, в день эфир (план)');
            $sheet->mergeCells("A{$row}:B{$row}");
            foreach ($model->getPlansEfir() as $plan) {
                $col++;
                $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, $plan->sum_plan);
                $sheet->mergeCellsByColumnAndRow($col * 2 + 1, $row, $col * 2 + 2, $row);
            }
            $col++;
            $cellSumPlanEfir = [$col * 2 + 2, $row];
            $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, 'План');
            $sheet->setCellValueByColumnAndRow($col * 2 + 2, $row, $this->getSumFormula([3, $row], [$col * 2, $row]));
            $sheet->getStyle("C{$row}:R{$row}")->getFont()->setBold(true);
            $sheet->getStyle("A{$row}:R{$row}")->getFont()->setSize(11);
            $sheet->getStyle("C{$row}:R{$row}")->getNumberFormat()->setFormatCode(self::RUR_FORMAT);

            $col = 0;
            $row++;
            $sheet->setCellValue("A{$row}", 'Итого, в день эфир (факт)');
            $sheet->mergeCells("A{$row}:B{$row}");
            foreach ($model->getPlansEfir() as $plan) {
                $col++;
                $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, $model->getData('efir_total_sum', $plan->date));
                $sheet->mergeCellsByColumnAndRow($col * 2 + 1, $row, $col * 2 + 2, $row);
            }
            $col++;
            $cellSumFactEfir = [$col * 2 + 2, $row];
            $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, 'Факт');
            $sheet->setCellValueByColumnAndRow($col * 2 + 2, $row, $this->getSumFormula([3, $row], [$col * 2, $row]));
            $sheet->getStyle("C{$row}:R{$row}")->getFont()->setBold(true);
            $sheet->getStyle("A{$row}:R{$row}")->getFont()->setSize(11);
            $sheet->getStyle("C{$row}:R{$row}")->getNumberFormat()->setFormatCode(self::RUR_FORMAT);

            $col = 0;
            $row++;
            $sheet->setCellValue("A{$row}", '% от плана (эфир) - факт');
            $sheet->mergeCells("A{$row}:B{$row}");
            foreach ($model->getPlansEfir() as $plan) {
                $col++;
                $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, $this->getDivFormula([$col * 2 + 1, $cellSumFactEfir[1]], [$col * 2 + 1, $cellSumPlanEfir[1]]));
                $sheet->mergeCellsByColumnAndRow($col * 2 + 1, $row, $col * 2 + 2, $row);
            }
            $col++;
            $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, '% выполнения');
            $sheet->setCellValueByColumnAndRow($col * 2 + 2, $row, $this->getDivFormula($cellSumFactEfir, $cellSumPlanEfir));
            $sheet->getStyle("C{$row}:R{$row}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE);
            $sheet->getStyle("C{$row}:R{$row}")->getFont()->setBold(true);
            $sheet->getStyle("A{$row}:R{$row}")->getFont()->setSize(11);
        }

        $col = 0;
        $row++;
        $sheet->setCellValue("A{$row}", 'Кол-во проданных товаров');
        $sheet->mergeCells("A{$row}:B{$row}");
        foreach ($model->plans as $plan) {
            $col++;
            $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, $model->getData('baskets_quantity', $plan->date));
            $sheet->mergeCellsByColumnAndRow($col * 2 + 1, $row, $col * 2 + 2, $row);
        }
        $col++;
        $cellCountBaskets = [$col * 2 + 2, $row];
        $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, 'Всего товаров');
        $sheet->setCellValueByColumnAndRow($col * 2 + 2, $row, $this->getSumFormula([3, $row], [$col * 2, $row]));


        $col = 0;
        $row++;
        $sheet->setCellValue("A{$row}", 'Кол-во заказов');
        $sheet->mergeCells("A{$row}:B{$row}");
        foreach ($model->plans as $plan) {
            $col++;
            $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, $model->getData('orders_count', $plan->date));
            $sheet->mergeCellsByColumnAndRow($col * 2 + 1, $row, $col * 2 + 2, $row);
        }
        $col++;
        $cellCountOrders = [$col * 2 + 2, $row];
        $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, 'Всего заказов');
        $sheet->setCellValueByColumnAndRow($col * 2 + 2, $row, $this->getSumFormula([3, $row], [$col * 2, $row]));

        $col = 0;
        $row++;
        $sheet->setCellValue("A{$row}", 'Средний чек');
        $sheet->mergeCells("A{$row}:B{$row}");
        foreach ($model->plans as $plan) {
            $col++;
            $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, $this->getDivFormula([$col * 2 + 1, $cellSumFact[1]], [$col * 2 + 1, $cellCountOrders[1]]));
            $sheet->mergeCellsByColumnAndRow($col * 2 + 1, $row, $col * 2 + 2, $row);
        }
        $col++;
        $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, 'Средний чек');
        $sheet->setCellValueByColumnAndRow($col * 2 + 2, $row, $this->getDivFormula($cellSumFact, $cellCountOrders));
        $sheet->getStyle("C{$row}:R{$row}")->getNumberFormat()->setFormatCode(self::RUR_FORMAT);


        $col = 0;
        $row++;
        $sheet->setCellValue("A{$row}", 'Среднее кол-во товаров в чеке');
        $sheet->mergeCells("A{$row}:B{$row}");
        foreach ($model->plans as $plan) {
            $col++;
            $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, $this->getDivFormula([$col * 2 + 1, $cellCountBaskets[1]], [$col * 2 + 1, $cellCountOrders[1]]));
            $sheet->mergeCellsByColumnAndRow($col * 2 + 1, $row, $col * 2 + 2, $row);
        }
        $col++;
        $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, 'Среднее кол-во товаров');
        $sheet->setCellValueByColumnAndRow($col * 2 + 2, $row, $this->getDivFormula($cellCountBaskets, $cellCountOrders));
        $sheet->getStyle("C{$row}:R{$row}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        $sheet->getStyle("A{$rowBorderBegin}:P{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);


        $col = 0;
        $row+=3;
        $rowBorderBegin = $row;
        $sheet->setCellValue("A{$row}", '1. Акция');
        $sheet->mergeCells("A{$row}:B{$row}");
        foreach ($model->plans as $plan) {
            $value = '';
            if ($shopDiscounts = $model->getData('discounts', $plan->date, [])) {
                $value = join("\n", array_column($shopDiscounts, 'name'));
            }
            $col++;
            $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, $value);
            $sheet->mergeCellsByColumnAndRow($col * 2 + 1, $row, $col * 2 + 2, $row);
            $sheet->getStyleByColumnAndRow($col * 2 + 1, $row)->getAlignment()->setWrapText(true)->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        $sheet->getRowDimension($row)->setRowHeight(-1);

        $col = 0;
        $row++;
        $sheet->setCellValue("A{$row}", 'Акция эфира');
        $sheet->mergeCells("A{$row}:B{$row}");
        foreach ($model->plans as $plan) {
            $value = '';
            if ($airActions = $model->getData('air_actions', $plan->date, [])) {
                $value = join("\n", $airActions);
            }
            $col++;
            $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, $value);
            $sheet->mergeCellsByColumnAndRow($col * 2 + 1, $row, $col * 2 + 2, $row);
            $sheet->getStyleByColumnAndRow($col * 2 + 1, $row)->getAlignment()->setWrapText(true)->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        $sheet->getRowDimension($row)->setRowHeight(-1);

        $row+=2;
        $sheet->setCellValue("A{$row}", '2. Цтс');
        $sheet->getStyle("A{$row}")->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
        $sheet->mergeCells("A{$row}:A".($row+7));

        $col = 0;
        $sheet->setCellValue("B{$row}", 'какой');
        foreach ($model->plans as $plan) {
            /** @var CmsContentElement $cts */
            $cts = $model->getData('cts', $plan->date, null);
            $value = $cts ? sprintf('%s [%s]', $cts->name, $cts->relatedPropertiesModel->getAttribute('LOT_NUM')) : '(нет)';

            $col++;
            $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, $value);
            $sheet->mergeCellsByColumnAndRow($col * 2 + 1, $row, $col * 2 + 2, $row);
            $sheet->getStyleByColumnAndRow($col * 2 + 1, $row)->getAlignment()->setWrapText(true)->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        $sheet->getRowDimension($row)->setRowHeight(-1);

        $col = 0;
        $row++;
        $sheet->setCellValue("B{$row}", 'цена');
        foreach ($model->plans as $plan) {
            /** @var ShopProduct $ctsProduct */
            $ctsProduct = $model->getData('cts_product', $plan->date, null);
            $value = $ctsProduct ? $ctsProduct->getProductPriceByType('TODAY')->price : 0;

            $col++;
            $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, $value);
            $sheet->mergeCellsByColumnAndRow($col * 2 + 1, $row, $col * 2 + 2, $row);
            $sheet->getStyleByColumnAndRow($col * 2 + 1, $row)->getNumberFormat()->setFormatCode(self::RUR_FORMAT);
            $sheet->getStyleByColumnAndRow($col * 2 + 1, $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $col = 0;
        $row++;
        $sheet->setCellValue("B{$row}", 'сумма дохода по ЦТС САЙТ');
        foreach ($model->plans as $plan) {
            $col++;
            $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, $model->getData('cts_sum', $plan->date));
            $sheet->mergeCellsByColumnAndRow($col * 2 + 1, $row, $col * 2 + 2, $row);
            $sheet->getStyleByColumnAndRow($col * 2 + 1, $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyleByColumnAndRow($col * 2 + 1, $row)->getNumberFormat()->setFormatCode(self::RUR_FORMAT);
        }
        $col++;
        $cellSumCtsSite = [$col * 2 + 2, $row];
        $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, 'Всего цтс сайт');
        $sheet->setCellValueByColumnAndRow($col * 2 + 2, $row, $this->getSumFormula([3, $row], [$col * 2, $row]));
        $sheet->getStyleByColumnAndRow($col * 2 + 2, $row)->getNumberFormat()->setFormatCode(self::RUR_FORMAT);
        $sheet->getStyleByColumnAndRow($col * 2 + 2, $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $col = 0;
        $row++;
        $sheet->setCellValue("B{$row}", 'сумма дохода по ЦТС ЭФИР');
        foreach ($model->plans as $plan) {
            $col++;
            $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, $model->getData('efir_cts_sum', $plan->date));
            $sheet->mergeCellsByColumnAndRow($col * 2 + 1, $row, $col * 2 + 2, $row);
            $sheet->getStyleByColumnAndRow($col * 2 + 1, $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyleByColumnAndRow($col * 2 + 1, $row)->getNumberFormat()->setFormatCode(self::RUR_FORMAT);
        }
        $col++;
        $cellSumCtsEfir = [$col * 2 + 2, $row];
        $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, 'Всего цтс эфир');
        $sheet->setCellValueByColumnAndRow($col * 2 + 2, $row, $this->getSumFormula([3, $row], [$col * 2, $row]));
        $sheet->getStyleByColumnAndRow($col * 2 + 2, $row)->getNumberFormat()->setFormatCode(self::RUR_FORMAT);
        $sheet->getStyleByColumnAndRow($col * 2 + 2, $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $col = 0;
        $row++;
        $sheet->setCellValue("B{$row}", 'доля ЦТС на сайте (ср. знач - 10%)');
        foreach ($model->plans as $plan) {
            $col++;
            $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, $this->getDivFormula([$col * 2 + 1, $cellSumCtsSite[1]], [$col * 2 + 1, $cellSumFact[1]]));
            $sheet->mergeCellsByColumnAndRow($col * 2 + 1, $row, $col * 2 + 2, $row);
            $sheet->getStyleByColumnAndRow($col * 2 + 1, $row)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE);
            $sheet->getStyleByColumnAndRow($col * 2 + 1, $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        $col++;
        $cellPercentCtsSite = [$col * 2 + 2, $row];
        $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, 'Доля цтс на сайте');
        $sheet->setCellValueByColumnAndRow($col * 2 + 2, $row, $this->getDivFormula($cellSumCtsSite, $cellSumFact));
        $sheet->getStyleByColumnAndRow($col * 2 + 2, $row)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE);
        $sheet->getStyleByColumnAndRow($col * 2 + 2, $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        if ($model->getPlansEfir()) {
            $col = 0;
            $row++;
            $sheet->setCellValue("B{$row}", 'доля ЦТС в эфире (норма - 20%)');
            foreach ($model->plans as $plan) {
                $col++;
                $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, $this->getDivFormula([$col * 2 + 1, $cellSumCtsEfir[1]], [$col * 2 + 1, $cellSumFactEfir[1]]));
                $sheet->mergeCellsByColumnAndRow($col * 2 + 1, $row, $col * 2 + 2, $row);
                $sheet->getStyleByColumnAndRow($col * 2 + 1, $row)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE);
                $sheet->getStyleByColumnAndRow($col * 2 + 1, $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }
            $col++;
            $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, 'Доля цтс в эфире');
            $sheet->setCellValueByColumnAndRow($col * 2 + 2, $row, $this->getDivFormula($cellSumCtsEfir, $cellSumFactEfir));
            $sheet->getStyleByColumnAndRow($col * 2 + 2, $row)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE);
            $sheet->getStyleByColumnAndRow($col * 2 + 2, $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $col = 0;
        $row++;
        $sheet->setCellValue("B{$row}", 'отклонение от нормы - факт');
        foreach ($model->plans as $plan) {
            $col++;
            $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, $this->getCellValue([$col * 2 + 1, $cellPercentCtsSite[1]]) . ' - 0.1');
            $sheet->mergeCellsByColumnAndRow($col * 2 + 1, $row, $col * 2 + 2, $row);
            $sheet->getStyleByColumnAndRow($col * 2 + 1, $row)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE);
            $sheet->getStyleByColumnAndRow($col * 2 + 1, $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        $col++;
        $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, 'Отклонение от нормы');
        $sheet->setCellValueByColumnAndRow($col * 2 + 2, $row, $this->getCellValue($cellPercentCtsSite) .' - 0.1');
        $sheet->getStyleByColumnAndRow($col * 2 + 2, $row)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE);
        $sheet->getStyleByColumnAndRow($col * 2 + 2, $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $col = 0;
        $row++;
        $sheet->setCellValue("B{$row}", 'отклонение от нормы - прогноз');
        foreach ($model->plans as $plan) {
            $col++;
            //$sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, '(не реализовано)');
            $sheet->mergeCellsByColumnAndRow($col * 2 + 1, $row, $col * 2 + 2, $row);
            $sheet->getStyleByColumnAndRow($col * 2 + 1, $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $sheet->getStyle("A{$rowBorderBegin}:P{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $row+=3;
        $rowBorderBegin = $row;
        $sheet->setCellValue("A{$row}", '3. По категориям');
        $sheet->mergeCells("A{$row}:B{$row}");

        $dailyAirBlock = $model->getDailyAirBlocks();
        foreach ($model->getCategories() as $rootCategory) {
            $col = 0;
            $row++;
            $sheet->setCellValueByColumnAndRow($col + 1, $row, $rootCategory->name);
            $sheet->mergeCellsByColumnAndRow($col + 1, $row, $col + 2, $row);

            $childCategories = $model->getCategories($rootCategory->id);

            foreach ($model->plans as $plan) {
                $col++;
                $isCategoryOnair = array_key_exists($rootCategory->id, $dailyAirBlock[$plan->date]);
                $sumForCategory = $model->getSumForCategory($plan->date, $rootCategory->id);

                if ($childCategories) {
                    $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, $this->getSumFormula([$col * 2 + 1, $row + 1], [$col * 2 + 1, $row + sizeof($childCategories)]));
                }
                else {
                    $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, $sumForCategory);
                }
                $sheet->getStyleByColumnAndRow($col * 2 + 1, $row)->getNumberFormat()->setFormatCode(self::RUR_FORMAT);
                if ($isCategoryOnair) {
                    $sheet->getStyleByColumnAndRow($col * 2 + 1, $row)->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FF90EE90');
                }

                $sheet->setCellValueByColumnAndRow($col * 2 + 2, $row, $sumForCategory - $model->getCategoryAvg($rootCategory->id, $isCategoryOnair, $dailyAirBlock[$plan->date]));
                $sheet->getStyleByColumnAndRow($col * 2 + 2, $row)->getNumberFormat()->setFormatCode(self::RUR_FORMAT);
            }
            $col++;
            $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, 'Итого');
            $sheet->setCellValueByColumnAndRow($col * 2 + 2, $row, $this->getPartialSumFormula([3, $row], [$col * 2, $row]));
            $sheet->getStyleByColumnAndRow($col * 2 + 2, $row)->getNumberFormat()->setFormatCode(self::RUR_FORMAT);

            foreach ($childCategories as $childCategory) {
                $col = 0;
                $row++;
                $sheet->setCellValueByColumnAndRow($col + 1, $row, $childCategory->name);
                $sheet->mergeCellsByColumnAndRow($col + 1, $row, $col + 2, $row);
                $sheet->getStyleByColumnAndRow($col + 1, $row)->getFont()->setSize(8)->setItalic(true)->getColor()->setARGB('FF888888');

                foreach ($model->plans as $plan) {
                    $col++;
                    $sumForCategory = $model->getSumForCategory($plan->date, $childCategory->id);
                    $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, $sumForCategory);
                    $sheet->getStyleByColumnAndRow($col * 2 + 1, $row)->getNumberFormat()->setFormatCode(self::RUR_FORMAT);
                    $sheet->getStyleByColumnAndRow($col * 2 + 1, $row)->getFont()->setSize(8)->setItalic(true)->getColor()->setARGB('FF888888');
                }
                $col++;
                $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, 'Итого');
                $sheet->setCellValueByColumnAndRow($col * 2 + 2, $row, $this->getPartialSumFormula([3, $row], [$col * 2, $row]));
                $sheet->getStyleByColumnAndRow($col * 2 + 2, $row)->getNumberFormat()->setFormatCode(self::RUR_FORMAT);
                $sheet->getStyleByColumnAndRow($col * 2 + 2, $row)->getFont()->setSize(8)->setItalic(true)->getColor()->setARGB('FF888888');
            }
        }

        $sheet->getStyle("A{$rowBorderBegin}:P{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);


        $row+=3;
        $rowBorderBegin = $row;
        $col = 0;
        $sheet->setCellValueByColumnAndRow($col + 1, $row, '4. Объём часов по рубрикам');
        $sheet->mergeCellsByColumnAndRow($col + 1, $row, $col + 2, $row);

        $airBlocks = $model->getAirBlocksData();
        foreach ($airBlocks as $items) {
            $col = 0;
            foreach ($model->plans as $plan) {
                $col++;
                $category = isset($items[$plan->date]) ? key($items[$plan->date]) : '';
                $value = isset($items[$plan->date]) ? current($items[$plan->date]) : '';

                $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, $category);
                $sheet->setCellValueByColumnAndRow($col * 2 + 2, $row, $value);
            }
            $row++;
        }
        $row--;

        $sheet->getStyle("A{$rowBorderBegin}:P{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $row+=3;
        $rowBorderBegin = $row;
        $col = 0;
        foreach ($model->plans as $plan) {
            $col++;

            $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, 'руб');
            $sheet->setCellValueByColumnAndRow($col * 2 + 2, $row, 'шт');

            $sheet->getStyleByColumnAndRow($col * 2 + 1, $row)->getFont()->setBold(true);
            $sheet->getStyleByColumnAndRow($col * 2 + 2, $row)->getFont()->setBold(true);
        }
        $col++;

        $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, 'всего руб');
        $sheet->setCellValueByColumnAndRow($col * 2 + 2, $row, 'всего шт');

        $sheet->getStyleByColumnAndRow($col * 2 + 1, $row)->getFont()->setBold(true);
        $sheet->getStyleByColumnAndRow($col * 2 + 2, $row)->getFont()->setBold(true);

        $row++;
        $col = 0;
        $sheet->setCellValueByColumnAndRow($col + 1, $row, 'Продажи товаров из ПЭ');
        $sheet->mergeCellsByColumnAndRow($col + 1, $row, $col + 2, $row);
        foreach ($model->plans as $plan) {
            $col++;
            $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, $model->getData('baskets_onair_sum', $plan->date));
            $sheet->setCellValueByColumnAndRow($col * 2 + 2, $row, $model->getData('baskets_onair_quantity', $plan->date));
            $sheet->getStyleByColumnAndRow($col * 2 + 1, $row)->getNumberFormat()->setFormatCode(self::RUR_FORMAT);
        }
        $col++;
        $cellSumOnair = [$col * 2 + 1, $row];
        $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, $this->getPartialSumFormula([3, $row], [$col * 2 + 1, $row]));
        $sheet->setCellValueByColumnAndRow($col * 2 + 2, $row, $this->getPartialSumFormula([4, $row], [$col * 2 + 2, $row]));
        $sheet->getStyleByColumnAndRow($col * 2 + 1, $row)->getNumberFormat()->setFormatCode(self::RUR_FORMAT);

        $row++;
        $col = 0;
        $sheet->setCellValueByColumnAndRow($col + 1, $row, 'Продажи товаров из акций (кроме ПЭ)');
        $sheet->mergeCellsByColumnAndRow($col + 1, $row, $col + 2, $row);
        foreach ($model->plans as $plan) {
            $col++;
            $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, $model->getData('baskets_onbanner_sum', $plan->date));
            $sheet->setCellValueByColumnAndRow($col * 2 + 2, $row, $model->getData('baskets_onbanner_quantity', $plan->date));
            $sheet->getStyleByColumnAndRow($col * 2 + 1, $row)->getNumberFormat()->setFormatCode(self::RUR_FORMAT);
        }
        $col++;
        $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, $this->getPartialSumFormula([3, $row], [$col * 2 + 1, $row]));
        $sheet->setCellValueByColumnAndRow($col * 2 + 2, $row, $this->getPartialSumFormula([4, $row], [$col * 2 + 2, $row]));
        $sheet->getStyleByColumnAndRow($col * 2 + 1, $row)->getNumberFormat()->setFormatCode(self::RUR_FORMAT);

        $row++;
        $col = 0;
        $sheet->setCellValueByColumnAndRow($col + 1, $row, 'Продажи остальное');
        $sheet->mergeCellsByColumnAndRow($col + 1, $row, $col + 2, $row);
        foreach ($model->plans as $plan) {
            $col++;
            $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, $model->getData('baskets_other_sum', $plan->date));
            $sheet->setCellValueByColumnAndRow($col * 2 + 2, $row, $model->getData('baskets_other_quantity', $plan->date));
            $sheet->getStyleByColumnAndRow($col * 2 + 1, $row)->getNumberFormat()->setFormatCode(self::RUR_FORMAT);
        }
        $col++;
        $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, $this->getPartialSumFormula([3, $row], [$col * 2 + 1, $row]));
        $sheet->setCellValueByColumnAndRow($col * 2 + 2, $row, $this->getPartialSumFormula([4, $row], [$col * 2 + 2, $row]));
        $sheet->getStyleByColumnAndRow($col * 2 + 1, $row)->getNumberFormat()->setFormatCode(self::RUR_FORMAT);

        $row++;
        $col = 0;
        $sheet->setCellValueByColumnAndRow($col + 1, $row, '% продаж товаров ПЭ от общего дохода сайта');
        $sheet->mergeCellsByColumnAndRow($col + 1, $row, $col + 2, $row);
        foreach ($model->plans as $plan) {
            $col++;
            $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, $this->getDivFormula([$col * 2 + 1, $cellSumOnair[1]], [$col * 2 + 1, $cellSumFact[1]]));
            $sheet->mergeCellsByColumnAndRow($col * 2 + 1, $row, $col * 2 + 2, $row);
            $sheet->getStyleByColumnAndRow($col * 2 + 1, $row)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE);
            $sheet->getStyleByColumnAndRow($col * 2 + 1, $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        $col++;
        $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, 'Итого продаж');
        $sheet->setCellValueByColumnAndRow($col * 2 + 2, $row, $this->getDivFormula($cellSumOnair, $cellSumFact));
        $sheet->getStyleByColumnAndRow($col * 2 + 2, $row)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE);
        $sheet->getStyleByColumnAndRow($col * 2 + 2, $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row++;
        $col = 0;
        $sheet->setCellValueByColumnAndRow($col + 1, $row, 'Трафик (сеансы)');
        $sheet->mergeCellsByColumnAndRow($col + 1, $row, $col + 2, $row);
        foreach ($model->plans as $plan) {
            $col++;
            $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, $model->getData('sessions', $plan->date));
            $sheet->mergeCellsByColumnAndRow($col * 2 + 1, $row, $col * 2 + 2, $row);
            $sheet->getStyleByColumnAndRow($col * 2 + 1, $row)->getNumberFormat()->setFormatCode(self::RUR_FORMAT);
            $sheet->getStyleByColumnAndRow($col * 2 + 1, $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        $col++;
        $sheet->setCellValueByColumnAndRow($col * 2 + 1, $row, 'Итого трафик');
        $sheet->setCellValueByColumnAndRow($col * 2 + 2, $row, $this->getSumFormula([3, $row], [$col * 2, $row]));
        $sheet->getStyleByColumnAndRow($col * 2 + 2, $row)->getNumberFormat()->setFormatCode(self::RUR_FORMAT);
        $sheet->getStyleByColumnAndRow($col * 2 + 2, $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle("A{$rowBorderBegin}:P{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    }

    /**
     * тема сообщения
     * @return string
     */
    private function getSubject()
    {
        return 'Еженедельный отчет с ' . $this->date_from . ' по ' . $this->date_to;
    }

    /**
     * Отправка файла на почту
     * @param $spreadsheet
     * @return bool
     */
    private function sendToEmail($spreadsheet)
    {
        $attach = $this->writeToFile($spreadsheet, $this->getSubject());

        return \Yii::$app->mailer->compose()
            ->setTo($this->email)
            ->setSubject($this->getSubject())
            ->setTextBody($this->getSubject())
            ->attach($attach)
            ->send();
    }

    /**
     * Вставляет формулу суммы ячеек вида =SUM(А1:Е9)
     * @param array $cellFromArr
     * @param array $cellToArr
     * @return string
     */
    private function getSumFormula(array $cellFromArr, array $cellToArr)
    {
        $cellFrom = Coordinate::stringFromColumnIndex($cellFromArr[0]).$cellFromArr[1];
        $cellTo = Coordinate::stringFromColumnIndex($cellToArr[0]).$cellToArr[1];
        return sprintf('=SUM(%s:%s)', $cellFrom, $cellTo);
    }

    /**
     * Вставляет поштучную сумму ячеек из диапазона вида =А1+B1+C1
     * @param array $cellFromArr
     * @param array $cellToArr
     * @param int $offset
     * @return string
     */
    private function getPartialSumFormula(array $cellFromArr, array $cellToArr, $offset = 2)
    {
        $range = $cellToArr[0] - $cellFromArr[0];
        $cellRange = [];
        for($i=0; $i<$range; $i+= $offset) {
            $cellRange[] = Coordinate::stringFromColumnIndex($cellFromArr[0] + $i).$cellFromArr[1];
        }
        return sprintf('=%s', join('+', $cellRange));
    }

    private function getDivFormula(array $cellFromArr, array $cellToArr)
    {
        $cellFrom = Coordinate::stringFromColumnIndex($cellFromArr[0]).$cellFromArr[1];
        $cellTo = Coordinate::stringFromColumnIndex($cellToArr[0]).$cellToArr[1];
        return sprintf('=%s/%s', $cellFrom, $cellTo);
    }

    private function getCellValue(array $cellArr)
    {
        $cell = Coordinate::stringFromColumnIndex($cellArr[0]).$cellArr[1];
        return sprintf('=%s', $cell);
    }

    /**
     * сохранение файла
     * @param $spreadsheet
     * @param $fileName
     * @return string
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    private function writeToFile($spreadsheet, $fileName)
    {
        $path = \Yii::getAlias('@runtime');
        $path = $path . DIRECTORY_SEPARATOR . 'xlsx';
        if (!is_dir($path)) {
            mkdir($path);
        }
        $fullpath = $path . DIRECTORY_SEPARATOR . 'task_' . $this->taskModel->id . '_' . $fileName . '.xlsx';

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        //$writer->setPreCalculateFormulas(true);
        $writer->save($fullpath);

        return $fullpath;
    }
}