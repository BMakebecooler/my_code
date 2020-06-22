<?php

namespace common\widgets\promo;

use common\helpers\Url;
use skeeks\cms\base\WidgetRenderable;
use common\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

/**
 * Class CyberMonday
 * @package skeeks\cms\shop\cmsWidgets\filters
 */
class CyberMonday extends WidgetRenderable
{

    const COOKIE_NAME = 'ss_8march';

    public $viewFile = '@template/widgets/Promo/_promo-subscribe';
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

    public function isShowingPromo()
    {
        return isset($_COOKIE[self::COOKIE_NAME]);
    }

    public function run()
    {
        $isShowPopup = false;

        if ($this->isShowingPromo()) {
            $isShowPopup = false;
        }

        if (!Url::isMainPageCurrent()) {
//            return false;
        }

        $cookieName = self::COOKIE_NAME;
        $cookieValue = 1;

        if ($this->isFancyBox && $isShowPopup) {
            $this->view->registerJs(<<<JS
                (function(sx, $, _) {
                    setTimeout(function() {
                        $.fancybox({
                            'content': $('#promo-subscribe'),
                            'closeBtn': true,
                            'fixed': true,
                            'locked': false,
                            'autoCenter': false,
                            'afterShow': function() {
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