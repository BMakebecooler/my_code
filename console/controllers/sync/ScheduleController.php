<?php

/**
 * php ./yii sync/schedule/run
 * php ./yii sync/schedule/six
 */

namespace console\controllers\sync;

use common\helpers\Msg;
use yii\helpers\Console;

/**
 * Class ScheduleController
 * @package console\controllers
 */
class ScheduleController extends \yii\console\Controller
{

    const CAMPAIGN_ID_SHOPANDSHOW = '9';
    const CAMPAIGN_ID_FINAL_TEST = 'M';

    public function actionRun()
    {
        if (date('H:i') == '07:10') {
            $this->seven();
        }

        if (date('H:i') == '08:01') {
            $this->eight();
        }

        if (date('d.m.Y H:i') == '14.07.2018 19:01') {
            $this->cacheFlush();
        }

        //Тестирование рассылки по сегментам
        //$this->checkMailDispatch(); //ЗАВЕРШЕНО

        //Очищаем кеш и ассеты
//        $this->cacheFlush();
    }

    /**
     * Расписание на 7 часов
     */
    protected function seven()
    {
        //Запускаем импорт медиа плана
        \Yii::$app->runAction('imports/media-plan/all');
        $this->stdout("Медиа план загружен!\n", Console::FG_GREEN);

        \Yii::error('Кеш по расписанию на 7:10 утра почищен');
    }

    /**
     * Расписание на 8 часов
     */
    protected function eight()
    {
        $this->cacheFlush();
        \Yii::error('Кеш по расписанию на 8 утра почищен');
    }

    /**
     * Очистка кеша
     */
    protected function cacheFlush()
    {
        //Очищаем кеш и ассеты
        \Yii::$app->frontendCache->flush();
        $this->stdout("Кеш почищен!\n", Console::FG_GREEN);
    }

    /**
     * Проверка расписания на необходимость запуска рассылки по сегментам [тестирование].
     */
    protected function checkMailDispatch()
    {
        $campaignToken = self::CAMPAIGN_ID_SHOPANDSHOW; //prod
        $mailSegments = [
            1 => 'time_test01',
            2 => 'time_test02',
            3 => 'time_test03',
        ];

        $mailSegmentsTimes = [
            1 => 0,
            2 => 0,
            3 => 0,
        ];

        $date = date('Y-m-d');

        switch ($date) {
            case '2018-07-03': //ПредТест
                $campaignToken = self::CAMPAIGN_ID_FINAL_TEST; //test
                $mailSegmentsTimes = [
                    1 => '15:05',
                    2 => '15:30',
                    3 => '16:00',
                ];

                break;

            case '2018-07-05': //День 1
            case '2018-07-06': //День 2
            case '2018-07-07': //День 3
                $mailSegmentsTimes = [
                    1 => '08:00',
                    2 => '09:00',
                    3 => '10:00',
                ];
                break;

            case '2018-07-08': //День 4
                //case '2018-07-09': //День 5 !!! ДЕНЬ ПРОПУЩЕН ИЗ-ЗА СТРАННОЙ БОЕВОЦ РАССЫЛКИ НА ВСЮ БАЗУ
            case '2018-07-10': //День 5
            case '2018-07-11': //День 6
                $mailSegmentsTimes = [
                    1 => '11:00',
                    2 => '12:00',
                    3 => '13:00',
                ];
                break;

            case '2018-07-12': //День 7
            case '2018-07-13': //День 8
            case '2018-07-14': //День 9
            case '2018-07-15': //День 10
            case '2018-07-16': //День ПРОПУСК для снятия результатов первых трех блоков и выявления победителей для последних трех дней теста
                $mailSegmentsTimes = [
                    1 => '09:00',
                    2 => '09:00',
                    3 => '09:00',
                ];
                break;

            case '2018-07-17': //День 11
                $mailSegmentsTimes = [
                    1 => '09:00', //Победитель дней 1-3
                    2 => '13:00', //Победитель дней 4-6
                    3 => '16:00', //Победитель дней 7-9
                ];
                break;

            case '2018-07-18': //День 12
                $mailSegmentsTimes = [
                    1 => '09:00', //Победитель дней 1-3
                    2 => '13:00', //Победитель дней 4-6
                    3 => '16:00', //Победитель дней 7-9
                ];
                break;

            case '2018-07-19': //День 13
                $mailSegmentsTimes = [
                    1 => '09:00', //Победитель дней 1-3
                    2 => '13:00', //Победитель дней 4-6
                    3 => '16:00', //Победитель дней 7-9
                ];
                break;

            case '2018-08-07': //День альтернативных победителей 1 --- рассылка в 15:00 не ушла, день не учитываем и добавляем 4 день теста
            case '2018-08-08': //День альтернативных победителей 2
            case '2018-08-09': //День альтернативных победителей 3
            case '2018-08-10': //День альтернативных победителей 4
                $mailSegmentsTimes = [
                    1 => '08:00', //Альтернативные победители дней 1-3
                    2 => '11:00', //Альтернативные победители дней 4-6
                    3 => '15:00', //Альтернативные победители дней 7-9
                ];
                break;
            default:
                //Ничего не отправяем
        }

        //Проверим первый элемент времени отправки сегментов, этого нам достаточно
        //что бы понять нужны ли дальнейшие проверки на отправку
        if (!empty($mailSegmentsTimes[1])) {
            //Рассылки в этот день есть, так что проверяем конкретное время
            $time = date('H:i');

            foreach ($mailSegmentsTimes as $mailSegmentNum => $mailSegmentTime) {
                if ($time == $mailSegmentTime) {

                    switch ($campaignToken) {
                        case self::CAMPAIGN_ID_SHOPANDSHOW:
                            $action = 'generate-cts-by-segments';
                            break;
                        case self::CAMPAIGN_ID_FINAL_TEST:
                        default:
                            $action = 'generate-cts-test-by-segments';
                            break;
                    }

                    echo "Dispatch to '{$campaignToken}' campaign to segments: '{$mailSegments[$mailSegmentNum]}' \n";

                    //Время рассылки - отправляем
                    \Yii::$app->runAction("mail/{$action}", [
                        'campaignToken' => $campaignToken,
                        'segments' => $mailSegments[$mailSegmentNum]
                    ]);
                }
            }
        } else {
            //echo "No today dispatches\n";
        }
    }
}