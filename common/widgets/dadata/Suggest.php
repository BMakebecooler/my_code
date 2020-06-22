<?php

/**
 * Виджет для выбора города
 */

namespace common\widgets\dadata;

use yii\base\Widget;

class Suggest extends Widget
{
    public $viewFile = '@template/widgets/Dadata/suggest';

    public $params = [];

    public function run()
    {
        return $this->render($this->viewFile);
    }
}