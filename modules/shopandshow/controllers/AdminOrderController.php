<?php
namespace modules\shopandshow\controllers;

use skeeks\cms\models\Comment;
use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use skeeks\cms\modules\admin\traits\AdminModelEditorStandartControllerTrait;
use skeeks\cms\shop\models\ShopOrder;
use Yii;
use skeeks\cms\models\searchs\User as UserSearch;
use yii\helpers\ArrayHelper;

/**
 * Class AdminOrderController
 * @package modules\shopandshow\controllers
 */
class AdminOrderController extends AdminModelEditorController
{
    use AdminModelEditorStandartControllerTrait;

    public function init()
    {
        $this->modelClassName = ShopOrder::className();
        $this->name = 'Заказы';
        $this->modelShowAttribute = "id";

        parent::init();
    }

    public function actions()
    {
        $actions = parent::actions();
        ArrayHelper::remove($actions, 'create');
        return $actions;
    }
}
