<?php
/**
 * Created by PhpStorm.
 * User: ubuntu5
 * Date: 22.05.17
 * Time: 17:03
 */

namespace common\helpers;

use common\components\sms\Sms;
use yii\helpers\VarDumper as VD;

class Developers extends VD
{

    const DEBUG_PROD_COOKIE_NAME = 'test_cookie_name';


    protected static $memories = [];

    /**
     * Начать отсчет используемой памяти в блоке
     * @param string $nameBlock
     * @return int
     */
    public static function getMemoryUsageStart($nameBlock = '')
    {
        return self::$memories[$nameBlock] = memory_get_usage();
    }

    /**
     * Закончить отсчет использауемой памяти в блоке
     * @param string $nameBlock
     * @return float|int МБ
     */
    public static function getMemoryUsageEnd($nameBlock = '')
    {
        return $nameBlock . ' ' . self::byMb(memory_get_usage() - self::$memories[$nameBlock]);
    }

    /**
     * Перевести из байтов в Мб
     * @param $bites
     * @return float|int
     */
    public static function byMb($bites)
    {
        return $bites / (1024 * 1024);
    }

    /**
     * Сообщить о проблеме на почту программерам
     * @param null $message
     * @param null $body
     * @return bool
     */
    public static function reportProblem($message = null, $body = null)
    {
        try {

            \Yii::$app->mailer->htmlLayout = false;
            \Yii::$app->mailer->textLayout = false;

            $emails = [
                'ryabov_yn@shopandshow.ru',
                'vorobyev_aa@shopandshow.ru',
            ];

            $message = \Yii::$app->mailer->compose('@templates/mail/developers/_problems', [
                'message' => $message,
                'body' => $body,
            ])
                ->setFrom([\Yii::$app->cms->adminEmail => \Yii::$app->cms->appName])
                ->setTo($emails)
                ->setSubject('Возникла проблема');

            // todo wtf????
            //return $message->send();

        } catch (\Exception $exception) {

        }
    }

    /**
     * Сообщить о проблеме по смс программерам
     * @param null $message
     */
    public static function reportProblemSms($message)
    {
        $phoneList = [
            '+7(929)580-60-20', //Коваленко
        ];

        foreach ($phoneList as $phone) {
            \Yii::$app->sms->sendSms($phone, $message, true, Sms::SMS_TYPE_FOR_ADMINS);
        }
    }

    /**
     * Признак дебаг режима
     * @return bool
     */
    public static function isDebug()
    {
        return isset($_COOKIE[self::DEBUG_PROD_COOKIE_NAME]);
    }

}