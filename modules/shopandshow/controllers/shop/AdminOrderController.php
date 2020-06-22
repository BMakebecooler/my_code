<?php
namespace modules\shopandshow\controllers\shop;

use common\helpers\ArrayHelper;
use modules\shopandshow\models\searches\ShopOrderSearch;
use skeeks\cms\shop\controllers\AdminOrderController as SxAdminOrderController;

/**
 * Class AdminOrderController
 * @package modules\shopandshow\controllers
 */
class AdminOrderController extends SxAdminOrderController
{
    public function actions()
    {
        $actions = ArrayHelper::merge(parent::actions(),
            [
                "index" =>
                    [
                        'modelSearchClassName' => ShopOrderSearch::className()
                    ],
            ]
        );

        return $actions;
    }

    public function init()
    {
        parent::init();
    }
}
