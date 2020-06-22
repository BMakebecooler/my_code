<?php

namespace modules\shopandshow\controllers\shares;

use modules\shopandshow\models\shares\SsShareType;
use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use skeeks\cms\modules\admin\traits\AdminModelEditorStandartControllerTrait;

/**
 * Class AdminSharesTypesController
 *
 * @package modules\shopandshow\controllers
 */
class AdminSharesTypesController extends AdminModelEditorController
{
    use AdminModelEditorStandartControllerTrait;

    public function init()
    {
        $this->modelClassName = SsShareType::className();
        $this->name = 'Типы баннеров';
        $this->modelShowAttribute = "id";

        parent::init();
    }
}
