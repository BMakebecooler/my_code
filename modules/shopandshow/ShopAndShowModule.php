<?php

namespace modules\shopandshow;

use Yii;
use yii\base\BootstrapInterface;
use yii\base\Event;
use yii\base\Module;
use yii\web\View;

/**
 * Class ShopAndShowModule
 * @package modules\shopandshow
 */
class ShopAndShowModule extends Module implements BootstrapInterface
{
    public $controllerNamespace = 'modules\shopandshow\controllers';

    public function init()
    {
        parent::init();

        if (defined('SS_SITE')) {
            \Yii::configure(\Yii::$app, require(__DIR__ . sprintf('/config/%s/main.php', SS_SITE)));
        } else {
            \Yii::configure(\Yii::$app, require(__DIR__ . '/config/main.php'));
        }

        $this->removeMetaTagsSkeeksCms();
    }

    public function bootstrap($app)
    {
//        $app->getUrlManager()->addRules([
//            'cartindex' => 'shopandshow/cart/index', //Вообще потом разобраться почему в конфиге нельзя это сделать, там этому самое место
//        ], false);

        // перенаправляем с уже реализованного в skeeks модуля скидок на наш дополненный модуль
        $app->getUrlManager()->addRules(
            [
                '~sx/shop/<c_:(admin-discount|admin-discount-coupon|admin-discsave|admin-order|admin-cms-content-element)>' => 'shopandshow/shop/<c_>',
                '~sx/shop/<c_:(admin-discount|admin-discount-coupon|admin-discsave|admin-order|admin-cms-content-element)>/<a_:.*>' => 'shopandshow/shop/<c_>/<a_>',
                '~shop-cart-old' => 'shopandshow/cart/cart',
                '~shop-cart' => 'cart/index',
                '~sx/cms/<c_:(admin-cms-content-property)>/<a_:.*>' => 'shopandshow/cms/<c_>/<a_>',
                [
                    'pattern' => 'robots.txt',
                    'route' => '/setting/robots',
                    'suffix' => ''
                ],
            ]
            , false);
    }

    /**
     * Для удаления метатегов цмс (чтобы не палиться)
     */
    private function removeMetaTagsSkeeksCms()
    {
        \Yii::$app->view->on(View::EVENT_BEGIN_PAGE, function (Event $e) {
            if (!\Yii::$app->request->isAjax && !\Yii::$app->request->isPjax) {

                \Yii::$app->response->getHeaders()->remove('X-Powered-CMS');


                /**
                 * @var $view View
                 */
                $view = $e->sender;

                $view->metaTags['Z2VuZXJhdG9y'] = '';
                $view->metaTags['cmsmagazine'] = '';
            }
        });
    }
}