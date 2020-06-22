<?php

namespace console\controllers\queues\jobs\shop;

use console\controllers\queues\jobs\Job;
use modules\shopandshow\models\newEntities\shop\Coupon as CouponModel;

class Coupons extends Job
{
    /**
     * @param \yii\queue\Queue $queue
     * @param string $guid
     *
     * @return bool
     * @throws \Exception
     */
    public function execute($queue, &$guid)
    {
        if ($this->prepareData($queue)) {
            $guid = null;

            return $this->addCoupon();
        }

        return false;
    }

    /**
     * @return bool
     */
    protected function addCoupon()
    {
        $info = $this->data['Info'];
        $data = $this->data['Data'];

        if ($info['Source'] == 'SITE') {
            return true;
        }

        Job::dump('----- Coupon -------');
        Job::dump('PromoCode: '.$data['PromoCode']);
        Job::dump('Email: '.$data['Email']);

        $coupon = new CouponModel();

        $coupon->promocode_type = $data['PromoCodeType'];
        $coupon->promocode = $data['PromoCode'];
        $coupon->is_active = (int)$data['IsActive'];
        $coupon->error_text = $data['ErrorText'];
        $coupon->email = $data['Email'];
        $coupon->active_from = $data['DateStart'] ? strtotime($data['DateStart']) : time();
        $coupon->active_to = $data['DateEnd'] ? strtotime($data['DateEnd']) : null;

        return $coupon->addData();
    }
}