<?php

/**
 * Created by PhpStorm.
 * User: ubuntu5
 * Date: 28.09.17
 * Time: 19:06
 */

namespace console\controllers\queues\jobs;

use yii\helpers\Json;

abstract class Job
{
    protected $data;

    protected $shortName;

    public $onlySave = false;

    public function __construct()
    {
        $this->shortName = $this->shortClassName();
    }

    /**
     *
     * @param $queueMessage
     *
     * @return bool
     * @throws \Exception
     */
    protected function prepareData($queueMessage)
    {
        try {

            $data = Json::decode($queueMessage);

            if ($data === null || empty($data)) {
                Job::dump('empty json');

                return false;
            }

            if (!isset($data['Data'])) {
                Job::dump('No data');

                return false;
            }

            return $this->data = $data;

        } catch (\Exception $exception) {

            Job::dump('invalid json');

            return false;
        }

    }

    /**
     * Get classname without namespace
     *
     * @return string
     */
    public function shortClassName()
    {
        $classname = get_called_class();

        if (preg_match('@\\\\([\w]+)$@', $classname, $matches)) {
            $classname = $matches[1];
        }

        return $classname;
    }

    public static function dump($var)
    {
        //echo date('Y-m-d H:i:s : ');
        if (is_string($var)) {
            echo $var.PHP_EOL;
        } else {
            var_dump($var);
        }
    }

    public abstract function execute($queue, &$guid);

}