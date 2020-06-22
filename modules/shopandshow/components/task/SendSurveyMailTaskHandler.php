<?php

namespace modules\shopandshow\components\task;
use modules\shopandshow\models\mail\MailTemplate;
use modules\shopandshow\services\Survey;


/**
 * Class SendCoupons500rTaskHandler
 */
class SendSurveyMailTaskHandler extends BaseTaskHandler
{
    /**
     * @var string - емейл клиента кому отправляем письмо
     */
    public $email;

    /**
     * @var string - тип отправляемого письма на голосование
     */
    public $type;

    /**
     * @return bool
     */
    public function handle()
    {
        return $this->sendSurvey();
    }


    /**
     * Отправка письма на почту клиента
     * @return bool
     */
    private function sendSurvey()
    {
        //TODO Убрать заглушку когда старый сайт перестанет слушать очереди и обрабатывать данную инфу!
        //Отключено что бы оба сайта не слалили одно и то же
        return true;

        $surveyService = new Survey($this->type);

        \Yii::$app->mailer->htmlLayout = false;
        \Yii::$app->mailer->textLayout = false;

        $message = \Yii::$app->mailer->compose('@templates/mail/modules/survey/survey', [
            'data' => [
                'SUBJECT' => $surveyService->getSubject(),
                'BODY' => $surveyService->getBody()
            ]
        ])
            ->setFrom([\Yii::$app->cms->adminEmail => \Yii::$app->cms->appName])
            ->setSubject($surveyService->getSubject())
            ->setTo($this->email);


        return $message->send();
    }

}