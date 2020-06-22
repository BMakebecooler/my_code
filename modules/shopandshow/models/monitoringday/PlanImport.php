<?php

namespace modules\shopandshow\models\monitoringday;

use yii\web\UploadedFile;

class PlanImport extends \yii\base\Model
{
    /**
     * @var UploadedFile file attribute
     */
    public $file;

    public $period;
    public $type_plan;

    public function init()
    {
        parent::init();

        if (!$this->period) {
            $this->period = date('Y-m', mktime(0, 0, 0, date('m') + 1, 1, date('Y')));
        }
    }

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['file'], 'file', 'skipOnEmpty' => false],
            [['period', 'type_plan'], 'string'],
            [['period', 'type_plan'], 'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'file' => 'CSV файл',
            'period' => 'Период',
            'type_plan' => 'Тип плана',
        ];
    }

    /**
     * Загружает расписание с планом на месяц
     */
    public function import()
    {
        $data = @file($this->file->tempName);
        if (empty($data)) {
            return 'Не удалось распознать файл';
        }

        $row = array_shift($data);
        try {
            $items = $this->parseRow(1, $row, true);
        }
        catch (\Exception $e) {
            return $e->getMessage();
        }

        $result = [];
        foreach ($data as $line => $row) {
            try {
                $items = $this->parseRow($line + 2, $row);
            }
            catch (\Exception $e) {
                return $e->getMessage();
            }
            $result[] = $items;
        }

        return $this->generatePlan($result);
    }

    private function parseRow($line, $row, $header = false)
    {
        $items = preg_split('/[;]/', trim($row));
        $items = array_filter($items, function($value) {
            return ($value !== null && $value !== false && $value !== '');
        });

        $day = array_shift($items);
        $sumPlan = array_shift($items);
        $sumPlan = str_replace(' ', '', $sumPlan);

        if (sizeof($items) != 24) {
            throw new \Exception("Неверный формат файла. Формат строки: день, план, 24 столбца по часам. Строка: ".$line);
        }

        if (false == $header) {
            if (!is_numeric($day) || $day < 1 || $day > 31) {
                throw new \Exception('Неверный формат дня. Разрешен диапазон 1-31');
            }
            if (!is_numeric($sumPlan) || $sumPlan < 0) {
                throw new \Exception('Неверный формат суммы плана. Разрешено число больше 0');
            }
            if (array_sum($items) !== 100) {
                throw new \Exception("Неверная сумма процентов за день ".array_sum($items).". Строка: ".$line);
            }
        }

        foreach ($items as $i => $item) {
            if (!is_numeric($item)) {
                throw new \Exception("'$item' должно быть целым числом. Строка: ".$line.', час: '.$i);
            }
        }

        return ['day' => (int)$day, 'sum_plan' => (int)$sumPlan, 'items' => $items];
    }

    private function generatePlan(array $result)
    {
        foreach ($result as $row) {
            $date = $this->getDate($row['day']);
            $plan = PlanDay::findOne(['date' => $date, 'type_plan' => $this->type_plan]);
            if (!$plan) {
                $plan = new PlanDay(['date' => $date, 'type_plan' => $this->type_plan]);
            }
            $plan->sum_plan = $row['sum_plan'];

            if (!$plan->save()) {
                return 'Plan #'.$plan->id.PHP_EOL.print_r($plan->getErrors(), true);
            }

            foreach($row['items'] as $hour => $percent) {
                $planHour = PlanHour::find()->where(['plan_id' => $plan->id])->andWhere(['hour' => $hour])->one();
                if (!$planHour) {
                    $planHour = new PlanHour(['plan_id' => $plan->id, 'hour' => $hour]);
                }
                $planHour->percent = trim($percent);

                if (!$planHour->save()) {
                    return 'PlanHour #'.$planHour->id.PHP_EOL.print_r($plan->getErrors(), true);
                }
            }
        }

        return 'Данные загружены';
    }

    private function getDate($day)
    {
        return sprintf('%s-%02d', $this->period, $day);
    }
}