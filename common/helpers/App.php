<?php

namespace common\helpers;

class App
{

    /**
     * Признак веб приложения
     * @return bool
     */
    public static function isWebApplication()
    {
        return \Yii::$app instanceof \yii\web\Application;
    }


    /**
     * Признак консоль приложения
     * @return bool
     */
    public static function isConsoleApplication()
    {
        return \Yii::$app instanceof \yii\console\Application;
    }

    /**
     * Вернуть реальный IP
     * @return string
     */
    public static function getRealIp()
    {
        $ip = null;

        if (self::isWebApplication()) {
            if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
                $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
                $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
            }
            $client = $_SERVER['HTTP_CLIENT_IP'] ?? null;
            $forward = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? null;
            $remote = $_SERVER['REMOTE_ADDR'];

            if (filter_var($client, FILTER_VALIDATE_IP)) {
                $ip = $client;
            } elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
                $ip = $forward;
            } else {
                $ip = $remote;
            }
        }

        return $ip;
    }

}