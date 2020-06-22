<?php

namespace common\widgets\services;

use yii\base\Widget;

class OntheIoWidget extends Widget
{
    public static $_data = [];

    /**
     * Это событие необходимо передавать каждый раз, когда человек просматривает товар, как по наведению мышкой на странице каталога и поиска, так и при переходе на страницу товара.
     * @param \common\models\cmsContent\CmsContentElement $model
     * @param \modules\shopandshow\models\shop\ShopProduct $shopProduct
     * @param string $viewType
     * @param \common\components\Breadcrumbs $breadcrumbs
     *
     * @return string
     */
    public static function viewItem($model, $shopProduct, $viewType = 'full', \common\components\Breadcrumbs $breadcrumbs = null)
    {
        // строку "/Главная страница/Каталог/Мода/Обувь/Туфли/Босоножки женские «Давиния»" преобразуем в массив из Мода/Обувь/Туфли
        // если метод будет вызываться аяксом (например quick_view) - переделать на динамическое получение из Tree
        if ($breadcrumbs) {
            $categories = array_slice(explode('/', $breadcrumbs->getStringViewPath('/')), 2);
        } else {
            $categories = array_slice(explode('/', \Yii::$app->breadcrumbs->getStringViewPath('/')), 3, -1);
        }

        $data = [
            'event' => 'pageviews_product',
            'product_id' => $model->id,
            'product_url' => $model->url,
            'product_name' => $model->name,
            'categories' => $categories,
            'price' => $shopProduct->basePrice() * 100, // хотят цену в копейках
            'product_available' => $model->new_quantity > 0 ? 'yes' : 'no',
            'product_article' => $model->relatedPropertiesModel->getAttribute('LOT_NUM'),
            //'product_size' => '',
            //'product_color' => '',
            'view_type' => $viewType
        ];

        if ($user = \common\helpers\User::getUser()) {
            //$data['user_id'] = $userId;
            if ($user->email) {
                $data['user_visit'] = $user->email;
            }
        }

        self::$_data[] = $data;

        return '';
    }

    /**
     * Это событие необходимо отправлять, когда человек добавляет товар в корзину. Это событие используется в отчетах воронок.
     * @param \common\models\cmsContent\CmsContentElement $model
     * @param \modules\shopandshow\models\shop\ShopProduct $shopProduct
     * @param int $count
     *
     * @return void
     */
    public static function addToBasket($model, $shopProduct, $count = 1)
    {
        $data = [
            'event' => 'add_to_basket',
            'product_id' => $model->id,
            'product_url' => $model->url,
            'price' => $shopProduct->basePrice() * 100, // хотят цену в копейках
            'count' => $count
        ];

        if ($user = \common\helpers\User::getUser()) {
            //$data['user_id'] = $userId;
            if ($user->email) {
                $data['user_visit'] = $user->email;
            }
        }

        self::$_data[] = $data;
    }

    /**
     * Это событие необходимо отправлять, когда человек переходит на корзину. Это событие считается попыткой оформления заказа.
     * @param array $cartData
     */
    public static function viewCart(array $cartData)
    {
        $data = [
            'event' => 'try_order_send',
            'cart' => $cartData
        ];

        if ($user = \common\helpers\User::getUser()) {
            //$data['user_id'] = $userId;
            if ($user->email) {
                $data['user_visit'] = $user->email;
            }
        }

        self::$_data[] = $data;
    }

    /**
     * Это событие необходимо отправлять, когда заказ был совершен. Обычно, это страница благодарности за покупку.
     * @param int $orderId
     * @param array $basketProducts
     */
    public static function checkout($orderId, array $basketProducts)
    {
        $data = [
            'event' => 'basket_send',
            'order_id' => $orderId,
            'cart' => $basketProducts
        ];

        if ($user = \common\helpers\User::getUser()) {
            //$data['user_id'] = $userId;
            if ($user->email) {
                $data['user_visit'] = $user->email;
            }
        }

        self::$_data[] = $data;
    }

    /**
     * Это событие необходимо отправлять, когда пользователь отправляет свои данные из формы регистрации или оформления заказа.
     */
    public static function userData()
    {
        $data = [
            'event' => 'user_data',
        ];

        if ($user = \common\helpers\User::getUser()) {
            //$data['user_id'] = $user->id;
            $data['user_email'] = $user->email;
            $data['user_phone'] = $user->phone;
            $data['user_registered'] = true;
        } else {
            $data['user_registered'] = false;
        }

        self::$_data[] = $data;
    }

    public static function getJsEvent()
    {
        $content = '';
        foreach (self::$_data as $data) {
            $content .= 'sx.OntheIo._io_event(' . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . ');' . PHP_EOL;
        }
        return $content;
    }

    public function run()
    {
        \frontend\assets\v2\common\adversting\OntheIoAsset::register($this->view);

        $this->view->registerJs(self::getJsEvent());
    }
}