<?php


namespace modules\api\controllers\v2;


use common\helpers\Api as ApiHelper;
use common\helpers\Common;
use common\helpers\Order;
use common\helpers\Strings;
use common\helpers\ThemeHelper;
use common\helpers\User;
use common\helpers\User as UserHelper;
use common\models\DeliveryAddress;
use common\models\generated\models\ShopDiscountCoupon;
use common\models\Product;
use common\models\ShopBasket;
use common\models\ShopDelivery;
use common\models\ShopOrder;

//use modules\shopandshow\models\shop\ShopBasket;
use common\models\ShopPaySystem;
use common\filters\Cors;
use modules\shopandshow\models\shop\forms\QuickOrder;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\rest\Controller;

class CartController extends Controller
{
    const EVENT_COMPLETE_CART_ITEMS = 'CHANGE_STEP_CART';
    const EVENT_COMPLETE_SHIPPING = 'CHANGE_STEP_SHIPPING';
    const EVENT_COMPLETE_PAYMENT = 'CHANGE_STEP_PAYMENT';
    const EVENT_COMPLETE_CLIENT_DATA = 'CHANGE_STEP_CUSTOMER'; // == FINISH_ORDER
    const EVENT_FINISH_ORDER = 'FINISH_ORDER';
    const EVENT_UPDATE_COUPON = 'UPDATE_PROMO_CODE';

    public static $eventsForValidate = [
        self::EVENT_COMPLETE_CART_ITEMS,
        self::EVENT_COMPLETE_SHIPPING,
        self::EVENT_COMPLETE_PAYMENT,
        self::EVENT_COMPLETE_CLIENT_DATA,
//        self::EVENT_UPDATE_COUPON, //валидируется вручную
    ];

    private $response = [
        'success' => false,
        'message' => '',
        'data' => []
    ];

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // remove authentication filter
        $auth = $behaviors['authenticator'];
        unset($behaviors['authenticator']);

        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => Cors::className(),
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Allow-Credentials' => 'true',
                'Access-Control-Allow-Headers' => ['Origin', 'Content-Type', 'Accept'],
                'Access-Control-Request-Method' => ['GET', 'HEAD', 'OPTIONS', 'POST'],
            ],
        ];

        // re-add authentication filter
        //$behaviors['authenticator'] = $auth;
        // avoid authentication on CORS-pre-flight requests (HTTP OPTIONS method)
        //$behaviors['authenticator']['except'] = ['options'];

        $behaviors['verbsFilter'] = [
            'class' => VerbFilter::className(),
            'actions' => [
                'index' => ['get'],
                'remove' => ['post'],
                'add-product' => ['post'],
                'add-discount-coupon' => ['post'],
            ],
        ];

        $behaviors['contentNegotiator'] = [
            'class' => \yii\filters\ContentNegotiator::className(),
            'formats' => [
                'application/json' => \yii\web\Response::FORMAT_JSON,
            ],
        ];

        return $behaviors;

    }

    public function actionIndex()
    {
        $this->response['success'] = true;
        $this->response['message'] = 'Корзина';

        //* Добавляем состав корзины *//

        //Не удаленные товары в корзине
        $baskets = \Yii::$app->shop->shopFuser->getShopBaskets()->orderBy('id ASC')->all();

        if ($baskets) {
            /** @var ShopBasket $basket */
            foreach ($baskets as $basket) {
                $this->response['data']['cart'][] = [
                    'id' => $basket->product_id,
                    'quantity' => (int)$basket->quantity
                ];
            }
        }

        //* /Добавляем состав корзины *//

        return $this->response;
    }

    public function actionAddProduct()
    {
        $productId = \Yii::$app->request->post('product_id');
        $quantity = \Yii::$app->request->post('quantity', 1);
        //Добавить возможность принимать тип цены по которой продается товар

        if ($productId) {

//            $this->response['success'] = false;
//            $this->response['message'] = \Yii::$app->shop->shopFuser->id;
//            return $this->response;

            /** @var Product $product */
            $product = Product::find()
                ->where([
                    'content_id' => [Product::LOT, Product::MOD],
                    'id' => $productId
                ])
                ->one();

            if ($product) {
                if ($product->isLot()) {

                    //TODO Найти первую нормальную моификацию!
                    $offer = $product;
                } else {
                    $offer = $product;
                }

                $productAddResponse = ShopBasket::add($offer->id, $quantity);

                if ($productAddResponse && isset($productAddResponse['success'])) {
                    $this->response['success'] = $productAddResponse['success'];
                    $this->response['message'] = $productAddResponse['message'];
                } else {
                    $this->response['success'] = false;
                    $this->response['message'] = 'Ошибка при добавлении товара в корзину';
                }

            } else {
                //Товар не найден совсем
                $this->response['success'] = false;
                $this->response['message'] = "Товар не найден [id={$productId}]";
            }
        }

        //* Добавляем состав корзины *//

        //Не удаленные товары в корзине
        $baskets = \Yii::$app->shop->shopFuser->getShopBaskets()->orderBy('id ASC')->all();

        if ($baskets) {
            /** @var ShopBasket $basket */
            foreach ($baskets as $basket) {
                $this->response['data']['cart'][] = [
                    'id' => $basket->product_id,
                    'quantity' => (int)$basket->quantity
                ];
            }
        }

        //* /Добавляем состав корзины *//

        return $this->response;
    }

    //Обновляет позицию корзины
    public function actionUpdateBasket()
    {
        $basketId = \Yii::$app->request->post('basketId');
        $productId = \Yii::$app->request->post('productId');
        $quantity = \Yii::$app->request->post('quantity', 1);
        //Добавить возможность принимать тип цены по которой продается товар

        if ($productId || $basketId) {
            $fUser = \Yii::$app->shop->shopFuser;

            /** @var ShopBasket $shopBasket */
            if ($basketId) {
                $shopBasket = ShopBasket::find()->where(['fuser_id' => $fUser->id, 'id' => $basketId])->one();
            } else {
                $shopBasket = ShopBasket::find()->where(['fuser_id' => $fUser->id, 'product_id' => $productId])->one();
            }

            if ($shopBasket) {
                $updateResult = $shopBasket->updateBasket($quantity);

                if ($updateResult === true) {
                    $this->response['success'] = true;
                } else {
                    $this->response['message'] = $updateResult;
                }
            }
        } else {
            //Товар не найден совсем
            $this->response['success'] = false;
            $this->response['message'] = "Товар не найден";
        }

        $this->response['data'] = ApiHelper::getCartData();

        return $this->response;
    }

    public function actionMiniCart()
    {
        if (true /*ThemeHelper::isEnable()*/) {
            $shopBaskets = \Yii::$app->shop->shopFuser->shopBaskets;
            $countShopBaskets = (int)\Yii::$app->shop->shopFuser->getQuantity();
            $linkCart = Url::to(['/shop/cart']);

            $view = $this->renderPartial('@frontend/themes/v3/layouts/parts/_mini-cart', [
                'shopBaskets' => $shopBaskets,
                'countShopBaskets' => $countShopBaskets,
                'linkCart' => $linkCart,
            ]);

            return [
                'amount' => $countShopBaskets,
                'miniCart' => $view
            ];

        } else {
            return $this->renderPartial('@template/widgets/ShopCart/_small-top_no_pjax');
        }
    }

    public function actionOneClick()
    {
        $formData = \Yii::$app->request->post('formData');

        $result = [
            'success' => false,
            'message' => '',
        ];

        if ($formData) {
            $phone = '';
            $dadata = [];
            $source = \Yii::$app->request->post('src');

            //Проверяем наличие источника в списке источников
            $source = isset(ShopOrder::$sourceLabels['main'][$source]) ? $source : ShopOrder::SOURCE_UNKNOWN;

            foreach ($formData as $formDataItem) {
                switch ($formDataItem['name']) {
                    case 'location_data':
                        $dadata['data'] = json_decode($formDataItem['value'], true);
                        break;
                    case 'user_phone':
                        $phone = \common\helpers\Strings::getPhoneClean($formDataItem['value']);
                        break;
                }
            }

            //* Хотфикс от определенного клиента *//

            if ($phone == '79272206736') {
                $phone = '';
            }

            //* /Хотфикс от определенного клиента *//

            $phoneCallAvailable = true;

            //* Проверка валидности и доступности номера телефона *//

            //Только для CPA

            if ($source == ShopOrder::SOURCE_CPA) {
                $showServerMessage = true;

                $phoneAvailibilityForCall = \Yii::$app->smscHlr->getPhoneAvailabilityForCall($phone);

                if (!$phoneAvailibilityForCall['available']) {
                    $phoneCallAvailable = false;
                    $result['success'] = false;
                    $result['message'] = $phoneAvailibilityForCall['message'] ?: '';
                }
            }

            //* /Проверка валидности и доступности номера телефона *//

            $productId = \Yii::$app->request->post('variationId');

            //* Проверка на заблокированность для редиректа *//

            if (!empty($phone) && $source != ShopOrder::SOURCE_CPA) {
                $kfssUser = \Yii::$app->kfssLkApiV2->getUserByPhone($phone);
                $kfssUserBlocked = UserHelper::isBlockedKfssUser($kfssUser);

                $result['success'] = false;
                if ($kfssUserBlocked) {
                    $result['message'] = 'Возникла ошибка';
                } else {

                    //Отключаем проверку выкупа в связи с поступлением соответствующего указания
                    if (false) {
                        //Дополнительное условие.
                        //Если у клиента есть выкупленный заказ - редиректим в корзину
                        //Если выкупленных заказов нет - не редиректим, отправляем как и раньше в КЦ
                        $isNeedRedirectToCart = false;

                        if (!empty($kfssUser['data']['id'])) {
                            $kfssUserId = $kfssUser['data']['id'];

                            //Проверка  по обновленному АПИ
                            if (isset($kfssUser['data']['hasRepurchasedOrders'])) {
                                $ordersComplete = $kfssUser['data']['hasRepurchasedOrders'];
                            } else {
                                $ordersComplete = \Yii::$app->kfssLkApiV2->getUserOrdersWithStatus($kfssUserId, \Yii::$app->kfssLkApiV2::ORDER_STATUS_COMPLETE);
                            }

                            if ($ordersComplete) {
                                $isNeedRedirectToCart = true;
                            }
                        }
                        if ($isNeedRedirectToCart) {
                            //Добавляем товар как обычный в корзину
                            //                        $productAdded = ShopBasket::addProduct($productId);
                            $productAdded = ShopBasket::add($productId);

                            //Перенаправляем в корзину
                            $result['data']['redirect'] = Url::to(['/shop/cart', 'from' => '1click']);
                        }

                        \Yii::error("Phone: {$phone}. kfssUserID: "
                            . ($kfssUser['data']['id'] ?? 'EMPTY')
                            . ", OrdersCompleteNum = " . (!empty($ordersComplete) ? count($ordersComplete) : 'N/A')
                            . ", RedirectToCart: " . ($isNeedRedirectToCart ? 'Y' : 'N'), "1click_redirect_check");
                    }
                }
            }

            //* /Проверка на заблокированность для редиректа *//

            if (!empty($phone) && $phoneCallAvailable && empty($result['data']['redirect'])) {
                $result = ShopOrder::checkoutOneClick($productId, $phone, $dadata, $source);
            }
        }

        return [
            'success' => $result['success'] ?? false,
            'message' => $result['message'] ?? 'Ошибка при оформлении заказа.',
            'data' => $result['data'] ?? [],
            'showServerMessage' => $showServerMessage ?? false,
        ];
    }


    //************************//

    //Отдать полные данные по корзине
    public function actionGet()
    {
        $this->response['success'] = true;
        $this->response['data'] = ApiHelper::getCartData();

        return $this->response;
    }

    //Принимает все данные по корзине, проверяет что изменилось и производит изменения
    public function actionUpdate()
    {
        $fuser = \Yii::$app->shop->shopFuser;
        $user = User::getUser();
        //Пойдем по принципу все хорошо, если не встретится плохо
        $this->response['success'] = true;
        $validateErrors = [];

        //Событие из фронта
        $event = \Yii::$app->request->post('event');

        //Данные из фронта
        $data = \Yii::$app->request->post('data');

        if ($data) {

            //Текущие данные, с ними будем сравнивать то что пришло что бы понять что изменилось
//        $data = ApiHelper::getCartData();

            //Массив для записи списка того что изменилось
            $hasChanges = [];

            //Соберем все пришедшие данные, сверим с теми что у нас уже есть и сихронизируем
            //После этого пробуем синхрониться с кфсс и после этого возвращаем ответ

            //* Тип оплаты*//
            $paySystemId = $data['payment']['method'] ?? ShopPaySystem::ID_DEFAULT;
            if ($fuser->pay_system_id != $paySystemId) {
                //TODO Возможно все таки стоит добавить валидацию типа по белому списку
                $hasChanges['paymentType'] = Common::BOOL_Y_INT;
                $fuser->setPayment($paySystemId, false);
            }
            //* /Тип оплаты*//

            //* Тип доставки *//
            $deliveryId = $data['shipping']['method'] ?? ShopDelivery::ID_DEFAULT;

            $deliveryTypeEvents = [
                self::EVENT_COMPLETE_CART_ITEMS,
                self::EVENT_COMPLETE_SHIPPING,
            ];

            if ($fuser->delivery_id != $deliveryId || in_array($event, $deliveryTypeEvents)) {
                //TODO Возможно все таки стоит добавить валидацию типа по белому списку
                $hasChanges['deliveryType'] = Common::BOOL_Y_INT;
                $fuser->setDelivery($deliveryId);
            }
            //* /Тип доставки *//

            //* Адрес доставки *//
            $deliveryAddress = $data['shipping']['address'] ?? [];

            $deliveryAddressEvents = [
                self::EVENT_COMPLETE_CART_ITEMS,
                self::EVENT_COMPLETE_SHIPPING,
            ];

            if ($deliveryAddress && in_array($event, $deliveryAddressEvents)) {
                $hasChanges['deliveryAddress'] = Common::BOOL_Y_INT;

                $fuser->pvz_data = !empty($deliveryAddress['pvzData']) ? serialize($deliveryAddress['pvzData']) : '';
                $fuser->save(true, ['pvz_data']);

                $deliveryAddressModel = DeliveryAddress::getByFuser();
                //Дынные для заполнения модели
                $deliveryAddressAttributes = [
                    'delivery_id' => $fuser->delivery_id,
                    'pvz_id' => !empty($fuser->pvz['id']) ? $fuser->pvz['id'] : null,
                    'description' => '',
//                    'kladr_id' => $profile['kladr_id'] ?: '',
                    'city_with_type' => @$deliveryAddress['city'] ?: 'г Москва',
//                    'city_kladr_id' => $profile['FiasCodeCity'] ?: '',
                    'street_with_type' => $deliveryAddress['street'] ?? '',
//                    'street_kladr_id' => $profile['FiasCodeStreet'] ?: '',
                    'house' => $deliveryAddress['building'] ?? '',
//                    'house_kladr_id' => $profile['FiasCodeBuilding'] ?: '',
                    'flat' => $deliveryAddress['apartmentNumber'] ?? '',
//                    'postal_code' => $profile['postal_code'] ?: '',
                    'dadata' => $deliveryAddress['daData'] ?? '',
                ];
                $deliveryAddressModel->setAttributes($deliveryAddressAttributes);
                $deliveryAddressModel->save();
            }

            //* /Адрес доставки *//

            //* Данные клиента *//

            $userData = $data['customer'] ?? [];

            if ($userData) {
                $modelUser = $user ?: new QuickOrder();

                $userAttributes = [
                    'phone' => $userData['phoneNumber'] ?? '',
                    'email' => $userData['email'] ?? '',
                    'name' => $userData['firstName'] ?? '',
                    'patronymic' => $userData['middleName'] ?? '',
                    'surname' => $userData['lastName'] ?? '',
                ];

                $modelUser->setAttributes($userAttributes);

//                if ($modelUser->validate()) { //TODO Добавить условие валидации только на определенном шаге
                if (true) {
                    //Сохраняем
                    if ((!$fuser->phone && $modelUser->phone) || ($fuser->phone && $fuser->phone != $modelUser->phone)) {
                        $fuser->phone = Strings::getPhoneClean($modelUser->phone);
                        $fuser->save();
                    }

                    //Но куда то надо сохранить введенную клиентом инфу что бы не вводить ему ее заново
                    //сохраним в имеющегося фузера
                    $fuser->additional = serialize(\Yii::$app->request->post());
                    $fuser->save();

                } else {
                    $this->response['success'] = false;
                    $this->response['message'] .= var_export($modelUser->getErrors(), true);
                }
            }


            //* /Данные клиента *//

            //* Купон *//

            //Работа с купонами только через КФСС. Если КФСС недоступен - просто пишем что ошибка и тп
            if (true) {

                //Функционально можно вроде как использовать несколько купонов, но на практике у нас больше одного купона низя
                $fuserCoupon = $fuser->discount_coupons ? current($fuser->discount_coupons) : '';
                $coupon = $data['cart']['promoCode'] ?? null;
                $couponCode = trim($coupon['code']) ?? '';
                if ($couponCode != $fuserCoupon) {
                    //Применение купонов только для авторизорванных
                    if($couponCode && User::isGuest()){
                        $couponError = 'Для применения купона необходимо авторизоваться';
                        $validateErrors[] = $couponError;
                    }

                    if (empty($couponError)) {

                        //Проверка и фикс возможных кривых указаний купона
                        $couponCode = \common\helpers\Promo::fixCoupon($couponCode);
                        $hasChanges['coupon'] = Common::BOOL_Y_INT;
                        if (!$fuserCoupon && $couponCode) {
                            //Добавление купона

                            /* @var $applyShopDiscountCoupon ShopDiscountCoupon */
                            $applyShopDiscountCoupon = null;

                            $addCouponResponse = \Yii::$app->kfssApiV3->addCoupon($couponCode);

                            if (!empty($addCouponResponse)) {
                                if (isset($addCouponResponse['isSuccess']) && $addCouponResponse['isSuccess']) {

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
                                        //                                    throw new Exception("Coupon {$couponCode} save failed!");
                                    } else {
                                        //Купон добавился, сохранился - записываем ассоциирование в фузера
                                        $discountCoupons[] = $applyShopDiscountCoupon->id;
                                        $discountCoupons = array_unique($discountCoupons);
                                        $fuser->discount_coupons = $discountCoupons;

                                        if (!$fuser->save()) {
                                            //error
                                        }
                                    }

                                    //* /Получаем данные купона или добавляем новый если не нашелся *//
                                } else {
                                    $this->response['success'] = false;

                                    \Yii::error("Ошибка при попытке применения купона. Код - '{$couponCode}', fUserId = " . \Yii::$app->shop->shopFuser->id . '; ' .
                                        var_export($addCouponResponse, true), __METHOD__);
                                    //                                throw new Exception('Ошибка при применении промокода. ' . (!empty($addCouponResponse['message']) ? $addCouponResponse['message'] : ''));
                                }

                                //И для ошибки и лоя успеха текст в одинаковом месте
                                $this->response['message'] .= !empty($addCouponResponse['message']) ? $addCouponResponse['message'] : '';
                            } else {
                                $this->response['success'] = false;
                                $this->response['message'] .= 'Ошибка обновления купона. Попробуйте повторить попытку позже';

                                \Yii::error("Ошибка при попытке применения купона. Нет ответа сервера. Код - '{$couponCode}', fUserId = " . \Yii::$app->shop->shopFuser->id, __METHOD__);
                                //                            throw new Exception('Ошибка при применении купона. Попробуйте повторить попытку позже.!');
                            }

                        } elseif ($fuserCoupon && !$couponCode) {
                            //Удаление купона
                            //КФСС не умеет просто отменять купоны, так что тут мы просто убирем купон у нас, пересчитаем товары обратно в цены каталога
                            //При обновлении КФСС инициируем пересоздание заказа что бы убрать купон

                            $fuser->discount_coupons = [];
                            if (!$fuser->save()) {
                                //error
                            }else{
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
                            }

                        } else {
                            //error case
                        }
                    }
                }
            } else {
                $this->response['success'] = false;
                $this->response['message'] .= 'Ошибка обновления купона. Попробуйте повторить попытку позже.';
            }


            //* /Купон *//

            //* Товары *//

            $products = $data['cart']['items'] ?? [];

            if (!empty($products)) {
                foreach ($products as $index => $basket) {
                    $basketId = $basket['id'];
                    $quantity = max(0, $basket['quantity']);

                    $shopBasket = ShopBasket::findOne($basketId);
                    if ($shopBasket) {
                        //Если товар в кфсс не отправлялся (пусто в kfss_position_id) то считаем что в корзине что-то изменилось и товары надо синхронизировать
                        if (!$shopBasket->kfss_position_id){
                            $hasChanges['products'] = Common::BOOL_Y_INT;
                        }

                        if ($shopBasket->quantity != $quantity) {
                            $hasChanges['products'] = Common::BOOL_Y_INT;
                            $basketUpdated = $shopBasket->updateBasket($quantity);
                            if ($basketUpdated !== true) {
                                $this->response['success'] = false;
                                $this->response['message'] .= $basketUpdated;
                            }
                        }
                    } else {
                        $this->response['message'] .= "Товар [{$basketId}] не найден. ";
                    }
                }
            }

//        $this->response['message'] .= var_export($hasChanges, true);

            //* /Товары *//

            //* CART STEPS VALIDATION *//

            $validateErrors = in_array($event, self::$eventsForValidate) ? $this->validateEventData($event) : [];
            if (!empty($couponError)){
                $validateErrors = array_merge($validateErrors, [$couponError]);
            }

            if ($validateErrors){
                $this->response['success'] = false;
                $this->response['message'] .= implode(', ', $validateErrors);
            }

            //* /CART STEPS VALIDATION *//

            //Тут подумать как решить, это кейс требующий участия (пересчета кфсс или нет)
            if (true) {

                if (!$validateErrors) {
                    //Для тестов, нужно добавить проверку на наличие изменений
//                    $hasChanges['deliveryAddress'] = Common::BOOL_Y_INT;
                    $updateOrderResponse = \Yii::$app->kfssApiV3->updateOrder($hasChanges);
                    if (!empty($updateOrderResponse['orderId'])) {
                        $orderKfss = $updateOrderResponse;
                        //Синхроним кфсс заказ с локальными данными
                        \Yii::$app->kfssApiV3->recalculateOrder($orderKfss);
                    }
                }

            }
        }

        $this->response['data'] = ApiHelper::getCartData();

        if (!empty($orderKfss['lockstock'])){
            $this->response['data']['payment']['cardPaymentAvailable'] = true;
        }

        //* CHECKOUT *//

        if (($event == self::EVENT_FINISH_ORDER || $event == self::EVENT_COMPLETE_CLIENT_DATA) && !$validateErrors) {
            $checkoutResponse = ShopOrder::checkout();

            if (!empty($checkoutResponse) && isset($checkoutResponse['success']) && $checkoutResponse['success'] && $checkoutResponse['data']) {
                $this->response['success'] = true;
                /** @var ShopOrder $order */
                $order = $checkoutResponse['data'];

                //При онлайн оплате приходится где то сохранять урль формы оплаты, пока лучше комента ниче не нашлось (
                //Отдельно городить поле не стал
                $urlRedirect = $order->comments ?: $order->publicUrl;
                $this->response['data']['url'] = $urlRedirect;
                $this->response['data']['order'] = [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                ];
            } else {
                $this->response['success'] = false;
                $this->response['message'] = !empty($checkoutResponse['message']) ? $checkoutResponse['message'] : 'Возникла ошибка при создании заказа. Попробуйте повторить попытку позже.';
            }
        }

        //* /CHECKOUT *//

        return $this->response;
    }

    //* ВАЛИДАТОРЫ СОБЫТИЙ/ЭТАПОВ КОРЗИНЫ *//

    //TODO Вынести в отдельную модель с нормальной валидацией!!!

    private function validateEventData($event)
    {
        $errors = [];
        $fuser = \Yii::$app->shop->shopFuser;

        switch ($event){
            case self::EVENT_COMPLETE_CART_ITEMS:
                //Список товаров, пока валидировать вроде особо нечего
                break;
            case self::EVENT_UPDATE_COUPON:
                //Купоны только авторизованным
                if (User::isGuest()){
                    $errors[] = "Для применения купона необходимо авторизоваться";
                }
                break;
            case self::EVENT_COMPLETE_SHIPPING:
                //Для доставки 2 кейса
                // - почта или курьер - нужен нормальный адрес, как именно точно валидировать пока не знаем, пока минималка, город и что нибудь еще
                // - ПВЗ - обязателен идентификатор пункта выдачи
                if ($fuser->delivery_id == ShopDelivery::ID_PVZ){
                    $pvzData = $fuser->pvz;
                    if (empty($pvzData['id'])) {
                        $errors[] = 'Необходимо выбрать пункт выдачи';
                    }
                }else{

                }

                break;
            case self::EVENT_COMPLETE_PAYMENT:
                break;
            case self::EVENT_COMPLETE_CLIENT_DATA:
                $clientData = Order::getClientData();

                $reqFields = ['phone', 'name', 'surname', 'patronymic'];
                foreach ($reqFields as $reqField) {
                    if (!$clientData[$reqField]){
                        $errors[] = 'Заполните обязательные поля';
                        break;
                    }
                }

                break;
            default:
                //Непонятный шаг, не будем валидироваться
        }

        return $errors;
    }

    //* /ВАЛИДАТОРЫ СОБЫТИЙ/ЭТАПОВ КОРЗИНЫ *//
}