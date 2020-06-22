<?php
/**
 * Created by PhpStorm.
 * User: koval
 * Date: 03.02.17
 * Time: 15:52
 */

namespace console\models\sas;


use yii\base\DynamicModel;

class ImportModel extends DynamicModel
{

    /**
     * @param array $data строка из CSV
     * @return static
     */
    static public function createFromCsvRow($data = [])
    {
        $dataForModel = [];
        foreach ($data as $number => $value) {
//            $dataForModel['column' . $number] = trim(iconv('windows-1251', 'UTF-8', $value));
            $dataForModel['column' . $number] = $value;

        }

        return new static($dataForModel);
    }


}