<?php


namespace common\helpers;


use common\helpers\User as UserHelper;
use common\models\DeliveryAddress;
use common\models\Product;
use common\models\PromoSchedule;
use common\models\Setting;
use common\models\ShopBasket;
use common\models\ShopDelivery;
use common\models\ShopDiscount;
use common\models\ShopPaySystem;
use common\thumbnails\Thumbnail;
use yii\helpers\Html;

class Api
{
    private static $response = [
        'success' => false,
        'message' => '',
        'data' => []
    ];

    public static function getCartData()
    {
        $formData = \Yii::$app->shop->shopFuser->additional ? unserialize(\Yii::$app->shop->shopFuser->additional) : false;
        return [
            'id' => \Yii::$app->shop->shopFuser->id, //* Пользователь корзины *//
            'externalOrderId' => \Yii::$app->shop->shopFuser->external_order_id ?: '', //* Пользователь корзины *//
            'customer' => self::getUserData(), //* Данные клиента *//
            'shipping' => self::getCartShippingData(), //* Доставка *//
            'payment' => self::getCartPaymentData(), //* Способы оплаты *//
            'billing' => self::getCartBillingData(), //* Финансы *//
            'discount' => self::getCartDiscountData(), //* Скидки *//
            'cart' => [
                'items' => self::getCartItems(), //* Товарные позиции в корзине *//
                'promoCode' => self::getCartCouponData(), //* Данные  *//
            ],
            'url' => null,
        ];
    }

    public static function getUserData()
    {
        $fUser = \Yii::$app->shop->shopFuser;
        $formData = $fUser->additional ? unserialize($fUser->additional) : false;
        $customer = $formData['data']['customer'] ?? [];
        //Пока все еще оставляем юзера связанного со  скексом ((
//        if (!$user = UserHelper::getUser()) {
//            $user = new QuickOrder();
//        }

        $user = UserHelper::getUser();

        $userName = $user->name ?? '';
        $userSurname = $user->surname ?? '';
        $userPatronymic = $user->patronymic ?? '';
        $userEmail = $user->email ?? '';

        return [
            'firstName' => $customer['firstName'] ?? $userName,
            'lastName' => $customer['lastName'] ?? $userSurname,
            'middleName' => $customer['middleName'] ?? $userPatronymic,
            'phoneNumber' => Strings::getPhoneClean($customer['phoneNumber'] ?? $fUser['phone']),
            'email' => $customer['email'] ?? $userEmail,
//            'newsletterSubscription' => true,
        ];
    }

    public static function getCartShippingData()
    {
        $fUser = \Yii::$app->shop->shopFuser;
        $deliveries = \Yii::$app->cache->getOrSet('delivery-types-list', function () {
            return ShopDelivery::find()->where(['active' => Common::BOOL_Y])->indexBy('id')->all();
        },
            HOUR_6
        );

        if ($deliveries) {
            /** @var ShopDelivery $delivery */
            foreach ($deliveries as $delivery) {
                $shippingTypes[] = [
                    'id' => $delivery->id,
                    'name' => $delivery->name,
                    'description' => $delivery->description,
                ];
            }
        }

        $deliveryAddress = DeliveryAddress::getByFuser();

        $pvzData = $fUser->pvz;

        return [
            'method' => $fUser->delivery_id ?? ShopDelivery::ID_DEFAULT,
            'total' => $fUser->deliveryPrice,
            'types' => $shippingTypes ?? [],
            'address' => [
                'city' => $deliveryAddress['city_with_type'],
                'street' => $deliveryAddress['street_with_type'],
                'building' => $deliveryAddress['house'],
                'apartmentNumber' => $deliveryAddress['flat'],
                'daData' => $deliveryAddress['dadata'],
                'pvz' => $deliveryAddress['pvz_id'],
                'pvzMethodId' => null,
                'pvzData' => $pvzData,
            ],
        ];
    }

    public static function getCartPaymentData()
    {
        $fUser = \Yii::$app->shop->shopFuser;
        $paySystems = \Yii::$app->cache->getOrSet('payments-types-list', function () {
            return ShopPaySystem::find()->where(['active' => Common::BOOL_Y])->indexBy('id')->all();
        },
            HOUR_6
        );

        if ($paySystems) {
            /** @var ShopPaySystem $paySystem */
            foreach ($paySystems as $paySystem) {
                $shippingTypes[] = [
                    'id' => $paySystem->id,
                    'name' => $paySystem->name,
                    'description' => $paySystem->description,
                ];
            }
        }

        return [
            'method' => $fUser->pay_system_id ?? ShopPaySystem::ID_DEFAULT,
            //Зарезервированность остатков (можно ли оплачивать картой)
            'cardPaymentAvailable' => false, //TODO Пока не придумано как удобнее сюда передать данные этого параметра заказа в КФСС, так что будем выставлять в другом месте
            'types' => $shippingTypes ?? [],
        ];
    }

    public static function getCartBillingData()
    {
        $productsPrice = \Yii::$app->shop->shopFuser->productsPrice;
        $serviceChargePrice = \Yii::$app->kfssApiV2::SERVICE_CHARGE; //Сервисный сбор (за обработку через кц)
        $deliveryPrice = \Yii::$app->shop->shopFuser->deliveryPrice;
        $totalPrice = $productsPrice + $deliveryPrice + $serviceChargePrice; //Полная сумма которую надо оплатить клиенту
        $discountPromoPrice = \Yii::$app->shop->shopFuser->discountPrice; //Сумма всех АКЦИОННЫХ скидок

        return [
            'tax' => $serviceChargePrice, //Сервисный сбор (за обработку через кц)
            'discountPromo' => $discountPromoPrice,
            'products' => $productsPrice,
            'total' => $totalPrice,
        ];
    }

    public static function getCartItems()
    {
        $fUser = \Yii::$app->shop->shopFuser;

        //Не удаленные товары в корзине
        $baskets = \Yii::$app->shop->shopFuser->getShopBaskets()->orderBy('id ASC')->all();

        $items = [];
        if ($baskets) {
            /** @var ShopBasket $basket */
            foreach ($baskets as $basket) {
                /** @var Product $offer */
                $offer = $basket->product;
                $card = $offer ? Product::findOne($offer->parent_content_element_id) : false;
                $lot = \common\helpers\Product::getLot($basket->main_product_id);

                //* Prices *//

                $regularPrice = ($offer->new_price != $offer->new_price_old && $offer->hasDiscount()) || ($offer->new_price == $offer->new_price_old) ? (string)intval($offer->new_price_old) : '';
                $salePrice = $basket->price;

                //Это не каталог и не карточка, скидка тут плавающая так как зависит от наличия промо/акций и тп
                $discountPercent = 0;
                if ($salePrice > 0 && $regularPrice > 0 && $salePrice < $regularPrice) {
                    $discountPercent = max(0, round((($regularPrice - $salePrice) / $regularPrice) * 100));
                }

                //* /Prices *//

                //* Discount *//
                $discount = $offer->hasDiscount() ? [
                    'type' => 'percent',
                    'amount' => (int)$discountPercent,
                ] : null;
                //* /Discount *//


                $item = [
                    'id' => $basket->id,
                    'name' => $lot ? $lot->name : '',
                    'variationId' => $basket->product_id,
                    'cardId' => $card ? $card->id : '',
                    'productId' => $lot ? $lot->id : '',
                    'sku' => $lot ? $lot->new_lot_num : '',
                    'quantity' => (int)$basket->quantity,
//                    'price' => (int)$basket->price, //цена 1 штуки товара, по которой приобретает клиент, учитывая все скидки, промо и т.п.
                    'regularPrice' => (int)$regularPrice,
                    'salePrice' => (int)$salePrice,
                    'discount' => $discount,
                    'total' => (int)$basket->moneyTotal, //цена за все количество
                    'priceDiscountPromo' => (int)$basket->discount_price, //сумма скидки по акциям
                    'discountName' => $basket->discount_name ?? '', //СТРОКА со списком акций связанных с товаром
                ];

                $variationData = self::getVariation($offer);

                $items[] = ArrayHelper::merge($item, $variationData);
            }
        }

        return $items;
    }

    public static function getCartDiscountData()
    {
        $fUser = \Yii::$app->shop->shopFuser;
        $fUserDiscount = $fUser->ssShopFuserDiscount;

        if ($fUserDiscount && $fUserDiscount->discount_name) {
            $discount = [
                'items' => [
                    $fUserDiscount->discount_name,
                ],
                'total' => (int)$fUserDiscount->discount_price,
            ];
        }

        return  $discount ?? null;
    }

    public static function getCartCouponData()
    {
        $fuser = \Yii::$app->shop->shopFuser;

        $discountCouponData = [
            'code' => null,
            'info' => PromoSchedule::showActual('cart'),
        ];

        if ($discountCoupons = $fuser->discountCoupons){
            $discountCoupon = current($discountCoupons);
            if ($discountCoupon){
                $discountCouponData['code'] = $discountCoupon->coupon;
            }
        }

        return  $discountCouponData ?? null;
    }

    /**
     * @param $model Product
     */
    public static function getVariation($model)
    {
        if ($model->isOffer()) {
            $w = 208;
            $h = 208;

            $card = Product::findOne($model->parent_content_element_id);

            //* Image *//

            $imgId = '';
            $mainImageUrl = Image::getPhotoDefault();

            if ($card->image) {
                $mainImageUrl = \Yii::$app->imaging->thumbnailUrlSS($card->image->src,
                    new Thumbnail([
                        'w' => $w, // 220, // 218
                        'h' => $h, // 220, // 413
                    ]), $model->code
                );
                $imgId = $card->image->id;
            }

            $image = [ //Для модификации берем фотки берем из родителя
                'id' => $imgId,
                'src' => $mainImageUrl,
                'name' => '',
                'alt' => '',
            ];

            //* /Image *//

            //* PricePrime *//
            $pricePrime = Setting::getUsePricePrime() ? (int)($model->new_price && $model->pricePrime < $model->new_price ? $model->pricePrime : '') : '';

            //* Discount *//
            $discount = $model->hasDiscount() ? [
                'type' => 'percent',
                'amount' => (string)intval($model->new_discount_percent),
            ] : null;
            //* /Discount *//

            //* ATTRIBUTES *//

            $attrs = [];

            //* Colors *//

            $lotColors = Product::getLotColors($model->id, false);

            if (!empty($lotColors[$card->id])) {
                $cardColor = $lotColors[$card->id];
                $attrs[] = [
                    'id' => 1,
                    'name' => 'Цвет',
                    'slug' => 'color',
                    'option' => [
                        'name' => Html::encode($cardColor['name']),
                        'id' => (int)$cardColor['id'],
                    ],
                ];
            }

            //* /Colors *//

            //* Sizes *//

            $offerSizes = Product::getSizesFromProps($model->id);

            if ($offerSizes) {
                foreach ($offerSizes as $size) {
                    $attrs[] = [
                        'id' => (int)$size['property_id'],
                        'name' => 'Размер',
//                            'alias' => $size['name'],
                        'slug' => 'size',
                        'option' => [
                            'name' => $size['name'],
                            'id' => (int)$size['id'],
                        ]
                    ];
                }
            }

            //* /Sizes *//

            //* /ATTRIBUTES *//

            $result = [
//                'id' => $model->id,
//                'name' => $model->name,
                'image' => $image,
                'permalink' => $model->url, //ссылка на карточку,
                'stockQuantity' => $model->new_quantity,
                'stockStatus' => $model->new_quantity > 0 ? 'instock' : 'outofstock',
//                '_price' => [
//                    'current' => (string)intval($model->new_price),
//                    'old' => $model->hasDiscount() ? (string)intval($model->new_price_old) : null,
//                ],
                'priceTypeId' => 'new_price_active',
                'priceLabel' => Price::getPriceLabel($model->new_price_active) ?: 'Цена со скидкой',
//                'price' => (string)intval($model->new_price),
                //Перенесено в getCartItems()
//                'regular_price' => ($model->new_price != $model->new_price_old && $model->hasDiscount()) || ($model->new_price == $model->new_price_old) ? (string)intval($model->new_price_old) : '',
//                'sale_price' => $model->new_price != $model->new_price_old ? (string)intval($model->new_price) : '',
//                'discount' => $discount,
                'primePrice' => $pricePrime,
//                'badge' => function () {
//                    return null;
//                },
                'attributes' => $attrs
            ];
        }

        return $result ?? false;
    }
}