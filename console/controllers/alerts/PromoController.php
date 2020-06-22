<?php

/**
 * php ./yii alerts/promo/
 */

namespace console\controllers\alerts;

use common\components\sms\Sms;
use console\controllers\export\ExportController;
use modules\shopandshow\models\shares\SsShare;
use yii\helpers\Console;

/**
 * Class PromoController
 * @package console\controllers
 */
class PromoController extends ExportController
{

    const CRITICAL_ALERTS_TITLE = 'AHTUNG! ';

    public function actionIndex()
    {
        $this->badBanners();
        $this->abandonedBaskets();
    }

    /**
     * Алерт когда на сайте по баннеру мало кликают
     * @return bool
     */
    private function badBanners()
    {


        return false;

        $hour = (int)date('G');
        $time = time() - (60 * 30); // пол часа днем

        if ($hour >= 0 && $hour <= 7) {
            $time = time() - (60 * 60); // час ночью
        }

        $share = SsShare::find()
            ->andWhere('ss_shares.created_at >= :max_created_at', [
                ':max_created_at' => $time
            ])->all();

        if ($share) {
            $this->stdout("ok заказы есть!\n", Console::FG_GREEN);
            return false;
        }

        $this->sendSms(self::CRITICAL_ALERTS_TITLE . ' Мало кликают по банну!', [
            '+7(977)438-80-25', //Селянский
            '+7(929)580-60-20', //Коваленко
            '+7(926)581-28-70', //Анисимов
            '+7(965)151-72-18', //Иван Гуторов
        ]);

        return true;
    }


    private function sendSms($text, $phoneList = [
        '+7(929)580-60-20', //я
        '+7(926)581-28-70', //Анисимов
        '+7(926)020-12-11', //Юра Рябов
    ])
    {
        foreach ($phoneList as $phone) {
            \Yii::$app->sms->sendSms($phone, $text, true, Sms::SMS_TYPE_FOR_ADMINS);
        }
    }


    private function sendNotifyEmail($msg, $sbj = '')
    {

        $mailSended = false;

        try {

            \Yii::$app->mailer->htmlLayout = false;
            \Yii::$app->mailer->textLayout = false;

            $emails = [
                'anisimov_da@shopandshow.ru',
                'soskov_da@shopandshow.ru',
                'ryabov_yn@shopandshow.ru'
            ];

            $subject = $sbj ? $sbj : 'Сравнение кол-ва заказов по сравнению со средними значениями';

            $message = \Yii::$app->mailer->compose()
                ->setFrom('no-reply@shopandshow.ru')
                ->setTo($emails)
                ->setSubject($subject)
                ->setTextBody($msg);

            $mailSended = $message->send();

            //$out .= 'Сообщение' . ( $mailSended ? ' отправлено успешно.' : ' не удалось отправить' );

        } catch (\Exception $exception) {
            echo $exception->getMessage();
        }

        return $mailSended;
    }

    /**
     * Проверка на частоту захода по брошеннымм корзинам
     */
    private function abandonedBaskets()
    {

        $hour = (int)date('G');

        if ($hour >= 22 && $hour <= 9) {
            return false;
        }

        $sql = <<<SQL
SELECT count(*) FROM `ss_preorders_logs` WHERE `created_at` >= :date ORDER BY `created_at` DESC LIMIT 5
SQL;

        $data = (int)\Yii::$app->db->createCommand($sql, [
            ':date' => date('Y-m-d H:i', time() - 3600)
        ])->queryScalar();

        if ($data) {
            $this->stdout("ok все норм, за брошенными корзинами приходят!\n", Console::FG_GREEN);

            return false;
        }

        $text = sprintf('За прошлый час, к нам не приходили за брошенными корзинами!');

        $this->sendSms($text, [
            '+7(926)581-28-70', //Анисимов
        ]);

    }


}
