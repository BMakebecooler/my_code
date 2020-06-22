<?php

namespace common\widgets\promo;

use skeeks\cms\base\WidgetRenderable;
use common\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

/**
 * Class Subscribe
 * @package skeeks\cms\shop\cmsWidgets\filters
 */
class Subscribe extends WidgetRenderable
{

    const COOKIE_NAME = 'ss_subscribe_500_20180810';
    const COOKIE_NAME_RECIPES = 'ss_subscribe_recipes';
    const COOKIE_NAME_KITCHENDAY = 'ss_subscribe_kitchenday';
    const COOKIE_NAME_FASHIONDAY = 'ss_subscribe_fashionday';


    public $viewFile = '@template/widgets/Promo/subscribe/_500roubles';

    static public function descriptorConfig()
    {
        return array_merge(parent::descriptorConfig(), [
            'name' => 'Настройка виджета подписки',
        ]);
    }

    public function init()
    {
        parent::init();
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(),
            [
                'content_id' => \Yii::t('skeeks/shop/app', 'Content'),
            ]);
    }

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(),
            [
                [['content_id'], 'integer'],
            ]);
    }

    public function run()
    {
        $cookieName = self::COOKIE_NAME;
        $cookieValue = 1;

        if (isset($_COOKIE[self::COOKIE_NAME])){
            return false;
        }

        if (false && !isset($_COOKIE[self::COOKIE_NAME])) {
            $this->view->registerJs(<<<JS
                (function(sx, $, _) {
                    setTimeout(function() {
                        $.fancybox({
                            'content': $('#popup_subscribe'),
                            'closeBtn': true,
                            'fixed': false,
                            'locked': false,
                            'autoCenter': false,
                            afterClose: function() {

                            }
                        });
                    }, 10000);
                    
                    var expires, date = new Date();
                        date.setTime(date.getTime() + (60 * 24 * 60 * 60 * 1000));
                        expires = "; expires=" + date.toGMTString();
                    
                    document.cookie = "{$cookieName}=" + {$cookieValue} + expires + "; path=/";
                                
                })(sx, sx.$, sx._);
JS
            );
        }

        return $this->render($this->viewFile);
    }

    public function renderConfigForm(ActiveForm $form)
    {
    }
}