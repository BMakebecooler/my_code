<?php
namespace common\widgets\services;

use yii\base\Widget;

class CriteoWidget extends Widget
{
    public $account = 43803;
    public $email = '79054025255fb1a26e4bc422aef54eb4';

    /** @var int */
    public $id = null;
    /**
     * @var string
     */
    public $item = null;
    /**
     * @var string
     */
    public $event = null;

    public function run()
    {
        \frontend\assets\v2\common\adversting\CriteoAsset::register($this->view);

        return $this->view->registerJs('
window.criteo_q = window.criteo_q || [];
var deviceType = /iPad/.test(navigator.userAgent) ? "t" : /Mobile|iP(hone|od)|Android|BlackBerry|IEMobile|Silk/.test(navigator.userAgent) ? "m" : "d";
window.criteo_q.push(
 { event: "setAccount", account: '.$this->account.'}, // You should never update this line
 { event: "setEmail", email: "'.$this->email.'" }, // Can be an empty string 
 { event: "setSiteType", type: deviceType},
 { event: "'.$this->event.'"'.($this->item ? ', item: '.$this->item : '').($this->id ? ', id: '.$this->id : '').'}
);
'
        );
    }
}