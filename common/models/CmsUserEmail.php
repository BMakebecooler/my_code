<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 2019-06-10
 * Time: 14:13
 */

namespace common\models;


class CmsUserEmail extends \common\models\generated\models\CmsUserEmail
{

    const VALUE_TYPE_EMAIL = 0;
    const VALUE_TYPE_PHONE = 1;

    public static function isApprovedRREmail($email)
    {
        $model = self::find()
            ->andWhere(['value' => $email])
            ->onlyApprovedRR()
            ->onlyEmail()
            ->one();

        return $model ?? false;

    }
}