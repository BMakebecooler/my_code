<?php

namespace common\components\sale;

interface SaleChannelIntarface
{
    public function trackCheckout(int $orderId, string $label);
}