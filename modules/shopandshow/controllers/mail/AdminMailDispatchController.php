<?php
namespace modules\shopandshow\controllers\mail;

use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use skeeks\cms\modules\admin\traits\AdminModelEditorStandartControllerTrait;
use modules\shopandshow\models\mail\MailDispatch;

/**
 * Class AdminOrderController
 * @package modules\shopandshow\controllers
 */
class AdminMailDispatchController extends AdminModelEditorController
{
    use AdminModelEditorStandartControllerTrait;

    public function init()
    {
        $this->modelClassName = MailDispatch::className();
        $this->name = 'Рассылки';

        parent::init();
    }

    public function actionShow()
    {
        $this->layout = null;
        $body = $this->model->body;

        // после просмотра в превью удаляем объект (одноразовый предварительный просмотр)
        $this->model->delete();

        return $body;
    }
}
