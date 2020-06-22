<?php
namespace modules\shopandshow\controllers\questions;

use common\models\cmsContent\ContentElementFaq;
use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use skeeks\cms\modules\admin\traits\AdminModelEditorStandartControllerTrait;
use yii\helpers\ArrayHelper;

/**
 * Class AdminOrderController
 * @package modules\shopandshow\controllers
 */
class QuestionsController extends AdminModelEditorController
{
    use AdminModelEditorStandartControllerTrait;

    public function init()
    {
        $this->modelClassName = ContentElementFaq::className();
        $this->name = 'Вопросы - ответы';
        $this->modelShowAttribute = "id";

        parent::init();
    }

    public function actions()
    {
        $actions = parent::actions();
        ArrayHelper::remove($actions, 'create');
        ArrayHelper::remove($actions, 'delete');
        return $actions;
    }

    public function actionSendBuyer()
    {
        $message = '';
        $modelId = \Yii::$app->request->get('id');
        $model = ContentElementFaq::findOne($modelId);

        $rr = new \skeeks\cms\helpers\RequestResponse();

        if (!\common\helpers\User::can(ContentElementFaq::PERM_EDIT)) {
            $message = 'Недостаточно прав';
        } elseif ($rr->isRequestAjaxPost()) {
            $message = $model->sendMailToBuyer();
        }

        return $this->renderAjax('_send-buyer', ['message' => $message, 'model' => $model]);
    }

    public function actionSendService()
    {
        $message = '';
        $modelId = \Yii::$app->request->get('id');
        $model = ContentElementFaq::findOne($modelId);

        $rr = new \skeeks\cms\helpers\RequestResponse();

        if (!\common\helpers\User::can(ContentElementFaq::PERM_EDIT)) {
            $message = 'Недостаточно прав';
        } elseif ($rr->isRequestAjaxPost()) {
            $message = $model->sendMailToService();
        }

        return $this->renderAjax('_send-service', ['message' => $message, 'model' => $model]);
    }
}
