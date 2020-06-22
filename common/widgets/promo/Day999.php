<?php

namespace common\widgets\promo;

use common\helpers\Url;
use skeeks\cms\base\WidgetRenderable;
use common\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use frontend\assets\v2\common\adversting\Day999Asset;

/**
 * Class Subscribe
 * @package skeeks\cms\shop\cmsWidgets\filters
 */
class Day999 extends WidgetRenderable
{

    const COOKIE_NAME = 'ss_popup_999_4';

    public $viewFile = '@template/widgets/Promo/_day999';
    public $isFancyBox = true;

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

    /**
     * @return bool
     */
    public function isShowingPopup()
    {
        return isset($_COOKIE[self::COOKIE_NAME]);
    }

    public function run()
    {
        $isShowPopup = true;

        if ($this->isShowingPopup()) {
            $isShowPopup = false;
        }

        if (!Url::isMainPageCurrent()) {
//            return false;
        }

        Day999Asset::register($this->getView());

        $cookieName = self::COOKIE_NAME;
        $cookieValue = 1;

        if ($this->isFancyBox && $isShowPopup) {
            $this->view->registerJs(<<<JS
                (function(sx, $, _) {
                    new sx.classes.PopupPromo();               
                    setTimeout(function() {
                        $.fancybox({
                            'content': $('#popup_day999'),
                            'closeBtn': true,
                            'fixed': true,
                            'locked': false,
                            'autoCenter': false,
                            afterShow: function() {
                                
                                sx.Observer.trigger('widget999Open');

                                var expires, date = new Date();
                                date.setTime(date.getTime() + (60 * 24 * 60 * 60 * 1000));
                                expires = "; expires=" + date.toGMTString();
                                document.cookie = "{$cookieName}=" + {$cookieValue} + expires + "; path=/";
                            }
                        });
                    }, 10000)
                })(sx, sx.$, sx._);
JS
            );
        } else {
            $this->view->registerJs(<<<JS
                (function(sx, $, _) {
                    new sx.classes.PopupPromo();               
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