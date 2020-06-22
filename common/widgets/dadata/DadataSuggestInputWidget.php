<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 22.09.2016
 */
namespace common\widgets\dadata;

use skeeks\cms\dadataSuggest\widgets\suggest\DadataSuggestInputWidget as SxDadataSuggestInputWidget;

class DadataSuggestInputWidget extends SxDadataSuggestInputWidget
{
    public $backend = '/shopandshow/dadata/backend/save-address/';

    public function init()
    {
        parent::init();

        if ($this->backend) {
            $this->clientOptions['backend'] = $this->backend;
        }
    }
}

