<?php

namespace common\widgets\content;

use yii\base\Widget;

class StarsRating extends Widget
{

    public $value = 0;

    public $maxValue = 5;

    public $viewFile = '@template_common/widgets/starsRating/_stars_rating';


    public function init()
    {
        parent::init();
    }

    public function run()
    {
        echo \Yii::$app->view->render($this->viewFile, [
            'value' => $this->value,
        ], $this);
    }

    /**
     *
     * @return float|int
     */
    public function getPercent()
    {
        if (!$this->value) {
            return 0;
        }

        $percent = ($this->value / $this->maxValue) * 100;

        return round($percent);
    }


    public function getValue()
    {
        return $this->value;
    }
}