<?php

namespace console\controllers\mq;

use common\helpers\Msg;
use modules\shopandshow\components\amqp\SSMessageBus;
use modules\shopandshow\components\task\SendCoupons500rTaskHandler;
use modules\shopandshow\models\shop\ShopDiscount;
use modules\shopandshow\models\shop\ShopDiscountCoupon;
use modules\shopandshow\models\task\SsTask;
use yii\base\Exception;

/**
 * php ./yii mq/coupons/coupons
 */

/**
 * Class CouponsController
 *
 * @package console\controllers\mq
 */
class CouponsController extends ListenerController
{

    public $queueName = 'front.coupons';
    public $routingKey = 'front.coupons';

    /**
     * Для того чтобы слушать кффсс
     */
    public function actionCoupons()
    {

        /** @var SSMessageBus $queue */
        $queue = clone \Yii::$app->frontExchange;
        $queue->queueName = $this->queueName;
        $queue->routingKey = 'KFSS.PROMO';
        $queue->exchangeName = 'PromoExchange';
        $queue->vhost = '/';

        $queue->messageHandler = function ($id, $message) {

            $this->log("Incoming message. ID: " . $id);
            $this->log("Message body:");
            $this->log($message);

            try {
                $this->parseMessageKfss($message);
            } catch (Exception $e) {
                $this->log("Message decode error");
                $this->log("Exception {$e->getMessage()}");
                var_dump($e->getMessage());
            }

            try {

                if (call_user_func([$this, $this->method]))
                    return true;
                else
                    return false;

            } catch (\Exception $e) {

                $this->log("Message processing error");
                $this->log("Exception: {$e->getMessage()}");

                return false;

            }

        };

        $queue->listen();
    }

    protected function createCoupon()
    {
        static $promoCodeTypes = [
            1 => ShopDiscount::DISCOUNT_CODE_500RUB,
        ];

        // купон с ошибкой
        if ($this->data['ErrorText']) {
            // На указанный e-mail уже был выслан промо-код
            //\Yii::error(print_r($this->data, true));

            return true;
        }

        $promoCodeType = $this->data['PromoCodeType'];
        if (!array_key_exists($promoCodeType, $promoCodeTypes)) {
            throw new Exception("PromoCodeType {$promoCodeType} not supported");
        }
        $discountCode = $promoCodeTypes[$promoCodeType];

        $discount = ShopDiscount::find()
            ->andWhere('code=:CODE', [':CODE' => $discountCode])
            ->one();

        if ($discount == null) {
            throw new Exception("ShopDiscount with code {$discountCode} not found");
        }

        $couponExists = ShopDiscountCoupon::find()
            ->where(['shop_discount_id' => $discount->id])
            ->andWhere(['coupon' => $this->data['PromoCode']])
            ->andWhere(['is_active' => 1])
            ->count();

        if ($couponExists) {
            return true;
        }

        $coupon = new ShopDiscountCoupon();

        $coupon->shop_discount_id = $discount->id;
        $coupon->coupon = $this->data['PromoCode'];
        $coupon->max_use = 1;
        $coupon->is_active = (int)$this->data['IsActive'];
        $coupon->description = $this->data['Email'];
        $coupon->active_from = strtotime($this->data['DateStart']);
        $coupon->active_to = strtotime($this->data['DateEnd']);

        if (!$coupon->validate()) {
            \Yii::error(print_r($coupon->getErrors(), true));

            return false;
//            throw new Exception("ShopDiscountCoupon validation error: ".json_encode($coupon->getErrors()));
        }

        if (!$coupon->save()) {
            $this->log("Coupon {$this->data['PromoCode']} create failed!");
            $this->log("Errors: " . json_encode($coupon->getErrors()));

            \Yii::error(print_r($coupon->getErrors(), true));

            return false;
        }

        $this->log("Coupon {$this->data['PromoCode']} created!");

        if ($discountCode == ShopDiscount::DISCOUNT_CODE_500RUB) {
            $this->send500RubEmailCoupon();
        }

        return true;
    }

    protected function send500RubEmailCoupon()
    {

        $email = $this->data['Email'];
        $coupon = $this->data['PromoCode'];

        $validator = new \yii\validators\EmailValidator();

        if ($validator->validate($email) && $coupon) {

            $taskResult = SsTask::createNewTask(
                SendCoupons500rTaskHandler::className(),
                ['email' => $email, 'coupon' => $coupon]
            );

            if (!$taskResult) {
                //$this->stdout("Не удалось создать задание отправки купона на E-mail {$email}", Console::FG_RED);

                $this->log("Не удалось создать задание отправки купона на E-mail {$email}");

                \Yii::error("Не удалось создать задание отправки купона на E-mail {$email}" . PHP_EOL . print_r($taskResult, true));
            }
        }
        else {
            var_dump('failed to validate email');
        }

    }

}