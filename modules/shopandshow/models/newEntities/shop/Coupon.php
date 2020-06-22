<?php

namespace modules\shopandshow\models\newEntities\shop;

use common\helpers\Msg;
use console\controllers\queues\jobs\Job;
use modules\shopandshow\components\task\SendCoupons500rTaskHandler;
use modules\shopandshow\models\shop\ShopDiscount;
use modules\shopandshow\models\shop\ShopDiscountCoupon;
use modules\shopandshow\models\task\SsTask;

class Coupon extends ShopDiscountCoupon
{
    public $promocode_type;
    public $promocode;
    public $error_text;
    public $email;

    public function addData()
    {
        static $promoCodeTypes = [
            1 => ShopDiscount::DISCOUNT_CODE_500RUB,
        ];

        // купон с ошибкой
        if ($this->error_text) {
            Job::dump("{$this->promocode} {$this->email} {$this->error_text}");

            return true;
        }

        if (!array_key_exists($this->promocode_type, $promoCodeTypes)) {
            Job::dump("PromoCodeType {$this->promocode_type} not supported");

            return true;
        }
        $discountCode = $promoCodeTypes[$this->promocode_type];

        $discount = ShopDiscount::find()
            ->andWhere('code=:CODE', [':CODE' => $discountCode])
            ->one();

        if ($discount == null) {
            Job::dump("ShopDiscount with code {$discountCode} not found");

            return false;
        }

        $coupon = ShopDiscountCoupon::find()
            ->where(['shop_discount_id' => $discount->id])
            ->andWhere(['coupon' => $this->promocode])
            ->one();

        if ($coupon) {
            if ($coupon->is_active != $this->is_active) {
                $coupon->is_active = $this->is_active;
                $coupon->active_from = $this->active_from;
                $coupon->active_to = $this->active_to;
                $coupon->save(false, ['is_active']);
            }
            return true;
        }

        $coupon = new ShopDiscountCoupon();

        $coupon->shop_discount_id = $discount->id;
        $coupon->coupon = $this->promocode;
        $coupon->max_use = 1;
        $coupon->is_active = $this->is_active;
        $coupon->description = $this->email;
        $coupon->active_from = $this->active_from;
        $coupon->active_to = $this->active_to;

        if (!$coupon->save()) {
            Job::dump("Coupon {$this->promocode} create failed!");
            Job::dump("Errors: " . print_r($coupon->getErrors(), true));

            return false;
        }

        if ($discountCode == ShopDiscount::DISCOUNT_CODE_500RUB) {
            $this->send500RubEmailCoupon();
        }

        return true;
    }

    protected function send500RubEmailCoupon()
    {

        $email = $this->email;
        $coupon = $this->promocode;

        $validator = new \yii\validators\EmailValidator();

        if ($validator->validate($email) && $coupon) {

            $taskResult = SsTask::createNewTask(
                SendCoupons500rTaskHandler::className(),
                ['email' => $email, 'coupon' => $coupon]
            );

            if (!$taskResult) {
                Job::dump("Не удалось создать задание отправки купона на E-mail {$email}" . PHP_EOL . print_r($taskResult, true));
                \Yii::error("Не удалось создать задание отправки купона на E-mail {$email}" . PHP_EOL . print_r($taskResult, true));
            }
        }
        else {
            Job::dump('failed to validate email or empty coupon');
        }

    }
}