<?php

namespace modules\shopandshow\components\mail\template;

use modules\shopandshow\components\mail\BaseTemplate;

class Simple extends BaseTemplate
{
    public $viewFile = '@modules/shopandshow/views/mail/template/simple';

    public function init()
    {
        parent::init();

        $this->utm = 'utm_source=email&utm_medium=email_'.date('Ymd').'&utm_campaign='.date('Ymd');

        $this->data['IMAGE'] = [
            'URL' => 'https://shopandshow.ru/v2/common/docs/book_recipes.pdf',
            'IMG' => '/v2/common/img/newsletter/recipes/recipes-700x450.jpg',
        ];
    }
}