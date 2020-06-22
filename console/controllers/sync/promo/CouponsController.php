<?php

/**
 * php ./yii sync/promo/coupons
 */

namespace console\controllers\sync\promo;

use common\helpers\Msg;
use console\controllers\sync\SyncController;
use modules\shopandshow\components\task\SendCoupons500rTaskHandler;
use modules\shopandshow\models\shop\ShopDiscount;
use modules\shopandshow\models\task\SsTask;
use yii\helpers\Console;

/**
 * Class CouponsController
 *
 * @package console\controllers
 */
class CouponsController extends SyncController
{

    public function actionIndex()
    {

        $this->actionSubscriptionCoupons();

    }

    public function actionSubscriptionCoupons()
    {

        $this->stdout("Sync personal coupons for subscription to newsletter (500RUB)\n", Console::FG_CYAN);

        $discount = ShopDiscount::find()
            ->andWhere('code=:CODE', [':CODE' => ShopDiscount::DISCOUNT_CODE_500RUB])
            ->one();

        if ($discount == null) {

            $this->stdout("Promo with code 500RUB not found\n", Console::FG_RED);
            return false;

        }

        //Выберем данные по купонам которые необходимо будет добавлять
        $queryGetSandsSubscribeCoupons = "
            select
                c.uf_create_date as created_at,
                unix_timestamp() as updated_at,
                {$discount->id} as shop_discount_id,
                c.uf_active as is_active,
                c.uf_coupon as coupon,
                c.uf_email as email,
                1 as max_use,
                u.id as cms_user_id
            from front2.sands_subscribe_coupons c
            left join cms_user u ON u.bitrix_id=c.uf_user_id
            left join shop_discount_coupon sdc ON sdc.coupon=c.uf_coupon
            where sdc.id is null";

        $coupons = \Yii::$app->db->createCommand($queryGetSandsSubscribeCoupons)->queryAll();

        if (!$coupons) {
            $this->stdout("No coupons", Console::FG_YELLOW);
            return false;
        }

        $this->stdout("Updating coupons ", Console::FG_YELLOW);

        $insertTransaction = \Yii::$app->db->beginTransaction();

        try {

            $affected = 0;

            //Массив для хранения информации для дальнейшей уведомительной рассылки о факте создания купона
            $couponsNotifications = array();

            if ($coupons) {
                foreach ($coupons as $coupon) {
                    $couponEmail = $coupon['email'];
                    //При вставке почта не нужна
                    unset($coupon['email']);
                    $couponSaved = \Yii::$app->db->createCommand()->insert('shop_discount_coupon', $coupon)->execute();
                    if ($couponSaved) {
                        $couponsNotifications[$coupon['coupon']] = $couponEmail;
                    }
                }

                $affected = count($couponsNotifications);
            }

            $this->stdout(" done. Affected " . $affected . "\n", Console::FG_GREEN);

            $insertTransaction->commit();

            //Отправляем уведомления в очередь тасков на отправку
            if ($couponsNotifications) {

                $validator = new \yii\validators\EmailValidator();
                foreach ($couponsNotifications as $coupon => $email) {
                    if ($validator->validate($email) && $coupon) {

                        $taskResult = SsTask::createNewTask(
                            SendCoupons500rTaskHandler::className(),
                            ['email' => $email, 'coupon' => $coupon]
                        );

                        if (!$taskResult) {
                            $this->stdout("Не удалось создать задание отправки купона на E-mail {$email}", Console::FG_RED);
                        }
                    }
                }
            }

        } catch (\yii\db\Exception $e) {

            $this->logError($e);

            \Yii::error('Купоны не импортировались!');

            $insertTransaction->rollBack();
            return false;

        }


    }

    public function actionPromoCoupons()
    {

        $this->stdout("Sync Promo coupons\n", Console::FG_CYAN);


    }

}