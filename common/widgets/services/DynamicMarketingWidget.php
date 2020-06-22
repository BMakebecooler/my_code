<?php

namespace common\widgets\services;

use yii\base\Widget;

class DynamicMarketingWidget extends Widget
{

    /**
     * Название события прописывается в GTM
     * @var string
     */
    private $event = 'google_tag_params';

    /**
     * @var string
     */
    public $pageType = '';

    /**
     * @var null
     */
    public $totalValue = null;

    /**
     * @var array
     */
    public $itemIds = null;

    public function run()
    {
        if (is_array($this->itemIds)) {
            $itemIds = json_encode(array_filter($this->itemIds));
        } elseif ($this->itemIds) {
            $itemIds = $this->itemIds;
        } else {
            $itemIds = 'sx.ProductList.getList()';
        }

        $totalValue = $this->totalValue ? sprintf("'ecomm_totalvalue':%s,", $this->totalValue) : '';

        $script = <<<JS
    dataLayer.push({
       'event': '{$this->event}', 'ecomm_pagetype' : '{$this->pageType}', $totalValue 'ecomm_itemid' : {$itemIds}
    });
JS;

        return $this->view->registerJs($script);
    }
}