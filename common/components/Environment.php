<?php

/**
 * Created by PhpStorm.
 * User: ubuntu5
 * Date: 15.05.17
 * Time: 13:20
 */

namespace common\components;


class Environment
{

    /**
     * Доступные среды исполнения
     */
    const PRODUCTION = 'prod';
    const DEVELOPMENT = 'development';
    const TEST = 'test';

    /**
     * Название ключа в окружении сервера
     */
    const APP_ENV = 'APPLICATION_ENV';
    const SERVER_ENV_KEY = 'SS_ENV';

    /**
     * Признак "боевой" среды
     * @return bool
     */
    public static function isProduction()
    {
        return isset($_SERVER[self::SERVER_ENV_KEY]) && $_SERVER[self::SERVER_ENV_KEY] === self::PRODUCTION || self::isGetEnvProd();
    }

    /**
     * Признак "боевой" среды
     * @return bool
     */
    public static function isDev()
    {
        return (isset($_SERVER[self::SERVER_ENV_KEY]) && $_SERVER[self::SERVER_ENV_KEY] === self::DEVELOPMENT);
    }

    /**
     * Признак тестовой среды
     * @return bool
     */
    public static function isTest()
    {
        return (isset($_SERVER[self::SERVER_ENV_KEY]) && $_SERVER[self::SERVER_ENV_KEY] === self::TEST) && !self::isGetEnvProd();
    }

    /**
     * Проверить среду выполнения по методу "getenv" для консольного режима
     * @return bool
     */
    public static function isGetEnvProd()
    {
        return getenv(self::APP_ENV) == self::PRODUCTION;
    }

    /**
     * Вернуть название среды выполнения
     * @return string
     */
    public static function getEnv()
    {
        return ($env = getenv(self::APP_ENV)) ? $env : 'development';
    }

}