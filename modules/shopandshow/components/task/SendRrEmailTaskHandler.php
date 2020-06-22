<?php

namespace modules\shopandshow\components\task;


/**
 * Class SendRrEmailTaskHandler
 */
class SendRrEmailTaskHandler extends BaseTaskHandler
{
    /**
     * @var string - email клиента кому отправляем email
     */
    public $email;

    /**
     * @var - код шаблона по которому надо произвести отправку
     */
    public $template;

    /**
     * @return bool
     */
    public function handle()
    {
        return $this->sendEmail();
    }

    /**
     * Отправка приветственного письма после подписки/регистрации
     * @return
     */
    private function sendEmail()
    {
        $debugMode = false;
        $sended = false;

        if ($debugMode){
            $devEmails = [
                'anisimov_da@shopandshow.ru',
                'soskov_da@shopandshow.ru',
//                    'panina_av@shopandshow.ru',
                'ryabov_yn@shopandshow.ru',
//                    'shanin_dv@shopandshow.ru',
            ];

            if ($devEmails){
                foreach ($devEmails as $devEmail) {
                    $sended = \Yii::$app->retailRocketService->sendEmailWithTemplate($devEmail, $this->template);
                }
            }

        }else{
            $sended = \Yii::$app->retailRocketService->sendEmailWithTemplate($this->email, $this->template);
        }

        return $sended;
    }

}