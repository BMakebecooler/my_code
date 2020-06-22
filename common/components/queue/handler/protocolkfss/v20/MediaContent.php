<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 2019-05-08
 * Time: 15:42
 */

namespace common\components\queue\handler\protocolkfss\v20;


use common\components\queue\AbstractHandler;
use common\components\queue\HandlerInterface;

class MediaContent  extends AbstractHandler implements HandlerInterface
{

    public function execute()
    {
        return true;
        // TODO: Implement execute() method.
    }
}