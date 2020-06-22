<?php
/**
 * Created by PhpStorm.
 * User: ubuntu5
 * Date: 26.05.17
 * Time: 16:25
 */

namespace common\widgets\cart;

use common\widgets\shop\ShopGlobal;
use skeeks\cms\base\WidgetRenderable;


class ShopCart extends WidgetRenderable
{

    /**
     * Подключить стандартные скрипты
     * @var bool
     */
    public $allowRegisterAsset = true;

    /**
     * Глобавльные опции магазина
     * @var array
     */
    public $shopClientOptions = [];


    /**
     * Подготовка данных для шаблона
     * @return $this
     */
    public function run()
    {
        if ($this->allowRegisterAsset)
        {
            ShopGlobal::widget(['clientOptions' => $this->shopClientOptions]);
        }

        return parent::run();
    }

}