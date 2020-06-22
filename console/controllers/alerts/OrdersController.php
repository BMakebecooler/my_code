<?php

/**
 * php ./yii alerts/orders/bad-orders
 * php ./yii alerts/orders/test-sms
 * php ./yii alerts/orders/sales-alert-day-hour
 * php ./yii alerts/orders/sales-alert-day-part
 * php ./yii alerts/orders/sales-alert-week-part
 */

namespace console\controllers\alerts;

use common\components\sms\Sms;
use console\controllers\export\ExportController;
use DateInterval;
use DateTime;
use modules\shopandshow\models\shop\ShopOrder;
use modules\shopandshow\models\shop\ShopOrderStatus;
use yii\db\Expression;
use yii\helpers\Console;


/**
 * Class OrdersController
 *
 * @package console\controllers
 */
class OrdersController extends ExportController
{

    const MIN_ORDER_ID = 124744;
    const CRITICAL_ALERTS_TOKEN = '7';

    const CRITICAL_ALERTS_TITLE = 'AHTUNG! ';

    /**
     * Глобальное выключение алертов плохих продаж
     * @var bool
     */
    private $isEnabledOrdersAlert = false;

    public function actionBadOrders()
    {
//        $this->bitrixErrorOrder();
        $this->siteErrorOrder();
        $this->noOrders();
    }

    /**
     * Проверка отправки смс
     */
    public function actionTestSms()
    {
        $this->sendSms(self::CRITICAL_ALERTS_TITLE . 'Тестовое сообщение.');
    }

    /**
     * Алерт когда мы отправили заказы в очередь а ответы по ним не пришли в течении полу часа
     * @return bool
     */
    private function bitrixErrorOrder()
    {
        $noSendOrders = ShopOrder::find()
            ->andWhere('shop_order.status_code = :status', [
                ':status' => ShopOrderStatus::STATUS_SEND_QUEUE
            ])
            ->andWhere('shop_order.created_at <= :max_created_at', [
                ':max_created_at' => time() - (60 * 10)
            ])
//            ->andWhere('shop_order.user_id NOT IN (1000, 36574, 68471, 1267)') // TODO test user
            ->andWhere('shop_order.id > :order_id', [':order_id' => self::MIN_ORDER_ID])
            ->andWhere(['source' => ShopOrder::SOURCE_SITE])
            ->all();

        if (!$noSendOrders) {
            $this->stdout("Все в порядке битрикс принимает заказы с сайта\n", Console::FG_GREEN);
            return true;
        }

        $this->sendSms(self::CRITICAL_ALERTS_TITLE . 'KFSS не принимает заказы с сайта более 10 мин');
        $badOrders = [];


        return false;

        /**
         * @var $noSendOrder ShopOrder
         */
        foreach ($noSendOrders as $noSendOrder) {
            $badOrders[] = [
                'order-guid' => $noSendOrder->guid->getGuid(),
                'order-id' => $noSendOrder->id,
                'order-price' => $noSendOrder->price,
            ];
        }

        try {
            $grClient = \Yii::$app->getResponseService;
            $grClient->setCampaignToken(self::CRITICAL_ALERTS_TOKEN);
            $campaign = $grClient->getCampaigns()->getCampaign($grClient->getCampaignToken());

            $newLetter = new \common\components\email\services\modules\newsLetters\GRCreateNewsLettersOptions([
                'name' => 'ЧП! Битрикс не принимает заказы с сайта более 20 мин. Срочно проверить.',
                'type' => 'broadcast', // draft - черновик broadcast - рассылка
                'editor' => 'html2',
                'subject' => 'ЧП! Битрикс не принимает заказы с сайта более 20 мин. Срочно проверить.',
                'campaign' => [
                    'campaignId' => $campaign['campaignId'],
                ], //*
                'fromField' => [
                    'fromFieldId' => $campaign['confirmation']['fromField']['fromFieldId']
                ], //*

                'replyTo' => null,

                'content' => [
                    'html' => $this->renderPartial('@mail/developers/_bad_order', [
                        'badOrders' => $badOrders
                    ])
                ], //*

                'flags' => [], //Message flags. Allowed values: openrate, clicktrack and google_analytics
//            'attachments' => 'test',

                'sendSettings' => [
                    'selectedCampaigns' => [
                        $campaign['campaignId']
                    ],
                    /*'selectedContacts' => [
                        $campaign['campaignId']
                    ],*/
                    'timeTravel' => 'false',
                    'perfectTiming' => 'false',
                ], //*
            ]);

            $createNewsLetters = $grClient->getNewsLetters()->sendNewsletter($newLetter);

            if (is_array($createNewsLetters) && array_key_exists('error', $createNewsLetters)) {
                throw new \Exception(print_r($createNewsLetters, true));
            }
            $this->stdout("Response letter id: {$createNewsLetters['newsletterId']}\n", Console::FG_GREEN);

        } catch (\Exception $e) {
            \Yii::error('Ошибка отправки в getresponse: ' . $e->getMessage());

            var_dump($e->getMessage());
            var_dump($e->getFile());
            var_dump($e->getLine());

            $this->stdout("Response sent failed\n", Console::FG_RED);
        }
    }

    /**
     * Алерт когда мы не отправляем заказы в очередь
     * @return bool
     */
    private function siteErrorOrder()
    {
        $noSendOrders = ShopOrder::find()
            ->andWhere('shop_order.status_code = :status', [
                ':status' => ShopOrderStatus::STATUS_WAIT_PAY
            ])
            ->andWhere('shop_order.created_at <= :max_created_at', [
                ':max_created_at' => time() - (60 * 10)
            ])
            ->andWhere('shop_order.user_id NOT IN (1000, 36574, 68471, 1267)') // TODO test user
            ->andWhere('shop_order.id > :order_id', [':order_id' => self::MIN_ORDER_ID])
            ->andWhere(['source' => ShopOrder::SOURCE_SITE])
            ->all();

        if (!$noSendOrders) {
            $this->stdout("Все в порядке! Сайт передает заказы\n", Console::FG_GREEN);
            return true;
        }

        $countOrders = count($noSendOrders);

        $this->sendSms(self::CRITICAL_ALERTS_TITLE . "Сайт не передает заказы($countOrders шт.) более 10 мин.", [
            '+7(905)190-88-92', //Игнатенков
            '+7(926)581-28-70', //Анисимов
        ]);
    }

    /**
     * Алерт когда на сайте нет заказов в течении полу часа днем (часа ночью)
     * @return bool
     */
    private function noOrders()
    {
        $hour = (int)date('G');
        $time = time() - (60 * 30); // пол часа днем
        $minute = '30';

        if ($hour >= 0 && $hour <= 7) {
            $time = time() - (60 * 60); // час ночью
            $minute = '60';
        }

        $orders = ShopOrder::find()
            ->andWhere('shop_order.created_at >= :max_created_at', [
                ':max_created_at' => $time
            ])
            ->andWhere('shop_order.id > :order_id', [':order_id' => self::MIN_ORDER_ID])
            ->andWhere(['source' => ShopOrder::SOURCE_SITE])
            ->all();

        if ($orders) {
            $this->stdout("ok заказы есть!\n", Console::FG_GREEN);
            return false;
        }


        $this->sendSms(self::CRITICAL_ALERTS_TITLE . " Нет заказов на сайте в течении $minute минут!");
        return true;


        $this->sendSms(self::CRITICAL_ALERTS_TITLE . " Нет заказов на сайте в течении $minute минут!", [
//            '+7(977)438-80-25', //Селянский
//            '+7(929)580-60-20', //Коваленко
            '+7(926)581-28-70', //Анисимов
            '+7(905)190-88-92', //Игнатенков

            //Тех поддержка
            '+7(968)411-78-66', //Нагатинская
            '+7(968)325-45-65', //Центральный офис
            '+7(965)409-01-35', //Борисенко

            '+7(999)999-06-49', //Луковкин
//            '+7(965)151-72-18', //Иван Гуторов
        ]);

        return true;
    }

    private function sendSms($text, $phoneList = [
        '+7(905)190-88-92', //Игнатенков
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
     * Алерт если за час от текущего времени заказов меньше чем в среднем за месяц для этого же периода
     * @return string
     */

    public function actionSalesAlertDayHour()
    {

        if (!$this->isEnabledOrdersAlert) {
            return false;
        }

        $out = '';
        $notifyIfReductionMoreThenPercent = 35;

        $ordersWasNumSrc = $this->getAverageOrdersNumFor('day-hour');
        $ordersWasNum = $ordersWasNumSrc['num'];
        //echo $ordersWasNum;

        //В зависимости от средних значений используем разные пороговые значения
        if ($ordersWasNum <= 2) {
            $notifyIfReductionMoreThenPercent = 100;
        } elseif ($ordersWasNum > 2 && $ordersWasNum <= 5) {
            $notifyIfReductionMoreThenPercent = 50;
        } elseif ($ordersWasNum > 5 && $ordersWasNum <= 10) {
            $notifyIfReductionMoreThenPercent = 40;
        } elseif ($ordersWasNum > 10) {
            $notifyIfReductionMoreThenPercent = 35;
        }

        //Для тестов
        $dateSub = '0M0D'; //Для боя должно быть смещение 0

        $timeFrom = (new DateTime())->sub(new DateInterval("P{$dateSub}T1H"));
        $timeFromDate = $timeFrom->format("Y-m-d H:i:s");
        //$timeTo = new DateTime();
        $timeTo = (new DateTime())->sub(new DateInterval("P{$dateSub}T0H"));
        $timeToDate = $timeTo->format("Y-m-d H:i:s");

        $ordersIs = ShopOrder::find()
            ->select('id')
            ->andWhere(['>=', 'created_at', new Expression("UNIX_TIMESTAMP('{$timeFromDate}')")])
            ->andWhere(['<=', 'created_at', new Expression("UNIX_TIMESTAMP('{$timeToDate}')")])
            ->andWhere(['source' => ShopOrder::SOURCE_SITE])
            ->asArray()
            ->all();

        $ordersIsNum = count($ordersIs);

        if ($ordersIsNum < $ordersWasNum) {
            $isLessThenWasPercent = 100 - round($ordersIsNum / $ordersWasNum * 100, 2);
            $isPercentOfWas = 100 - $isLessThenWasPercent;

            $periodFromTime = $ordersWasNumSrc['period_from']->format('H:i');
            $periodToTime = $ordersWasNumSrc['period_to']->format('H:i');

            //Информируем если уменьшение больше кретического
            if ($isLessThenWasPercent > $notifyIfReductionMoreThenPercent) {
                $out .= "С {$periodFromTime} до {$periodToTime} сайт продал на {$isLessThenWasPercent}% (порог = {$notifyIfReductionMoreThenPercent}) меньше от средних значений ({$ordersWasNum}->{$ordersIsNum})";
                $subject = "Продажи сайта упали ниже {$isPercentOfWas}% от нормы! ({$periodFromTime} до {$periodToTime})";
                $mailSended = $this->sendNotifyEmail($out, $subject);

                $this->sendSms(self::CRITICAL_ALERTS_TITLE . $out, [
//                    '+7(977)438-80-25', //Селянский
//                    '+7(929)580-60-20', //Коваленко
                    '+7(926)581-28-70', //Анисимов
//                    '+7(926)020-12-11', //Юра Рябов

                    //Тех поддержка
//                    '+7(968)411-78-66', //Нагатинская
//                    '+7(968)325-45-65', //Центральный офис
//                    '+7(965)409-01-35', //Борисенко

//                    '+7(999)999-06-49', //Луковкин
//                    '+7(901)745-74-75', //Комарова
//                    '+7(965)151-72-18', //Иван Гуторов
                ]);

                $out .= "\nТема: {$subject}.\nСообщение " . ($mailSended ? 'отправлено успешно' : 'НЕ отправлено');
            }
        } else {
            $out .= "С продажами все ок ({$ordersWasNum}->{$ordersIsNum})";
        }

        echo $out;
        return $out;
    }

    /**
     * Алерт если с начала текущего дня до текущего времени заказов меньше чем в среднем за месяц для этого же периода
     * @return string
     */
    public function actionSalesAlertDayPart()
    {

        if (!$this->isEnabledOrdersAlert) {
            return false;
        }

        $out = '';
        $notifyIfReductionMoreThenPercent = 35;

        $ordersWasNumSrc = $this->getAverageOrdersNumFor('day-part');
        $ordersWasNum = $ordersWasNumSrc['num'];
        //echo $ordersWasNum;

        $timeFrom = DateTime::createFromFormat("Y-m-d H:i:s", (new DateTime())->format("Y-m-d 00:00:00"));
        $timeFromDate = $timeFrom->format("Y-m-d H:i:s");
        $timeTo = new DateTime();
        $timeToDate = $timeTo->format("Y-m-d H:i:s");

        $ordersIs = ShopOrder::find()
            ->select('id')
            ->andWhere(['>=', 'created_at', new Expression("UNIX_TIMESTAMP('{$timeFromDate}')")])
            ->andWhere(['<=', 'created_at', new Expression("UNIX_TIMESTAMP('{$timeToDate}')")])
            ->andWhere(['source' => ShopOrder::SOURCE_SITE])
            ->asArray()
            ->all();

        $ordersIsNum = count($ordersIs);

        $periodFromTime = $ordersWasNumSrc['period_from']->format('H:i');
        $periodToTime = $ordersWasNumSrc['period_to']->format('H:i');

        //Информируем
        if ($ordersWasNum > $ordersIsNum) {
            $isLessThenWasPercent = 100 - round($ordersIsNum / $ordersWasNum * 100, 2);
            $isPercentOfWas = 100 - $isLessThenWasPercent;

            $subject = "Продажи сайта упали ниже {$isPercentOfWas}% от нормы! ({$periodFromTime} до {$periodToTime})";
            $out .= "С {$periodFromTime} до {$periodToTime} сайт продал на {$isLessThenWasPercent}% МЕНЬШЕ от средних значений ({$ordersWasNum}->{$ordersIsNum})";

        } elseif ($ordersWasNum < $ordersIsNum) {
            $isMoreThenWasPercent = round($ordersIsNum / $ordersWasNum * 100, 2) - 100;
            $isPercentOfWas = 100 + $isMoreThenWasPercent;

            $subject = "Продажи сайта возросли до {$isPercentOfWas}% от нормы! ({$periodFromTime} до {$periodToTime})";
            $out .= "С {$periodFromTime} до {$periodToTime} сайт продал на {$isMoreThenWasPercent}% БОЛЬШЕ от средних значений ({$ordersWasNum}->{$ordersIsNum})";
        } else {
            $subject = "Продажи сайта не изменились по сравнению с нормой! ({$periodFromTime} до {$periodToTime})";
            $out .= "С {$periodFromTime} до {$periodToTime} сайт продал одинаковое количество с нормой ({$ordersWasNum}->{$ordersIsNum}).";
        }

        $mailSended = $this->sendNotifyEmail($out, $subject);

        $this->sendSms(self::CRITICAL_ALERTS_TITLE . $out, [
//                    '+7(977)438-80-25', //Селянский
//                    '+7(929)580-60-20', //Коваленко
            '+7(926)581-28-70', //Анисимов
//            '+7(926)020-12-11', //Юра Рябов

            //Тех поддержка
//                    '+7(968)411-78-66', //Нагатинская
//                    '+7(968)325-45-65', //Центральный офис
//                    '+7(965)409-01-35', //Борисенко

//                    '+7(999)999-06-49', //Луковкин
//                    '+7(901)745-74-75', //Комарова
//                    '+7(965)151-72-18', //Иван Гуторов
        ]);

        $out .= "\nТема: {$subject}.\nСообщение " . ($mailSended ? 'отправлено успешно' : 'НЕ отправлено');

        echo $out;

        return $out;
    }

    /**
     * Алерт если с начала текущей недели до текущего времени заказов меньше чем в среднем за месяц (4 недели) для этого же периода
     * @return string
     */
    public function actionSalesAlertWeekPart()
    {

        if (!$this->isEnabledOrdersAlert) {
            return false;
        }

        $out = '';
        $notifyIfReductionMoreThenPercent = 35;

        $ordersWasNumSrc = $this->getAverageOrdersNumFor('week-part');
        $ordersWasNum = $ordersWasNumSrc['num'];
        //echo $ordersWasNum;

        $datetimeFrom = (new DateTime())->modify('last Monday');
        $datetimeFromDate = $datetimeFrom->format("Y-m-d 00:00:00");
        $datetimeTo = new DateTime();
        $datetimeToDate = $datetimeTo->format("Y-m-d H:i:s");

        $ordersIs = ShopOrder::find()
            ->select('id')
            ->andWhere(['>=', 'created_at', new Expression("UNIX_TIMESTAMP('{$datetimeFromDate}')")])
            ->andWhere(['<=', 'created_at', new Expression("UNIX_TIMESTAMP('{$datetimeToDate}')")])
            ->andWhere(['source' => ShopOrder::SOURCE_SITE])
            ->asArray()
            ->all();

        $ordersIsNum = count($ordersIs);

        $periodFromTime = $ordersWasNumSrc['period_from']->format('H:i');
        $periodToTime = $ordersWasNumSrc['period_to']->format('H:i');

        //Информируем
        if ($ordersWasNum > $ordersIsNum) {
            $isLessThenWasPercent = 100 - round($ordersIsNum / $ordersWasNum * 100, 2);
            $isPercentOfWas = 100 - $isLessThenWasPercent;

            $subject = "[С НАЧАЛА НЕДЕЛИ] Продажи сайта упали ниже {$isPercentOfWas}% от нормы! ({$periodFromTime} до {$periodToTime})";
            $out .= "С {$periodFromTime} до {$periodToTime} сайт продал на {$isLessThenWasPercent}% МЕНЬШЕ от средних значений ({$ordersWasNum}->{$ordersIsNum})";

        } elseif ($ordersWasNum < $ordersIsNum) {
            $isMoreThenWasPercent = round($ordersIsNum / $ordersWasNum * 100, 2) - 100;
            $isPercentOfWas = 100 + $isMoreThenWasPercent;

            $subject = "[С НАЧАЛА НЕДЕЛИ] Продажи сайта возросли до {$isPercentOfWas}% от нормы! ({$periodFromTime} до {$periodToTime})";
            $out .= "С {$periodFromTime} до {$periodToTime} сайт продал на {$isMoreThenWasPercent}% БОЛЬШЕ от средних значений ({$ordersWasNum}->{$ordersIsNum})";
        } else {
            $subject = "[С НАЧАЛА НЕДЕЛИ] Продажи сайта не изменились по сравнению с нормой! ({$periodFromTime} до {$periodToTime})";
            $out .= "С {$periodFromTime} до {$periodToTime} сайт продал одинаковое количество с нормой ({$ordersWasNum}->{$ordersIsNum}).";
        }

        $mailSended = $this->sendNotifyEmail($out, $subject);

        $this->sendSms(self::CRITICAL_ALERTS_TITLE . $out, [
//                    '+7(977)438-80-25', //Селянский
//                    '+7(929)580-60-20', //Коваленко
            '+7(926)581-28-70', //Анисимов
//            '+7(926)020-12-11', //Юра Рябов

            //Тех поддержка
//                    '+7(968)411-78-66', //Нагатинская
//                    '+7(968)325-45-65', //Центральный офис
//                    '+7(965)409-01-35', //Борисенко

//                    '+7(999)999-06-49', //Луковкин
//                    '+7(901)745-74-75', //Комарова
//                    '+7(965)151-72-18', //Иван Гуторов
        ]);

        $out .= "\nТема: {$subject}.\nСообщение " . ($mailSended ? 'отправлено успешно' : 'НЕ отправлено');

        echo $out;

        return $out;
    }

    public function getAverageOrdersNumFor($conditionType)
    {

        if (!$this->isEnabledOrdersAlert) {
            return false;
        }

        $result = array('num' => 0, 'period_from' => '', 'period_to' => '');

        $ordersSrc = ShopOrder::find()
            ->select('id')
            ->andWhere(['source' => ShopOrder::SOURCE_SITE])
            ->asArray();

        //1) day-hour | 2) day-part | 3) week-part
        //1) за час до текущего времени, среднее за 30 дней до этого
        //2) с начала дня до текущего времени, среднее аз 30 дней до этого
        //3) с начала рабочей недели до текущего значения, среднее за предыдущие 4 недели

        switch ($conditionType) {
            case 'day-hour':

                $averagePeriodItemsNum = 30 * 3;

                $timeFrom = (new DateTime())->sub(new DateInterval("PT1H"));
                $timeFromDate = $timeFrom->format("Y-m-d H:i:s");
                $timeTo = new DateTime();
                $timeToDate = $timeTo->format("Y-m-d H:i:s");

                $result['period_from'] = $timeFrom;
                $result['period_to'] = $timeTo;

                $averagePeriodFrom = (new DateTime())->sub(new DateInterval("P{$averagePeriodItemsNum}D"));
                $averagePeriodFromDate = $averagePeriodFrom->format("Y-m-d 00:00:00");
                $averagePeriodTo = new DateTime();
                $averagePeriodToDate = $averagePeriodTo->format("Y-m-d 00:00:00");

                $ordersSrc->andWhere(
                    [
                        'and',
                        [
                            'and',
                            ['>=', 'TIME(FROM_UNIXTIME(created_at))', new Expression("TIME( '{$timeFromDate}' )")],
                            ['<=', 'TIME(FROM_UNIXTIME(created_at))', new Expression("TIME( '{$timeToDate}' )")]
                        ],
                        [
                            'and',
                            ['>=', 'created_at', new Expression("UNIX_TIMESTAMP( '{$averagePeriodFromDate}' )")],
                            ['<=', 'created_at', new Expression("UNIX_TIMESTAMP( '{$averagePeriodToDate}' )")]
                        ]
                    ]
                );

                break;
            case 'day-part':

                $averagePeriodItemsNum = 30 * 3;

                $timeFrom = DateTime::createFromFormat("Y-m-d H:i:s", (new DateTime())->format("Y-m-d 00:00:00"));
                $timeFromDate = $timeFrom->format("Y-m-d 00:00:00");
                $timeTo = new DateTime();
                $timeToDate = $timeTo->format("Y-m-d H:i:s");

                $result['period_from'] = $timeFrom;
                $result['period_to'] = $timeTo;

                $averagePeriodFrom = (new DateTime())->sub(new DateInterval("P{$averagePeriodItemsNum}D"));
                $averagePeriodFromDate = $averagePeriodFrom->format("Y-m-d 00:00:00");
                $averagePeriodTo = new DateTime();
                $averagePeriodToDate = $averagePeriodTo->format("Y-m-d 00:00:00");

                $ordersSrc->andWhere(
                    [
                        'and',
                        [
                            'and',
                            ['>=', 'TIME(FROM_UNIXTIME(created_at))', new Expression("TIME( '{$timeFromDate}' )")],
                            ['<=', 'TIME(FROM_UNIXTIME(created_at))', new Expression("TIME('{$timeToDate}' )")]
                        ],
                        [
                            'and',
                            ['>=', 'created_at', new Expression("UNIX_TIMESTAMP( '{$averagePeriodFromDate}' )")],
                            ['<=', 'created_at', new Expression("UNIX_TIMESTAMP('{$averagePeriodToDate}')")]
                        ]
                    ]
                );

                break;
            case 'week-part':

                $averagePeriodItemsNum = 4 * 3;

                for ($w = 1; $w <= $averagePeriodItemsNum; $w++) {

                    $averagePeriodFrom = ((new DateTime())->modify('last Monday'))->sub(new DateInterval("P{$w}W"));
                    $averagePeriodFromDate = $averagePeriodFrom->format("Y-m-d 00:00:00");
                    $averagePeriodTo = (new DateTime())->sub(new DateInterval("P{$w}W"));
                    $averagePeriodToDate = $averagePeriodTo->format("Y-m-d H:i:s");

                    $ordersSrc->orWhere(
                        [
                            'and',
                            ['>=', "created_at", new Expression("UNIX_TIMESTAMP( '{$averagePeriodFromDate}' )")],
                            ['<=', "created_at", new Expression("UNIX_TIMESTAMP( '{$averagePeriodToDate}' )")]
                        ]
                    );

                    if ($w == 1) {
                        $result['period_from'] = $averagePeriodFrom;
                    }
                    if ($w == $averagePeriodItemsNum) {
                        $result['period_to'] = $averagePeriodTo;
                    }
                }

                break;
            default:
                return $result;
                break;
        }

        if (1) {
            //echo ($ordersSrc->prepare(\Yii::$app->db->queryBuilder)->createCommand()->rawSql);
            //die();

            $orders = $ordersSrc->all();
            $ordersNum = count($orders);

            $averageOrdersNum = $ordersNum ? round($ordersNum / $averagePeriodItemsNum, 2) : 0;
            $result['num'] = $averageOrdersNum;
        }

        return $result;
    }

}
