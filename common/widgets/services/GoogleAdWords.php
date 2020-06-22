<?php
namespace common\widgets\services;

use yii\base\Widget;

class GoogleAdWords extends Widget
{


    public static $isSet = false;

    /**
     * @var string
     */
    public $item = null;

    /**
     * @var int
     */
    public $price = 0;

    /**
     * @var string
     */
    public $event = null;

    public function run()
    {
        $result = '';

        switch ($this->event) {
            case 'viewItem':
$result = <<<RES
<script>
  gtag('event', 'page_view', {
    'send_to': 'AW-924324719',
    'ecomm_pagetype': 'product',
    'ecomm_prodid': '{$this->item}',
    'ecomm_totalvalue': {$this->price}
  });
</script>
RES;
                break;
        }

        self::$isSet = true;

        return $result;
    }
}