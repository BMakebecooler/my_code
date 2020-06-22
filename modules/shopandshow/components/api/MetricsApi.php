<?php
/**
 * Интеграция с InfluxDB
 * https://mon.shopandshow.ru/
 */

namespace modules\shopandshow\components\api;

use modules\shopandshow\components\api\base\ApiBase;

class MetricsApi
{

    /** @var string protocol */
    public $protocol = 'http';

    /**
     * @var string InfluxDB http api url
     */
    public $host = 'data.shopandshow.ru';

    /**
     * @var int InfluxDB http api port
     */
    public $port = 8086;

    /**
     * @var string db name
     */
    public $dbName = '999';


    public function push($metricName, $tags = [], $fields = [])
    {

        $client = \InfluxDB\Client::fromDSN(sprintf('influxdb://ecom:qwe123@%s:%s/%s', $this->host, $this->port, $this->dbName));

        return $client->writePoints([
            new \InfluxDB\Point(
                $metricName,
                1,
                $tags,
                $fields,
                time()
            )
        ], \InfluxDB\Database::PRECISION_SECONDS);

    }

    public function pushArray($metrics)
    {

        $client = \InfluxDB\Client::fromDSN(sprintf('influxdb://ecom:qwe123@%s:%s/%s', $this->host, $this->port, $this->dbName));

        $_points = [];

        foreach ($metrics as $point) {

            if (isset($point['metricName'])) {
                $_points[] = new \InfluxDB\Point(
                    $point['metricName'],
                    1,
                    $point['tags'],
                    $point['fields'],
                    time()
                );
            }
        }

        return $client->writePoints($_points, \InfluxDB\Database::PRECISION_SECONDS);

    }

    public function pushHistoryArray($metrics)
    {

        $client = \InfluxDB\Client::fromDSN(sprintf('influxdb://ecom:qwe123@%s:%s/%s', $this->host, $this->port, $this->dbName));

        $_points = [];

        foreach ($metrics as $point) {

            $_points[] = new \InfluxDB\Point(
                $point['metricName'],
                1,
                $point['tags'],
                $point['fields'],
                $point['timestamp']
            );

        }

        return $client->writePoints($_points, \InfluxDB\Database::PRECISION_SECONDS);

    }

}