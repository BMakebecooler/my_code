<?php
namespace modules\shopandshow\services;

use uranum\delivery\services\PostNalojDelivery;

/**
 * Пока считаем так: берем результаты наложенного платежа и накручиваем стоимость
 * Class CourierDelivery
 * @package modules\shopandshow\services
 */
class CourierDelivery extends PostNalojDelivery
{
    public function calculate()
    {
        parent::calculate();

        $this->resultCost += 100;
        $this->terms = ceil((int)$this->terms/2) . ' дн.';
    }
}
