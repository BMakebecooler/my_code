<?php

namespace modules\shopandshow\components\mail;

use modules\shopandshow\models\monitoringday\Plan;
use yii\base\Component;

class MonitoringDayHelper extends Component
{
    /**
     * @var Plan
     */
    public $plan = null;
    // час отправки
    public $hour = null;
    // часы, когда письмо в любом случае уходит
    public $okHours = [10, 12, 16, 18];
    // % отклонения, при достижении которого письмо должно отправиться
    public $okDiff = -20;

    public function __construct(Plan $plan, array $config = [])
    {
        parent::__construct($config);

        $this->plan = $plan;
    }

    public function init()
    {
        // G - 24-hour format of an hour without leading zeros
        $this->hour = date('G');

        parent::init();
    }

    public function setPlan(Plan $plan)
    {
        $this->plan = $plan;
    }

    /**
     * Нужно ли отправлять письмо
     * @return bool
     */
    public function needSendMail()
    {
        if (!$this->isWorkHour()) {
            return false;
        }

        if ($this->isOkHour()) {
            return true;
        }

        if ($this->isPlanBad()) {
            return true;
        }

        return false;
    }

    /**
     * Рабочее время
     * @return bool
     */
    public function isWorkHour()
    {
        return in_array($this->hour, range(9, 21));
    }

    /**
     * время, когда отправляем всегда
     * @return bool
     */
    public function isOkHour()
    {
        return in_array($this->hour, $this->okHours);
    }

    /**
     * Сильное отставание от плана!
     * @return bool
     */
    public function isPlanBad()
    {
        if (!$this->plan) {
            throw new \RuntimeException('Где план?');
        }

        $lastHour = $this->plan->getLastHour();
        $dataProvider = $this->plan->getDataProvider();

        foreach ($dataProvider->getModels() as $row) {
            if ($row['hour'] == $lastHour) {
                $value = 100 * ($row['sum_fact'] - $row['sum_plan']) / $row['sum_plan'];
                if ($value < $this->okDiff) {
                    return true;
                }
                break;
            }
        }
        return false;
    }
}