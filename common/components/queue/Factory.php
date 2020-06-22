<?php

namespace common\components\queue;

use yii\base\Exception;
use yii\helpers\Inflector;

/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 2019-03-26
 * Time: 17:03
 */
class Factory
{

    /**
     * @param $string
     * @throws Exception
     */
    public static function factory($json)
    {


        $object = json_decode($json, true);


        $handler = self::createClass($object['Info']);
        if ($handler) {
            $handler->data = $object['Data'];
            return $handler;
        }

        throw new Exception(' Unknown format given');

    }


    /**
     * @param $info
     * @return object
     * @throws \yii\base\InvalidConfigException
     */
    protected static function createClass($info)
    {
        $type = $info['Type'];
        $version = $info['Version'];
        $source = $info['Source'];

        $class = trim(\yii\helpers\Inflector::camel2words($type));
        $class = str_replace(' ', '', $class);

        $version = str_replace('.', '', $version);
        $class = '\\common\\components\\queue\\handler\\protocol' . Inflector::slug($source) . '\\v' . $version . '\\' . $class;
        return \Yii::createObject($class);


    }

}