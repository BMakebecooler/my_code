<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 01.03.19
 * Time: 14:08
 */
namespace console\controllers\coupons;

use console\controllers\mq\CouponsController;
use modules\shopandshow\models\newEntities\shop\Coupon;
use modules\shopandshow\models\task\SsTask;
use skeeks\cms\models\CmsUserEmail;
use yii\console\Controller;

class CouponController extends Controller
{
    public function actionSendCoupon($email)
    {
        $coupon = Coupon::findOne(['description' => $email]);

        if(!empty($coupon)) {
            if($coupon->use_count != 0) {
                $this->stdout('Coupon is used.' . PHP_EOL);
            } else {
                SsTask::createNewTask(
                    'modules\shopandshow\components\task\SendCoupons500rTaskHandler',
                    ['email' => $email, 'coupon' => $coupon->coupon]);
                $this->stdout('Coupon '. $coupon->coupon .' add to task on sending to email : '. $email . PHP_EOL);
            }
        }else{
            $this->stdout('Coupon for email '. $email .' don`t found.' . PHP_EOL);
            $emailUser = CmsUserEmail::findOne(['value' => $email]);
            if(!empty($emailUser)) {
                $coupon = Coupon::findOne(['use_count' => 0, 'description' => NULL]);
                if(!empty($coupon)) {
                    SsTask::createNewTask(
                        'modules\shopandshow\components\task\SendCoupons500rTaskHandler',
                        ['email' => $email, 'coupon' => $coupon->coupon]);
                    $this->stdout('Coupon '. $coupon->coupon .' add to task on sending to email : '. $email . PHP_EOL);
                    $coupon->description = $email;
                    $coupon->save();
                    $this->stdout('Save coupon '. $coupon->coupon .' link to email : '. $email . PHP_EOL);
                    if($emailUser->is_send_coupon_500r == 'N') {
                        //TODO Add send request for a new coupon to KFSS
                    }
                } else {
                    $this->stdout('Coupon don`t found.' . PHP_EOL);
                }
            } else {
                $this->stdout('User email '. $email .' don`t found.' . PHP_EOL);
            }
        }
    }
}