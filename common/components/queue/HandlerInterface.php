<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 2019-03-26
 * Time: 17:36
 */

namespace common\components\queue;


interface HandlerInterface
{

    public function execute();

}