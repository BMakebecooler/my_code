<?php

namespace modules\shopandshow\controllers\mail;

use modules\shopandshow\models\mail\MailDispatch;
use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use skeeks\cms\modules\admin\traits\AdminModelEditorStandartControllerTrait;
use modules\shopandshow\models\mail\MailTemplate;
use yii\web\Response as webResponse;

/**
 * Class AdminOrderController
 *
 * @package modules\shopandshow\controllers
 */
class AdminMailTemplateController extends AdminModelEditorController
{
    use AdminModelEditorStandartControllerTrait;

    public function init()
    {
        $this->modelClassName = MailTemplate::className();
        $this->name = 'Шаблоны рассылки';

        parent::init();
    }

    public function actionGetSubject()
    {
        $beginDate = \Yii::$app->request->post('begin_date');
        $templateId = \Yii::$app->request->post('templateId');
        $mailTemplate = MailTemplate::findOne($templateId);

        if($mailTemplate) {
            $subject = $mailTemplate->getActiveSubjectByTimestamp($beginDate);
        } else {
            $subject = '';
        }

        \Yii::$app->response->format = webResponse::FORMAT_JSON;
        return $subject;
    }

    public function actionCheck()
    {
        $message = '';
        $rr = new RequestResponse();
        if ($rr->isRequestAjaxPost() && !$rr->isRequestOnValidateAjaxForm()) {
            // да, он дальше не используется, но если его не вызвать, не уйдут нужные хеадеры
            $model = $this->model;

            if ($model->load(\Yii::$app->request->post())) {
                $action = \Yii::$app->request->post('action');

                if (($action == 'send') && !$model->mail_to) {
                    $message = 'Не указан получатель';

                    return $this->render('check', ['message' => $message]);
                }

                try {
                    $mailDispatch = $model->generate(false);

                    // показываем рендеренный результат
                    if ($action == 'show') {
                        return $this->render('check', ['mail_id' => $mailDispatch->id, 'message' => $message]);
                    // шлем письмо
                    } elseif ($action == 'send') {
                        $result = $mailDispatch->send();
                        $message = $result ? 'Отправлено' : 'Не удалось отправить';
                    }
                    // шлем через getresponse
                    elseif ($action == 'send-getresponse') {
                        $result = $this->sendGetResponse($mailDispatch, 'M');
                        $message = $result ? 'Отправлено' : 'Не удалось отправить';
                    }
                } catch (\Exception $e) {
                    //return $e->getMessage();
                    $message = $e->getMessage();
                }
            }
        }

        return $this->render('check', ['message' => $message]);
    }

    /**
     * @param MailDispatch $mailDispatch
     * @param string       $campaignToken - M - токен для тестов через тех поддержку
     *
     * @return bool
     */
    protected function sendGetResponse(MailDispatch $mailDispatch, $campaignToken = 'M')
    {
        $mailDispatch->subject = '[TEST] '.$mailDispatch->subject;

        $grClient = \Yii::$app->getResponseService;
        $grClient->setCampaignToken($campaignToken);
        $createNewsLetters = $grClient->sendMailDispatch($mailDispatch);

        if (is_array($createNewsLetters) && array_key_exists('error', $createNewsLetters)) {
            $mailDispatch->setStatus(MailDispatch::STATUS_CANCEL);
            $mailDispatch->save();
            return false;
        }

        $mailDispatch->setStatus(MailDispatch::STATUS_SENT);
        return $mailDispatch->save();
    }
}
