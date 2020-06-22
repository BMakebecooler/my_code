<?php

namespace common\helpers;


use common\models\Setting;

class Common
{
    const BOOL_Y = 'Y';
    const BOOL_N = 'N';

    const BOOL_Y_INT = 1;
    const BOOL_N_INT = 0;

    const CATEGORY_SOURCE = 'category';
    const PROMO_SOURCE = 'promo';

    private static $timers;

    public static function getSitePhone()
    {
        $result = self::getSitePhoneCode() . ' ' . self::getSitePhoneNumber();
        return $result;
    }

    public static function getSitePhoneInt()
    {
        $result = self::getSitePhoneCode() . ' ' . self::getSitePhoneNumber();
        $result = preg_replace('/[^0-9]/', '', $result);
        return $result;
    }

    public static function getSitePhoneCode()
    {
        return Setting::getPhoneCode();
//        return \Yii::$app->params[self::getSitePhoneKey()]['code'];
    }

    public static function getSitePhoneNumber()
    {
        return Setting::getPhoneNumber();

//        return \Yii::$app->params[self::getSitePhoneKey()]['number'];
    }

    public static function getSitePhoneKey()
    {
        $phoneKey = 'phone';

        if (date('Y-m-d H:i') >= date('2019-04-01 23:55') && date('Y-m-d H:i:s') <= date('2019-04-02 06:35:00')) {
            $phoneKey = 'phone_2';
        }
        return $phoneKey;
    }

    /**
     * @param $phoneNumBegin - (int) - номер телефона с кодом региона/оператора, без кода страны (4951234567)
     * @param int $phonesNum
     * @return array
     */
    public static function generatePhones($phoneNumBegin, $phonesNum = 1)
    {
        $phones = [];

        $phonesNumEnd = (int)$phoneNumBegin + (int)$phonesNum;

        for ($i = (int)$phoneNumBegin; $i < $phonesNumEnd; $i++) {
            $phone = sprintf('%010d', $i);
            $phoneFormated = sprintf("+7 %03d %03d-%02d-%02d",
                substr($phone, 0, 3),
                substr($phone, -7, 3),
                substr($phone, -4, 2),
                substr($phone, -2)
            );
            $phones[] = $phoneFormated;
        }

        return $phones;
    }

    public static function startTimer($name)
    {
        if (empty(self::$timers[$name])){
            $time = microtime(true);

            self::$timers[$name] = $time;
            return $time;
        }
        return false;
    }

    public static function getTimerTime($name, $showTimerName = true){
        if (!empty(self::$timers[$name])){
            $startTime = self::$timers[$name];
            unset(self::$timers[$name]);
            $time = round((microtime(true) - $startTime), 5);
            return $showTimerName ? "Timer[{$name}] time = {$time} sec" : $time;
        }

        return false;
    }

    public static function getObjectClassShortName($object)
    {
        return \yii\helpers\StringHelper::basename(get_class($object));
    }
}