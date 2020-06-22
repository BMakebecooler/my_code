<?php

namespace modules\shopandshow\components\task;


/**
 * Class SendMailGunEmailTaskHandler
 */
class SendMailGunEmailTaskHandler extends BaseTaskHandler
{
    /**
     * @var string - email клиента кому отправляем email
     */
    public $email;

    /**
     * @var string - название класса сущности для передачи в шаблон
     */
    public $composeClass;

    /**
     * @var string - название сущности сущности для передачи в шаблон
     */
    public $composeEntity;

    /**
     * @var int - ассоциативный массив с обьектом сущности для передачи в шаблон
     */
    public $composeEntityId;

    /**
     * @var - код шаблона по которому надо произвести отправку
     */
    public $template;

    /**
     * @var - Тема письма
     */
    public $subject;

    /**
     * @return bool
     */
    public function handle()
    {
        return $this->sendEmail();
    }

    /**
     * Отправка  письма
     * @return
     */
    private function sendEmail()
    {
           $obj = \Yii::createObject($this->composeClass);
           $obj = $obj->findOne($this->composeEntityId);
           return \Yii::$app->mailer->compose($this->template, [$this->composeEntity => $obj])
                ->setFrom([\Yii::$app->cms->adminEmail => \Yii::$app->cms->appName . ''])
                ->setTo($this->email)
                ->setSubject($this->subject)
                ->send();
    }

}