<?php


namespace common\helpers;

use PhpOffice\PhpSpreadsheet\Reader\Xls;
use yii\db\Exception;


class Exel
{

    /**
     * Парсит xls файл и конвертирует его а массив
     *
     * @param string $src
     * @return array $data
     * @throws Exception
     */
    public static function parceExel(string $src)
    {
        if(!file_exists($src)) {
            throw new Exception('File '.$src.' is not exist');
        }

        $reader = new Xls();
        $spreadsheet = $reader->load($src);
        $sheet = $spreadsheet->getActiveSheet();
        $cells = $sheet->getCellCollection();

        $data = [];
        $highestRow = $cells->getHighestRow();
        $highestCol =  $cells->getHighestColumn();
        $highestCol =  ord(strtoupper($highestCol)) - ord('A') + 1;

        for ($row=1;$row <= $highestRow; $row++){
            for ($col = 1; $col <= $highestCol; $col++) {
                $data[$row][] = $sheet->getCellByColumnAndRow($col,$row)->getValue();
            }
        }
        return $data;
    }
}