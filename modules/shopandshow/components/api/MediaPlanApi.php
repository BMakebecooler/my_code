<?php
/**
 * Интеграция с апи медиапалана
 * http://10.1.2.10:8080/docs/
 * User: ubuntu5
 * Date: 14.03.17
 * Time: 17:33
 */

namespace modules\shopandshow\components\api;

use modules\shopandshow\components\api\base\ApiBase;

/**
 * Class MediaPlanApi
 * @property string $version    read-only
 * @property string $baseUrl    read-only
 *
 * @see http://10.1.2.10:8080/docs/#!/for_Site/
 * @package modules\shopandshow\components\api
 */
class MediaPlanApi extends ApiBase
{

    /**
     * версия api
     */
    const VERSION = 'v1';

    /**
     * @var string
     */
    public $schema = 'http://';

    /**
     * @var string
     */
    public $host = 'mp2.shopandshow.ru/api/';

    public function init()
    {
        parent::init();

        $this->timeout = 5;
    }

    public function getBaseUrl()
    {
        return $this->schema . $this->host . "/" . $this->version;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return static::VERSION;
    }

    /**
     * Список лотов которые шли или будут идти в указанный день
     * @param $date
     * @param bool $extended
     * @return base\ApiResponse
     */
    public function airDayLots($date, $extended = true)
    {
        return $this->send('/airDayLots', [
            'date' => $date,
            'extended' => json_encode($extended)
        ]);
    }

    /**
     * Возвращает блоки прямых эфиров и повторов за указанный день
     * @param $date
     * @param bool $extended
     * @return base\ApiResponse
     */
    public function airBlocks($date, $extended = true)
    {
        return $this->send('/airBlocks', [
            'date' => $date,
            'extended' => json_encode($extended)
        ]);
    }

    /**
     * Список лотов указанного блока эфира
     * @param int $blockId
     * @param bool $extended
     * @return base\ApiResponse
     */
    public function airBlocksLots($blockId, $extended = true)
    {
        return $this->send('/airBlockLots', [
            'blockId' => $blockId,
            'extended' => json_encode($extended)
        ]);
    }

    /**
     * Список лотов которые шли или будут идти в указанный день
     * @param $date
     * @param bool $extended
     * @return base\ApiResponse
     */
    public function airDayLotsTime($date, $extended = true)
    {
        return $this->send('/airDayLotsTime', [
            'date' => $date,
            'extended' => json_encode($extended)
        ]);
    }

    /**
     * Общая информация об эфире дня
     * @param $date
     * @param string $type
     * @param bool $extended
     * @param bool $parsed
     * @return base\ApiResponse
     */
    public function airDayInfo($date, $type = 'live', $extended = true, $parsed = true)
    {
        $method = sprintf('/day/%s/blocks', $date);
        return $this->send($method, [
            'type' => json_encode($type),
            'extended' => json_encode($extended),
            'parsed' => json_encode($parsed)
        ]);
    }
}