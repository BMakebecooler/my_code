<?php
/**
 * php yii promo/reset-count-views-day
 *
 */


namespace console\controllers;

use yii\console\Controller;
use common\helpers\Promo;
use common\models\Promo as PromoModel;

class PromoController extends Controller
{
    /**
     * Сбросить счетчик просмотров в сутки всем сборкам
     */
    public function actionResetCountViewsDay()
    {
        Promo::resetCountViewsDay();
    }
}