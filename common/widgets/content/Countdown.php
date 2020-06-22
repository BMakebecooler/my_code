<?php

namespace common\widgets\content;

use yii\base\Widget;

class Countdown extends Widget
{
    public $viewFile = '@template/widgets/Content/Countdown/countdown';
    public $timerClass = '';
    public $timerFinal = '';
    public $timerFormat = '%H ч | %M м | %S с';

    public function init()
    {
        parent::init();
    }

    /**
     * @return string
     */
    public function run()
    {
        \frontend\assets\v2\common\widgets\countdown\Countdown::register($this->getView()); //Этого тут не должно быть!
        return $this->render($this->viewFile);
    }
}