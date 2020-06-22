<?php

namespace common\widgets\promo;

use common\helpers\Url;
use skeeks\cms\base\WidgetRenderable;
use common\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

/**
 * Class Popup
 */
class Popup extends WidgetRenderable
{

    public $viewFile = '@template/widgets/Promo/_sale_stock';
    public $cookieName = 'ss_popup_sale';
    public $delayTimer = 30000;
    public $containerClass = '';
    public $fancyBoxParams = [];

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
        return isset($_COOKIE[$this->cookieName]);
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

        if (Url::isLockPromoCurrent()) {
            return false;
        }

        $cookieName = $this->cookieName;
        $delayTimer = $this->delayTimer;
        $containerClass = $this->containerClass;
        $cookieValue = 1;
        $wrapperPadding = isset($this->fancyBoxParams['padding']) ? $this->fancyBoxParams['padding'] : 15;

        if ($this->isFancyBox && $isShowPopup) {
            $this->view->registerJs(<<<JS
                (function(sx, $, _) {
                    setTimeout(function() {
                        $.fancybox({
                            'content': $('#sale_stock_popup_sale'),
                            'closeBtn': true,
                            'wrapCSS': '{$containerClass}',
                            'fixed': true,
                            'locked': false,
                            'autoCenter': false,
                            'padding': '{$wrapperPadding}',
                            'afterShow': function() {
                                var expires, date = new Date();
                                date.setTime(date.getTime() + (60 * 24 * 60 * 60 * 1000));
                                expires = "; expires=" + date.toGMTString();
                                document.cookie = "{$cookieName}=" + {$cookieValue} + expires + "; path=/";
                                
                                sx.Observer.trigger('popupAppear');
                            },
                            'onClosed': function() {
                                sx.Observer.trigger('popupClose');
                            }
                        });
                    }, $delayTimer)
                })(sx, sx.$, sx._);
JS
            );
        } else {
            $this->view->registerJs(<<<JS
                (function(sx, $, _) {
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