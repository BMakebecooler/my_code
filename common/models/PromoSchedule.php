<?php


namespace common\models;


use common\models\query\PromoScheduleQuery;
use yii\helpers\Html;

class PromoSchedule extends \common\models\generated\models\PromoSchedule
{
    public function behaviors()
    {
        return [
            'timestamp' => \yii\behaviors\TimestampBehavior::class,
        ];
    }

    public static function find()
    {
        return new PromoScheduleQuery(get_called_class());
    }

    public static function getActual()
    {
        return self::getDb()->cache(function () {
            return self::find()
                ->actual()
                ->orderBy([
                    'date_from' => SORT_ASC,
                    'id' => SORT_ASC
                ])
                ->all();
        },
            MIN_5);
    }

    public static function getMainCoupon()
    {
        if ($coupons = self::getActual()) {
            $couponsMain = array_filter($coupons, function ($coupon) {
                /** @var $coupon self */
                return (bool)$coupon->is_main;
            });

            $mainCouponSrc = $couponsMain ? current($couponsMain) : current($coupons);
        }

        return !empty($mainCouponSrc->coupon) ? $mainCouponSrc->coupon : '';
    }

    //$type - для какого места сайта нужно вернуть контент (карточка, корзина)
    public static function showActual($type)
    {
        $coupons = self::getActual();

        if ($coupons) {
            $couponsUsesNum = [];
            $couponsForGrouping = [];
            //Группируем купоны что бы понять есть ли группировки которые необходимо учитывать
            /** @var PromoSchedule $coupon */
            foreach ($coupons as $coupon) {
                if ($couponCode = trim($coupon->coupon)) {
                    $couponsUsesNum[$couponCode] = isset($couponsUsesNum[$couponCode]) ? $couponsUsesNum[$couponCode] + 1 : 1;

                    //Записываем купоны встречающиеся больше 1 раза
                    if ($couponsUsesNum[$couponCode] > 1 && !in_array($couponCode, $couponsForGrouping)) {
                        $couponsForGrouping[] = $couponCode;
                    }
                }
            }

            $couponsHeader = '';
            $couponsList = '';

            //* Coupons Header *//

            if ($couponsForGrouping) {
                $couponsHeader = 'Промокод ' . implode(', ', $couponsForGrouping) . '<br>';
            }

            //* /Coupons Header *//

            /** @var PromoSchedule $coupon */
            foreach ($coupons as $coupon) {
                $couponCode = trim($coupon->coupon);

                $couponCodeInfo = '';
                $discountPercentInfo = '';
                $discountOnInfo = '';

                if ($couponCode && !in_array($couponCode, $couponsForGrouping)) {
                    $couponCodeInfo = 'промокод ' . Html::encode($couponCode);
                }

                if ($coupon->discount_percent) {
                    $discountPercentInfo = ($couponCodeInfo ? ' - ' : '') . 'дополнительная скидка ' . Html::encode($coupon->discount_percent) . '%';
                }

                if ($coupon->discount_on_text) {
                    $discountOnText = Html::encode($coupon->discount_on_text);

                    if ($coupon->url) {
                        $url = Html::encode($coupon->url);
                        $linkClass = '';
                        $linkStyle = '';

                        //для карточки и корзины есть отличия
                        switch ($type) {
                            case 'product':
                                $linkClass = 'text-paprika';
                                $linkStyle = 'text-decoration: underline;';
                                break;
                            case 'cart':
                                $linkClass = 'nowrap';
                                $linkStyle = '';
                                break;
                        }

                        $discountOnInfo = " на <a class='{$linkClass}' href='{$url}' style='{$linkStyle}'>{$discountOnText}</a>";
                    } else {
                        $discountOnInfo = " на {$discountOnText}";
                    }
                }

                $couponsList .= $couponCodeInfo . $discountPercentInfo . $discountOnInfo . ';<br>';
            }

            return self::getPrefix($type) . $couponsHeader . $couponsList . self::getSufix($type);
        }

        return '';
    }

    public static function getPrefix($type)
    {
        switch ($type) {
            case 'product':
            case 'cart':
                $result = 'Только сегодня! Используйте промокод и получите скидку<br><br>';
                break;
        }
        return $result ?? '';
    }

    public static function getSufix($type)
    {
        switch ($type) {
            case 'product':
            case 'cart':
                $result = '* при сумме заказа от 3990 руб.';
                break;
        }
        return $result ?? '';
    }
}