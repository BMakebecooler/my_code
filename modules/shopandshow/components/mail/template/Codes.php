<?php

namespace modules\shopandshow\components\mail\template;

use modules\shopandshow\components\mail\BaseTemplate;

class Codes extends BaseTemplate
{
    public $viewFile = '@modules/shopandshow/views/mail/template/codes';

    public function init()
    {
        parent::init();

        $this->utm = 'utm_source=email&utm_medium=email_'.date('Ymd').'&utm_campaign='.date('Ymd');

        $this->data['IMAGE'] = [
            'URL' => '/promo/872072-28_11_17_nakhodki-dnya/',
            'IMG' => '/v2/common/img/sands_cts/nahodka_dnya_20171128.jpg',
        ];
    }
}