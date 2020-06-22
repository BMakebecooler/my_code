<?php

namespace modules\shopandshow\controllers;

use common\helpers\ArrayHelper;
use common\helpers\Developers;
use common\helpers\Msg;
use common\helpers\User;
use common\lists\Contents;
use common\models\cmsContent\CmsContentElement;
use common\models\Product;
use common\widgets\products\ModificationsWidget;
use common\widgets\checkout\CheckoutWidget;
use Exception;
use modules\shopandshow\models\shares\SsShare;
use modules\shopandshow\models\shares\SsShareSeller;
use modules\shopandshow\models\shop\delivery\DeliveryServices;
use modules\shopandshow\models\shop\ShopBasket;
use modules\shopandshow\models\shop\ShopDiscount;
use modules\shopandshow\models\shop\ShopOrder;
use modules\shopandshow\models\shop\ShopOrderStatus;
use modules\shopandshow\models\shop\ShopProduct;
use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\shop\controllers\CartController as SXCartController;
use modules\shopandshow\models\shop\ShopDiscountCoupon;
use skeeks\cms\shop\models\ShopDelivery;
use uranum\delivery\DeliveryCalculator;
use uranum\delivery\DeliveryCargoData;
use Yii;
use yii\filters\VerbFilter;

/**
 * Class CartController
 * @package modules\shopandshow\controllers
 */
class CartController extends SXCartController
{

    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;

        return parent::beforeAction($action);
    }

    /**
     * @return string
     */
    public function actionCart()
    {
        //Хотфикс на определенного клиента
        if (!\common\helpers\User::isGuest()){
            if ($userPhone = \common\helpers\Strings::getPhoneClean(\Yii::$app->user->identity->phone)){
                if ($userPhone == '79272206736'){
                    $this->goHome();
                }
            }
        }

        $this->view->title = \Yii::t('skeeks/shop/app', 'Basket') . ' | ' . \Yii::t('skeeks/shop/app', 'Shop');

        $orderKfss = null;

        $shopFuser = \Yii::$app->shop->shopFuser;

        $kfssOrderId = $shopFuser->external_order_id;

        $isRequestAjax = \Yii::$app->request->isAjax;
        $isRequestPjax = \Yii::$app->request->isPjax;

        if (\Yii::$app->abtest->isA()){
            \Yii::$app->session->set('new_cart', 1);
        }elseif (\Yii::$app->session->get('new_cart')){
            \Yii::$app->session->remove('new_cart');
        }

        //[!!! ЭТО ФЛАГ ДЛЯ ТЕСТИРОВАНИЯ. Основной переключатель доступности вынесен в параетр АПИ + настройка из БД] Временно для тестирования оплаты
        if (isset($_GET['op']) && $_GET['op'] == 1){
            \Yii::$app->session->set('op', 1);
        }elseif (isset($_GET['op']) && $_GET['op'] == 1 && \Yii::$app->session->get('op')){
            \Yii::$app->session->remove('op');
        }

        //Проверка в связи с тем что без нее при изменении кол-ва товаров перезагружается страница целиком

        if (!\Yii::$app->kfssApiV2->forcedUseOnly) {
            if (!($isRequestAjax || $isRequestPjax)) {
                try {
                    //Если корзина не пуста и у нас нет номера заказа в КФСС - пробуем отправить заказ и получить его номер и тп
                    if (!$shopFuser->isEmpty() && !$kfssOrderId) {

                        $orderKfss = \Yii::$app->kfssApiV2->createOrder();

                        if ($orderKfss && !empty($orderKfss['orderId'])) {
                            $kfssOrderId = $orderKfss['orderId'];
                            $shopFuser->external_order_id = $kfssOrderId;
                            $shopFuser->save();

                            //Приводим корзину в соответствие с заказом из АПИ
                            \Yii::$app->kfssApiV2->recalculateOrder($orderKfss);
                        } else {
                            //Не удалось создать заказ
                            \Yii::error('Cant create kfss order');
                        }
                    } elseif ($kfssOrderId) {
                        //* Проверка купона *//

                        if ($couponCode = \Yii::$app->request->get('coupon')) {
                            $addCouponResponse = \Yii::$app->kfssApiV2->addCoupon($couponCode);
                            $orderKfss = \Yii::$app->kfssApiV2->updateOrder();
                        } else {
                            $orderKfss = \Yii::$app->request->get('update') ? \Yii::$app->kfssApiV2->updateOrder() : \Yii::$app->kfssApiV2->getOrderFullUpdated('CartIndex');
                        }

                        //* /Проверка купона *//

                    }
                } catch (Exception $e) {
                    Yii::error('Fail cart/cart, message ' . $e->getMessage());
                    if (User::isDeveloper()) {
                        echo '<pre>';
                        var_dump($e->getMessage());
                        echo '</pre>';
                    }
                }
            }

            //Запрашиваем данные по заказу если они еще не получены
            if ($kfssOrderId && !$orderKfss) {
                $orderKfss = \Yii::$app->kfssApiV2->getOrderFullUpdated('CartIndex2');
//                $orderKfss = \Yii::$app->kfssApiV2->getOrderFull('CartIndex2');
                \Yii::$app->kfssApiV2->recalculateOrder($orderKfss);
            }
        }else{
            //Запрашиваем данные по заказу если они еще не получены
            if ($kfssOrderId && !$orderKfss) {
                //$orderKfss = \Yii::$app->kfssApiV2->getOrderFullUpdated('CartIndex2');
                $orderKfss = \Yii::$app->kfssApiV2->getOrderFull('CartIndex2');
                \Yii::$app->kfssApiV2->recalculateOrder($orderKfss);
            }
        }


        //* Если KFSS API отключено - пересчитываем корзину на каждом хите в ней *//
        if (\Yii::$app->kfssApiV2->isDisable || \Yii::$app->kfssApiV2->forcedUseOnly) {
            \Yii::$app->shop->shopFuser->recalculate();
        }
        //* /Если KFSS API отключено - пересчитываем корзину на каждом хите в ней *//

//        $orderSum = \Yii::$app->shop->shopFuser->money->getValue();
        $orderSum = \Yii::$app->shop->shopFuser->money;

        //* Выясняем какой у нас кейс - какую из кнопок показывать *//

        $isNeedRecalculateRemote = false;

        if ($kfssOrderId){
            //Есть связь с кфсс, то надо понять, менялась ли корзина
            //Сравним стоимость в заказе кфсс и на сайте
            $orderSumWithServiceCharge = $orderSum + \common\models\ShopOrder::SERVICE_CHARGE_PRICE;
            if ($orderKfss['sum'] != $orderSumWithServiceCharge){
                $isNeedRecalculateRemote = true;
            }

            //Еще кейс для перехода к перерасчету - текущий тип оплаты картой и выбранный соответствующий тип оплаты
            if (\Yii::$app->shop->shopFuser->pay_system_id == \Yii::$app->kfssApiV2::PAY_SYSTEM_ID_CARD){

                //...при том что оплачивать нельзя
                if (!$orderKfss['lockstock']){
                    $isNeedRecalculateRemote = true;
                }

                //...при том что Почтой России оплачивать нельзя
                if (\Yii::$app->shop->shopFuser->delivery_id == 6){
                    $isNeedRecalculateRemote = true;
                }

            }
            //Истек таймаут на бронь
            if (!isset($_COOKIE[\Yii::$app->kfssApiV2::LOCKSTOCK_TIME_COOCKIE_NAME])){
                $isNeedRecalculateRemote = true;
            }
        }else{
            //Для полной новой корзины если нет кфсс заказа пересчет обязателен
            if (\Yii::$app->kfssApiV2->isOnlinePaymentAllowed()){
                $isNeedRecalculateRemote = true;
            }
        }

        //* /Выясняем какой у нас кейс - какую из кнопок показывать *//

        return $this->render('@template/modules/shop/cart/cart', [
            'orderKfss' => $orderKfss,
            'kfssOrderId' => $kfssOrderId,
            'isNeedRecalculateRemote' => $isNeedRecalculateRemote,
        ]);
    }


    public function actionIndex()
    {
        return $this->render($this->action->id);
    }


    /**
     * @return array
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [

            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'has-removed' => ['post'],
                    'add-offer-product' => ['post'],
                    'add-discount-coupon' => ['post'],
                ],
            ],
        ]);
    }

    /**
     * Отключить или включить товар в корзине
     * @return array
     */
    public function actionHasRemoved()
    {
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost()) {
            $basketId = (int)\Yii::$app->request->post('basket_id');

            /**
             * @var ShopBasket $shopBasket
             */
            $shopBasket = ShopBasket::findOne($basketId);
            if (!$shopBasket) {
                throw new \RuntimeException('Корзина не найдена: #' . var_export($basketId, true));
            }

            $result = $shopBasket->hasRemovedUpdate();

            $offer = CmsContentElement::findOne($shopBasket->product_id);

            if ($result) {
                $rr->message = 'ok';
                $rr->success = true;

                $rr->data = Contents::getInfoProduct($shopBasket->main_product_id);
                $rr->data['card_id'] = $offer->parent_content_element_id;
                $rr->data['offer_id'] = $shopBasket->product_id;
                $rr->data['count'] = $shopBasket->quantity;
            } else {
                $rr->success = false;
                $rr->errors = '';
            }
        }

        return $rr;
    }


    /**
     * Adding a product to the cart.
     *
     * @return array|\yii\web\Response
     */
    public function actionAddProduct()
    {
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost()) {
            $productId = \Yii::$app->request->post('product_id');
            $quantity = \Yii::$app->request->post('quantity');
            $additional = \Yii::$app->request->post('additional');

            //Лоты в карточку более не добавляем, таким образом находим для лота его базовую модификацию

            /** @var CmsContentElement $product */
            $product = CmsContentElement::find()->where([
                'content_id' => [PRODUCT_CONTENT_ID, OFFERS_CONTENT_ID],
                'id' => $productId
            ])->one();

            /** @var CmsContentElement $offer */
            //Если пришел лот - добавляем его базовую модификацию, если пришла сразу модификация - добавляем ее
            $offer = $product->isLot() ? $product->getBaseModification() : $product;

            if ($offer && ShopBasket::addProduct($offer->id, $quantity)) {
                $rr->success = true;
                $rr->message = \Yii::t('skeeks/shop/app', 'Item added to cart');

                if ($additional && isset($additional[SsShare::BID_PARAM])) {
                    SsShareSeller::add($additional[SsShare::BID_PARAM], $productId);
                }

                $rr->data = Contents::getInfoProduct($productId);
                $rr->data['offer_id'] = $offer->id;
                $rr->data['card_id'] = $offer->parent_content_element_id;
                $rr->data['cart'] = [];

                //* Добавляем состав корзины *//

                //Не удаленные товары в корзине
                $baskets = \Yii::$app->shop->shopFuser->getShopBaskets()->orderBy('id ASC')->all();

                if ($baskets){
                    /** @var ShopBasket $basket */
                    foreach ($baskets as $basket) {
                        $rr->data['cart'][] = [
                            'id' => $basket->product_id,
                            'quantity' => (int)$basket->quantity
                        ];
                    }
                }

                //* /Добавляем состав корзины *//

            } else {
                $rr->success = false;
                $rr->message = 'Извините, товар закончился.';
            }

            return $rr;
        } else {
            return $this->goBack();
        }
    }

    /**
     * Добавить товар предложение
     * @return array|\yii\web\Response
     */
    public function actionAddOfferProduct()
    {
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost()) {

            $productId = (int)\Yii::$app->request->post('product_id');
            $quantity = (int)\Yii::$app->request->post('quantity', 1);
            $offer = (array)\Yii::$app->request->post('offer');
            $offerParams = $offer ? array_filter($offer) : []; //@todo Разобраться почему приходят лишние в посте
            $additional = \Yii::$app->request->post('additional');

            if (!$offerParams) {
                $rr->success = false;
                $rr->message = 'Извините, товар закончился.';

                Developers::reportProblem(
                    'Попытка добавить товар-предложение в корзину без входных данных (код проблемы OFFER-1)',
                    sprintf('<a href="https://shopandshow.ru/catalog/moda/platya/%s-2689972-002-689-972/" target="_blank">Перейти к товару</a>', $productId)
                );

                return $rr;
            }

            $modificationWidget = new ModificationsWidget([
                'namespace' => ModificationsWidget::getNameSpace(),
                'model' => CmsContentElement::findOne($productId) //?
            ]);

            $parameters = $modificationWidget->getParameters();
            $parameters = ArrayHelper::index($parameters, 'id');

            $contentElement = CmsContentElement::find()->alias('offer');
            $contentElement->innerJoin('cms_content_element AS card', "offer.parent_content_element_id = card.id");

            foreach ($offerParams as $property => $value) {
                $joinTable = ModificationsWidget::$joinMap[$parameters[$property]->code] ?? 'offer';
                $alias = 'q_' . $property;
                $contentElement->innerJoin('cms_content_element_property AS ' . $alias, "$joinTable.id = $alias.element_id AND $alias.property_id = $property AND $alias.value = $value");
            }

            $contentElement->innerJoin('shop_product AS sp', 'offer.id = sp.id AND sp.quantity > 0');

            $contentElement->where(['card.parent_content_element_id' => $productId]);
            $contentElement->groupBy('offer.id');

            $product = $contentElement->one();

            $rr->success = false;
            $rr->message = 'Извините, товар закончился.';

            if ($product) {
                if (ShopBasket::addProduct($product->id, $quantity)) {
                    $rr->success = true;
                    $rr->data = Contents::getInfoProduct($productId);
                    $rr->data['offer_id'] = $product->id;
                    $rr->data['card_id'] = $product->parent_content_element_id;
                    $rr->message = \Yii::t('skeeks/shop/app', 'Item added to cart');

                    if ($additional && isset($additional[SsShare::BID_PARAM])) {
                        SsShareSeller::add($additional[SsShare::BID_PARAM], $productId);
                    }

                } else {
                    $rr->success = false;
                    $rr->message = \Yii::t('skeeks/shop/app', 'Failed to add item to cart');
                }
            }

            return $rr;
        } else {
            return $this->goBack();
        }
    }

    /**
     * @return array|\yii\web\Response
     */
    public function actionRemoveDiscountCoupon()
    {
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost())
        {
            $fuser = \Yii::$app->shop->shopFuser;

            $couponId         = \Yii::$app->request->post('coupon_id');

            try
            {
                if (!$couponId)
                {
                    throw new Exception(\Yii::t('skeeks/shop/app', 'Not set coupon code'));
                }


                $newValue = [];
                $discount_coupons = $fuser->discount_coupons;
                if ($discount_coupons)
                {
                    foreach ($discount_coupons as $id)
                    {
                        if ($id != $couponId)
                        {
                            $newValue[] = $id;
                        }
                    }
                }
                $fuser->discount_coupons = $newValue;

                //Если есть номер заказа - обновляем заказ через АПИ
                if ($fuser->external_order_id) {

                    $isKfssApiDisabled = \Yii::$app->kfssApiV2->isDisable;
                    //Отключаем отключение
                    if ($isKfssApiDisabled){
                        \Yii::$app->kfssApiV2->isDisable = false;
                    }

                    //Пересоздаем заказ - текущий отменится и создастся новый без купона

                    //* Восстановление цен без учета купона *//

                    $shopBaskets = $fuser->getShopBaskets()->orderBy('id ASC')->all();

                    if ($shopBaskets){
                        /** @var ShopBasket $shopBasket */
                        foreach ($shopBaskets as $shopBasket) {
                            $product = Product::findOne($shopBasket->product_id);

                            if ($product && $product->new_price > 2){
                                $shopBasket->price = $product->new_price;
                                $shopBasket->save();
                            }
                        }
                    }

                    //* /Восстановление цен без учета купона *//

                    $kfssOrderId = \Yii::$app->kfssApiV2->recreateOrder();

                    //Если в целом АПИ отключено то возвращаем в исходное состояние
                    if ($isKfssApiDisabled){
                        \Yii::$app->kfssApiV2->isDisable = true;
                    }

                    $fuser->save();
//                    \Yii::$app->shop->shopFuser->recalculate()->save();

                } else {
                    $fuser->save();
                    $fuser->recalculate()->save();
                }

                $rr->data = $fuser->jsonSerialize();
                $rr->success = true;
                $rr->message = \Yii::t('skeeks/shop/app', 'Your coupon was successfully deleted');

            } catch (\Exception $e)
            {
                $rr->message = $e->getMessage();
                return (array) $rr;
            }

            return (array) $rr;
        } else
        {
            return $this->goBack();
        }
    }

    /**
     * Adding a product to the cart.
     * @return array|\yii\web\Response
     */
    public function actionAddDiscountCoupon()
    {
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost()) {
            $couponCode = \Yii::$app->request->post('coupon_code');
            $couponCode = trim($couponCode);

            //Проверка и фикс возможных кривых указаний купона
            $couponCode = \common\helpers\Promo::fixCoupon($couponCode);

            try {
                //Отключено так как теперь у нас купон работает всегда
//                if (\Yii::$app->kfssApiV2->isDisable && !\Yii::$app->session->get('new_cart')/*&& !\Yii::$app->kfssApiV2->forcedUseOnly*/) {
//                    throw new Exception('Чтобы применить промокод - сообщите его операторам при подтверждении заказа');
//                }

                if (!$couponCode) {
                    throw new Exception(\Yii::t('skeeks/shop/app', 'Not set coupon code'));
                }

                $discount_coupons = \Yii::$app->shop->shopFuser->discount_coupons;
                if (sizeof($discount_coupons) > 0) {
                    throw new Exception('Нельзя использовать более одного промокода');
                }

                /* @var $applyShopDiscountCoupon ShopDiscountCoupon */
                $applyShopDiscountCoupon = null;
                /*
                $applyShopDiscountCoupon = ShopDiscountCoupon::getActiveCouponByCode($couponCode);
                if (!$applyShopDiscountCoupon) {
                    throw new Exception(\Yii::t('skeeks/shop/app', 'Coupon does not exist or is not active'));
                }
                */

                //* KFSS API *//
                //С купонами всегда через АПИ
                if (true /*\Yii::$app->kfssApiV2->isDisable*/ /*&& \Yii::$app->kfssApiV2->forcedUseOnly*/){
                    $isKfssApiDisabled = \Yii::$app->kfssApiV2->isDisable;
                    //Отключаем отключение
                    if ($isKfssApiDisabled){
                        \Yii::$app->kfssApiV2->isDisable = false;
                    }

                    $addCouponResponse = \Yii::$app->kfssApiV2->addCoupon($couponCode);

                    //Если в целом АПИ отключено то возвращаем в исходное состояние
                    if ($isKfssApiDisabled){
                        \Yii::$app->kfssApiV2->isDisable = true;
                    }

                    if (!empty($addCouponResponse)) {
                        if (isset($addCouponResponse['isSuccess']) && $addCouponResponse['isSuccess']){

                            //* Получаем данные купона или добавляем новый если не нашелся *//

                            $applyShopDiscountCoupon = ShopDiscountCoupon::find()
                                ->andWhere(['coupon' => $couponCode])
                                ->one();


                            if (!$applyShopDiscountCoupon) {
                                $applyShopDiscountCoupon = new ShopDiscountCoupon();
                                $applyShopDiscountCoupon->coupon = $couponCode;
                            }

                            //$applyShopDiscountCoupon->shop_discount_id = $discount->id;
                            $applyShopDiscountCoupon->max_use = 1;
                            $applyShopDiscountCoupon->is_active = 1;
//                        $applyShopDiscountCoupon->description = $this->email;
//                        $applyShopDiscountCoupon->active_from = $this->active_from;
//                        $applyShopDiscountCoupon->active_to = $this->active_to;


                            if (!$applyShopDiscountCoupon->save()) {
                                \Yii::error("Coupon {$couponCode} save failed! Errors: " . print_r($applyShopDiscountCoupon->getErrors(), true), "modules\shopandshow\controllers\Cart::" . __METHOD__);
                                throw new Exception("Coupon {$couponCode} save failed!");
                            }

                            //* /Получаем данные купона или добавляем новый если не нашелся *//
                        }else{
                            \Yii::error("Ошибка при попытке применения купона. Код - '{$couponCode}', fUserId = " . \Yii::$app->shop->shopFuser->id . '; ' .
                                var_export($addCouponResponse, true));
                            throw new Exception('Ошибка при применении промокода. ' . (!empty($addCouponResponse['message']) ? $addCouponResponse['message'] : ''));
                        }
                    } else {
                        \Yii::error("Ошибка при попытке применения купона. Нет ответа сервера. Код - '{$couponCode}', fUserId = " . \Yii::$app->shop->shopFuser->id);
                        throw new Exception('Ошибка при применении купона. Попробуйте повторить попытку позже.!');
                    }
                }

                //* /KFSS API *//

                $discount_coupons[] = $applyShopDiscountCoupon->id;
                $discount_coupons = array_unique($discount_coupons);
                \Yii::$app->shop->shopFuser->discount_coupons = $discount_coupons;

                // Проверяем, можно ли использовать этот купон
                //[Deprecated] Используется если работаем с купонами только на своей стороне

                //ОТКЛЮЧАЕМ так как всегда с купоном работаем через АПИ
                if (false) {
                    if (!$applyShopDiscountCoupon->canApply(\Yii::$app->shop->shopFuser)) {
                        throw new Exception(\Yii::t('skeeks/shop/app', 'Coupon does not exist or is not active'));
                    }
                }


                \Yii::$app->shop->shopFuser->save();

                //Если есть номер заказа - обновляем заказ через АПИ
                if (\Yii::$app->shop->shopFuser->external_order_id) {
                    $orderKfss = \Yii::$app->kfssApiV2->updateOrder();
                    \Yii::$app->kfssApiV2->recalculateOrder($orderKfss);
                } else {
                    \Yii::$app->shop->shopFuser->recalculate()->save();
                }

//                $rr->data = \Yii::$app->shop->shopFuser->jsonSerialize();
                $rr->success = true;
                $rr->message = \Yii::t('skeeks/shop/app', 'Coupon successfully installed');

            } catch (\Exception $e) {
                Yii::error('Fail cart/add-discount-coupon, message ' . $e->getMessage());
                $rr->message = $e->getMessage();
                return $rr;
            }

            return $rr;
        } else {
            return $this->goBack();
        }
    }


    /**
     * Updating the positions of the basket, such as changing the number of
     *
     * @return array|\yii\web\Response
     * @throws \Exception
     */
    public function actionUpdateBasket()
    {
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost()) {
            $basket_id = (int)\Yii::$app->request->post('basket_id');
            $quantity = (float)\Yii::$app->request->post('quantity');

            //\Yii::error("Updating basket #$basket_id. Set Q={$quantity}");

//            $rr->success = true;
//            return (array)$rr;

            /**
             * @var $shopBasket ShopBasket
             */
            $shopBasket = ShopBasket::find()->where(['id' => $basket_id])->one();
            if ($shopBasket) {
                if ($quantity > 0) {
                    $product = $shopBasket->product;

                    if ($product->measure_ratio > 1) {
                        if ($quantity % $product->measure_ratio != 0) {
                            $quantity = $product->measure_ratio;
                        }
                    }

                    $shopBasket->quantity = $quantity;

                    //* KFSS *//

                    $shopBasket->save();

                    //Обновляем данные по АПИ
                    $orderKfss = \Yii::$app->kfssApiV2->updateOrder();

                    \Yii::$app->kfssApiV2->recalculateOrder($orderKfss);

                    //Moved to kfssApi
                    if (false) {
                        $product = $shopBasket->product;

                        $cmsContentElement = \common\lists\Contents::getContentElementById($product->id);
                        $shopProduct = ShopProduct::getInstanceByContentElement($cmsContentElement); //$parentElement Записываем либо цену предложения либо главного товара
                        $parentElement = $cmsContentElement->product;

//                    $kfssOffercntId = $shopBasket->cmsContentElement->kfss_id;
                        $kfssOffcntId = $cmsContentElement['kfss_id'];


                        if ($kfssOffcntId && !empty($orderKfss['positions']) && !empty($orderKfss['positions'][$kfssOffcntId])) {
                            $kfssElement = $orderKfss['positions'][$kfssOffcntId];
                            //Скидки для товара
                            $shopBasket->discount_price = $kfssElement['originalPrice'] - $kfssElement['price'];
                            $shopBasket->discount_value = "";
                            //            $this->shopBasket->discount_name = "Тестовая скидка 1 + тестовая скидка 2";
                            $shopBasket->price = $kfssElement['price'];

                            //Запишем все скидки связанные с данным товаром
                            if (!empty($kfssElement['discounts'])) {
                                $discountNames = array_column($kfssElement['discounts'], 'name');
                                $shopBasket->discount_name = implode(' + ', $discountNames);
                            }
                        }

                        //$shopBasket->price = $shopProduct->basePrice();

                        $shopBasket->name = $parentElement->getLotName();
                        $shopBasket->main_product_id = $parentElement->id;

                        $shopBasket->currency_code = 'RUB';

                        $shopBasket->save();
                    }

                    //moved to kfssApi
                    if (false) {
                        \Yii::$app->shop->shopFuser->link('site', \Yii::$app->cms->site);

                        ShopDiscount::fuserRecalculate(\Yii::$app->shop->shopFuser);
                        \Yii::$app->shop->shopFuser->save();
                    }

                    $rr->success = true;
                    $rr->data = Contents::getInfoProduct($shopBasket->main_product_id);
                    $rr->data['offer_id'] = $shopBasket->product_id;

                    return $rr;

                    //* /KFSS *//


//                    $shopBasket->recalculate();
                    if ($shopBasket->recalculate()->save()) {
//                    if ($shopBasket->save()) {
                        $rr->success = true;
                        $rr->data = Contents::getInfoProduct($shopBasket->main_product_id);
                        $rr->data['offer_id'] = $shopBasket->product_id;
                        $rr->message = \Yii::t('skeeks/shop/app', 'Postion successfully updated');
                    }

                } else {
                    \Yii::error("updateBasket - removing basket {$shopBasket->id}");
                    if ($shopBasket->delete()) {
                        \Yii::$app->kfssApiV2->updateOrder();
                        $rr->success = true;
                        $rr->message = \Yii::t('skeeks/shop/app', 'Position successfully removed');
                    }
                }

            }

            \Yii::$app->shop->shopFuser->link('site', \Yii::$app->cms->site);
            \Yii::$app->shop->shopFuser->recalculate()->save();
//            $rr->data = \Yii::$app->shop->shopFuser->jsonSerialize();
            return $rr;
        } else {
            return $this->goBack();
        }
    }

    /**
     * Мультидобавление товаров в корзину
     */
    public function actionAddProducts()
    {
        $products = \Yii::$app->request->post('products');
    }

    /**
     * Добавить товары в корзину со страницы лукбука
     * @return array|\yii\web\Response
     */
    public function actionAddProductsLookBook()
    {
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost()) {

            $sets = \Yii::$app->request->post('sets', []);
            $countSet = count($sets);

            $lookBookId = \Yii::$app->request->post('lookBookId'); //Для расчета скидки
//            $lookBook = Contents::getContentElementById($lookBookId);
            $additional = \Yii::$app->request->post('additional');

            $rr->success = false;
            $rr->message = \Yii::t('skeeks/shop/app', 'Failed to add item to cart');

//            if (!$lookBook) {
//                return (array)$rr;
//            }

            $modificationWidget = new ModificationsWidget([
                'namespace' => ModificationsWidget::getNameSpace(),
            ]);

            $parameters = $modificationWidget->getParameters();
            $parameters = ArrayHelper::index($parameters, 'id');

            foreach ($sets as $productId => $params) {
                $contentElement = CmsContentElement::find()->alias('offer');

                /**
                 * Логика такая, если есть модификации смотрим по parent_content_element_id, если нет то по id
                 */
                if (isset($params['modification'])) {
                    $contentElement->innerJoin('cms_content_element AS card', "offer.parent_content_element_id = card.id");

                    foreach ($params['modification'] as $paramId => $value) {
                        $joinTable = ModificationsWidget::$joinMap[$parameters[$paramId]->code] ?? 'offer';
                        $alias = 'q_' . $productId . '_' . $paramId;
                        $contentElement->innerJoin('cms_content_element_property AS ' . $alias, "$joinTable.id = $alias.element_id AND $alias.property_id = $paramId AND $alias.value = $value");
                    }

                    $contentElement->where(['card.parent_content_element_id' => $productId]);
                } else {
                    $contentElement->where(['offer.id' => $productId]);
                }

                $contentElement->groupBy('offer.id');

                if ($product = $contentElement->one()) {

                    if (ShopBasket::addProduct($product->id, 1, [ShopBasket::LOOKBOOK_CODE => $lookBookId])) {
                        $rr->success = true;
                        $rr->message = \Yii::t('skeeks/shop/app', 'Item added to cart');

                        if ($additional && isset($additional[SsShare::BID_PARAM])) {
                            SsShareSeller::add($additional[SsShare::BID_PARAM], $productId);
                        }

                    } else {
                        $rr->success = false;
                        $rr->message = 'Извините, товар закончился.';
                        return $rr;
                    }
                }
            }

            /**
             * Если кол-во добавленных товаров в луке совпадает с  кол-вом товара в луке то даем скидку
             */
            /*            $productIds = $lookBook->relatedPropertiesModel->getAttribute('products');
                        $productIds = ($productIds) ? array_values($productIds) : [];
                        $countProducts = count($productIds);

                        if ($countSet == $countProducts) {
                        }*/

            return $rr;
        } else {
            return $this->goBack();
        }
    }

    /**
     * Расчет доставки
     * @return array|RequestResponse
     */
    public function actionDeliveryCalc()
    {
        $rr = new RequestResponse();

        $rr->success = false;
        $rr->message = 'К сожалению, не получилось уточнить стоимость';

        if ((\Yii::$app->request->post() || $rr->isRequestAjaxPost() || $rr->isRequestPjaxPost())) {

            $index = (int)\Yii::$app->request->post('index');

            if (strlen($index) !== 6) {
                $rr->message = 'Некорректный индекс';
                return $rr;
            }

            $localDeliveryDays = \Yii::$app->shop->shopFuser->getDeliveryDays();
            $weight = \Yii::$app->shop->shopFuser->getWeightProducts();
            $cartCost = \Yii::$app->shop->shopFuser->money->getValue();

            $services = \Yii::$app->getModule('delivery')->getComponents();
            $data = new DeliveryCargoData($index, '', $cartCost, $weight, 275); // zip, locationTo, cartCost, weight, innerCode (own carrier code)
            $calculator = new DeliveryCalculator($data, $services);
            $result = $calculator->calculate();

            $type = \uranum\delivery\module\Module::DELIVERY_CODE['NALOJ'];

            if ($result && isset($result[$type])) {

                $data = $result[$type];

                if (isset($data['terms']) && isset($data['cost'])) {
                    $rr->message = '';
                    $rr->success = true;
                    $rr->data = [
                        'cost' => \common\helpers\Math::roundingUp($data['cost'], 50),
                        'days' => (int)$data['terms'],
                        'local_days' => $localDeliveryDays,
                        'weight' => $weight,
                    ];

                    return $rr;
                }

            }

            return $rr;
        }
    }


    /**
     * Оформление заказа через 1 клик
     * @return array|\yii\web\Response
     */
    public function actionOneClick()
    {
        $rr = new RequestResponse();

        if ($rr->isRequestAjaxPost()) {

            $sets = \Yii::$app->request->post('sets', []);
            $phone = \Yii::$app->request->post('phone');
            $quantity = \Yii::$app->request->post('quantity', 1);

            $products = [];

            $rr->success = false;
            $rr->message = 'Извините, товар закончился.';

            foreach ($sets as $productId => $params) {
                $contentElement = CmsContentElement::find();

                /**
                 * Логика такая, если есть модификации смотрим по parent_content_element_id, если нет то по id
                 */
                if (isset($params['modification'])) {
                    foreach ($params['modification'] as $paramId => $value) {
                        $alias = 'q_' . $productId . '_' . $paramId;
                        $contentElement->innerJoin('cms_content_element_property AS ' . $alias, "cms_content_element.id = $alias.element_id AND $alias.property_id = $paramId AND $alias.value = $value");
                    }
                    $contentElement->where(['cms_content_element.parent_content_element_id' => $productId]);
                } else {
                    $contentElement->where(['cms_content_element.id' => $productId]);
                }
                $contentElement->groupBy('cms_content_element.id');

                if ($product = $contentElement->one()) {
                    $products[] = $product->id;
                }
            }

            if ($products && ($order = CheckoutWidget::oneClick($phone, $products, $quantity))) {
                $rr->success = true;
                $rr->message = \Yii::t('skeeks/shop/app', 'Item added to cart');
                $rr->data = [
                    'order' => $order->publicUrl
                ];
            }

            return $rr;
        } else {
            return $this->goBack();
        }
    }

    /**
     * Установка выбранной доставки
     * @return array|RequestResponse
     */
    public function actionSetDelivery()
    {
        $rr = new RequestResponse();

        $rr->success = false;
        $rr->message = 'К сожалению, не получилось сохранить выбранный способ доставки';

        if ((\Yii::$app->request->post() || $rr->isRequestAjaxPost() || $rr->isRequestPjaxPost())) {

            $deliveryServiceId = (int)\Yii::$app->request->post('delivery_service_id');
            $orderKey = \Yii::$app->request->post('order_key');

            $deliveryService = DeliveryServices::findOne($deliveryServiceId);
            if (!$deliveryService) {
                $rr->message = 'Не найден способ доставки';
                return $rr;
            }

            \Yii::$app->shop->shopFuser->delivery_id = $deliveryService->delivery_id;

            if (\Yii::$app->shop->shopFuser->save(false, ['delivery_id'])) {
                $rr->success = true;
                $rr->message = 'Способ доставки сохранен';
            }

            if ($orderKey) {
                /** @var ShopOrder $shopOrder */
                $shopOrder = ShopOrder::find()->where(['key' => $orderKey, 'status_code' => ShopOrderStatus::STATUS_DELAYED])->one();
                if ($shopOrder) {
                    $shopOrder->delivery_id = $deliveryService->delivery_id;
                    if (!$shopOrder->save(false, ['delivery_id'])) {
                        $rr->success = false;
                        $rr->message = 'Способ доставки не сохранен';
                    }
                }
            }

            return $rr;
        }
    }

    /**
     * Установка выбранной доставки в корзине для фузера
     * @return array|RequestResponse
     */
    public function actionSetShopDelivery()
    {
        $rr = new RequestResponse();

        $rr->success = false;
        $rr->message = 'К сожалению, не получилось сохранить выбранный способ доставки';

        if ((\Yii::$app->request->post() || $rr->isRequestAjaxPost() || $rr->isRequestPjaxPost())) {

            $deliveryId = (int)\Yii::$app->request->post('delivery_id');

            if (\Yii::$app->shop->shopFuser->setDelivery($deliveryId)) {
                //Не отрабатывает в режиме обновления по кнопке, так что пока отключим за невостребованностью
                //\Yii::$app->kfssApiV2->updateOrder();

                \Yii::$app->kfssApiV2->unsetCookieLastRecalc();

                $rr->success = true;
                $rr->message = 'Способ доставки сохранен';
            }

            return $rr;
        }
    }

    /**
     * Установка выбранного типа оплаты в корзине для фузера
     * @return array|RequestResponse
     */
    public function actionSetShopPaymentType()
    {
        $rr = new RequestResponse();

        $rr->success = false;
        $rr->message = 'К сожалению, не получилось сохранить выбранный способ оплаты';

        if ((\Yii::$app->request->post() || $rr->isRequestAjaxPost() || $rr->isRequestPjaxPost())) {

            $paymentId = (int)\Yii::$app->request->post('payment_id');

            if (\Yii::$app->shop->shopFuser->setPayment($paymentId)) {
                \Yii::$app->kfssApiV2->updateOrder();

                $rr->success = true;
                $rr->message = 'Способ оплаты сохранен';
            }

            return $rr;
        }
    }

    /**
     * Установка выбранного пункта выдачи заказов в корзине для фузера
     * @return array|RequestResponse
     */
    public function actionSetShopDeliveryPvz()
    {
        $rr = new RequestResponse();

        $rr->success = false;
        $rr->message = 'К сожалению, не получилось сохранить выбранный способ доставки';

        $pvzData = \Yii::$app->request->post('response');

        if (\Yii::$app->request->post() || $rr->isRequestAjaxPost()) {

            \Yii::$app->shop->shopFuser->pvz_data = serialize($pvzData);

            if (\Yii::$app->shop->shopFuser->save(true, ['pvz_data'])) {
                //Требование пересчета
                \Yii::$app->kfssApiV2->unsetCookieLastRecalc();

                if (!\Yii::$app->kfssApiV2->isDisable) {
                    //Отключено, так как обновление при выборе ПВЗ слишком накладно, отложено для явного вызова обновления заказа
                    //\Yii::$app->kfssApiV2->updateOrder();
                }
                $rr->success = true;
                $rr->message = 'Способ доставки сохранен';
            }

            return $rr;
        }
    }

    public function actionRecalculateRemote()
    {
        $result = ['success' => false, 'message' => 'Deprecated'];

        return $result;

        //Отключаем работу
        \Yii::$app->kfssApiV2->isDisable = false;

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $orderKfss = null;

        if (Yii::$app->request->isAjax){
            $shopFuser = \Yii::$app->shop->shopFuser;
            $kfssOrderId = $shopFuser->external_order_id;

            //Если корзина не пуста и у нас нет номера заказа в КФСС - пробуем отправить заказ и получить его номер и тп
            if (!$shopFuser->isEmpty() && !$kfssOrderId) {

                $orderKfss = \Yii::$app->kfssApiV2->createOrder();

                if ($orderKfss && !empty($orderKfss['orderId'])) {
                    $kfssOrderId = $orderKfss['orderId'];
                    $shopFuser->external_order_id = $kfssOrderId;
                    $shopFuser->save();
                } else {
                    //Не удалось создать заказ
                    \Yii::error('Cant create kfss order');
                }
            } elseif ($kfssOrderId) {
                $orderKfss = \Yii::$app->kfssApiV2->updateOrder($kfssOrderId);
            }

            if ($orderKfss){
                //Приводим корзину в соответствие с заказом из АПИ
                \Yii::$app->kfssApiV2->recalculateOrder($orderKfss);
            }
        }

        if ($orderKfss){
            $result['success'] = true;
        }

        \Yii::$app->kfssApiV2->isDisable = true;

        return $result;
    }
}
