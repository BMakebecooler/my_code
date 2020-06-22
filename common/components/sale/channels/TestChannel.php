<?php

namespace common\components\sale\channels;

use common\components\sale\AbstractChannel;
use common\components\sale\SaleChannelIntarface;

class TestChannel extends AbstractChannel  implements saleChannelIntarface
{
    protected $label = 'test_utm';

    public function trackCheckout(array $products, array $order, string $label)
    {

    }

}