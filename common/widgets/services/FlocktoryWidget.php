<?php

namespace common\widgets\services;

use common\helpers\User;
use common\models\cmsContent\CmsContentElement;
use frontend\assets\v2\common\adversting\FlocktoryAsset;
use modules\shopandshow\models\shop\forms\QuickOrder;
use modules\shopandshow\models\shop\ShopOrder;
use modules\shopandshow\models\shop\ShopProduct;
use skeeks\cms\models\CmsTree;
use skeeks\cms\models\CmsUser;
use yii\base\Widget;

class FlocktoryWidget extends Widget
{

    public static $_data = [];

    public function run()
    {
        FlocktoryAsset::register($this->view);

        // отслеживаем на всех страницах
        self::trackUser();

        foreach (self::$_data as $data) {
            $this->view->registerJs("sx.Flocktory.push('{$data['event']}', " . json_encode($data['data'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . ");");
        }
    }

    /**
     * Форматирование данных пользователя для flocktory
     * @param CmsUser $user
     * @return string
     */
    public static function getEmailFromUser(CmsUser $user)
    {
        $userMail = $user->email;
        if (!User::isRealEmail($userMail)) {
            $userMail = '';
        }

        return $userMail ?: ($user->phone ? \common\helpers\Strings::onlyInt($user->phone) : $user->id) . '@unknown.email';
    }

    /**
     * Это событие необходимо отправлять, когда заказ был совершен. Обычно, это страница благодарности за покупку.
     * @param ShopOrder $order
     * @param array $basketProducts
     */
    public static function checkout($order, array $basketProducts)
    {
        $user = $order->user ? [
            'email' => self::getEmailFromUser($order->user)
        ] : [];
        if (array_key_exists('email', $user) && $order->user->name !== QuickOrder::DEFAULT_USER_NAME) {
            // не передаем имя пользователя, если у него имя от "быстрого заказа"
            $user['name'] = $order->user->name;
        }

        $data = [
            'event' => 'postcheckout',
            'data' => [
                'user' => $user,
                'order' => [
                    'id' => $order->id,
                    'price' => $order->price,
                    'items' => $basketProducts
                ]
            ]
        ];

        self::$_data[] = $data;
    }

    /**
     * Трэкинг пользователя
     * @return void
     */
    public static function trackUser()
    {
        $user = User::getUser();
        if (!$user) {
            return;
        }

        $userName = $user->name;
        $userEmail = self::getEmailFromUser($user);

        echo <<<HTML
        <div class="i-flocktory" data-fl-user-name="{$userName}" data-fl-user-email="{$userEmail}"></div>
HTML;
    }

    /**
     * Трэкинг каталога
     * @param CmsTree $tree
     * @return string
     */
    public static function trackCatalog(CmsTree $tree)
    {
        echo <<<HTML
        <div class="i-flocktory" data-fl-action="track-category-view" data-fl-category-id="{$tree->id}"></div>
HTML;
    }

    /**
     * @param CmsContentElement $model
     * @param ShopProduct $shopProduct
     */
    public static function trackItem($model, $shopProduct)
    {
        $available = $model->new_quantity > 0 ? 'true' : 'false';
        $brand = '';

        $brandId = $model->relatedPropertiesModel->getAttribute('BRAND');
        if ($brandId) {
            $brandModel = \common\lists\Contents::getContentElementById($brandId);
            $brand = $brandModel->name;
        }

        echo <<<HTML
        <div class="i-flocktory" data-fl-action="track-item-view" data-fl-item-id="{$model->id}" data-fl-item-category-id="{$model->tree_id}" data-fl-item-available="{$available}" data-fl-item-vendor="{$brand}"></div>
HTML;
    }

    /**
     * Интеграция модуля Exchange
     */
    public static function exchangeModule()
    {
        $userName = '';
        $userEmail = 'xname@flocktory.com';

        if ($user = User::getUser()) {
            $userEmail = self::getEmailFromUser($user);
            $userName = User::isRealName($user->name) ? $user->name : $userName;
        }

        $dataFlUserName = $userName ? "data-fl-user-name=\"{$userName}\"" : '';

        echo <<<HTML
        <div class="i-flocktory" data-fl-action="exchange" data-fl-spot="" {$dataFlUserName} data-fl-user-email="{$userEmail}"></div>
HTML;
    }

}