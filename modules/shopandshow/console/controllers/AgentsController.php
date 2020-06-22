<?php

namespace modules\shopandshow\console\controllers;

use yii\console\Controller;

/**
 * Агенты
 *
 * Class AgentsController
 * @package modules\shopandshow\console\controllers
 */
class AgentsController extends Controller
{
    public function init()
    {
        parent::init();

        set_time_limit(0);
    }

}
