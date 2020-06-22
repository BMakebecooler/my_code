<?php

namespace modules\shopandshow\components\content;

use yii\base\Component;
use modules\shopandshow\components\content\traits\ByBitrix;

/**
 * User: koval
 * Date: 23.04.18
 * Time: 13:44
 */
class ContentElement extends Component
{

    use ByBitrix;

    public $productId;
    public $bitrixId;

}