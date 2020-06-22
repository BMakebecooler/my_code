<?php
namespace modules\shopandshow\controllers\questions;

use modules\shopandshow\models\questions\QuestionEmail;
use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use skeeks\cms\modules\admin\traits\AdminModelEditorStandartControllerTrait;

/**
 * Class AdminOrderController
 * @package modules\shopandshow\controllers
 */
class QuestionsEmailController extends AdminModelEditorController
{
    use AdminModelEditorStandartControllerTrait;

    public function init()
    {
        $this->modelClassName = QuestionEmail::className();
        $this->name = 'Адресаты для сервиса Вопрос - ответ';
        $this->modelShowAttribute = "id";

        parent::init();
    }
}
