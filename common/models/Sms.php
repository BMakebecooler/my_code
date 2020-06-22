<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 2019-05-24
 * Time: 11:07
 */

namespace common\models;


use common\helpers\Strings;

class Sms extends \common\models\generated\models\Sms
{
    const MAX_TIME_LIMIT_REQUEST = 60;

    public static function canRequest($phone, $fUserId = null)
    {
        $phone = Strings::getPhoneClean($phone);

        $sms = self::find()
            ->andWhere('phone = :phone', [':phone' => $phone])
//            ->andWhere('fuser_id = :fuser_id', [':fuser_id' => $fUserId])
//            ->andWhere('provider = :provider', [':provider' => $this->currentServiceName])
//            ->andWhere('ip = :ip', [':ip' => ])
            ->andWhere('created_at >= :created_at', [
                ':created_at' => date('Y-m-d H:i:s', (time() - self::MAX_TIME_LIMIT_REQUEST)),
            ])
            ->limit(1)
            ->one();

        return !$sms;
    }

}