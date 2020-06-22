<?php

namespace modules\shopandshow\components\task;


/**
 * Class SendCoupons500rTaskHandler
 */
class SendCoupons500rTaskHandler extends BaseTaskHandler
{
    /**
     * @var string - емейл клиента кому отправляем купон
     */
    public $email;

    /**
     * @var string - Код купона
     */
    public $coupon;

    /**
     * @return bool
     */
    public function handle()
    {
        return $this->sendCoupon();
    }


    /**
     * Отправка купона на почту клиента
     * @return
     */
    private function sendCoupon()
    {
        //TODO Убрать заглушку когда старый сайт перестанет слушать очереди и обрабатывать данную инфу!
        //Отключено что бы оба сайта не слалили одно и то же
//        return true;

        \Yii::$app->mailer->htmlLayout = false;
        \Yii::$app->mailer->textLayout = false;

        $devEmails = [
            'anisimov_da@shopandshow.ru',
            'soskov_da@shopandshow.ru',
//                    'panina_av@shopandshow.ru',
            'ryabov_yn@shopandshow.ru',
//                    'shanin_dv@shopandshow.ru',
//                    'Soskov_da@shopandshow.ru',
        ];

        //$this->stdout("send - " . $this->email, Console::FG_GREEN);

        $theme = sprintf('Ваш купон на 500 руб. - %s. Удачных покупок!', $this->coupon);

        $message = \Yii::$app->mailer->compose('@templates/mail/promo/coupon-500r', [
            'coupon' => $this->coupon,
            'email' => $this->email,
            'theme' => $theme,
        ])
            ->setFrom([\Yii::$app->cms->adminEmail => \Yii::$app->cms->appName])
            ->setSubject($theme);

        $debugMode = false;

        //В debug режиме не будем реально отправлять письма адресатам
        if ($debugMode) {
            //Если нужно просто сохранять в файлы - раскоментировать
            //\Yii::$app->mailer->useFileTransport = true;

            //Отправляем не адресату, а разработчикам
            $message->setTo($devEmails);
        } else {
            $message->setTo($this->email);
        }

        return $message->send();
    }

}