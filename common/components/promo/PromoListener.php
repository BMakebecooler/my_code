<?php

namespace common\components\promo;

use common\helpers\User;
use modules\shopandshow\models\shares\SsShare;
use yii\base\BootstrapInterface;
use yii\base\Component;
use yii\web\Application;

/**
 * Специальный слушатель для промо кампаний
 * Class PromoListener
 * @package common\components\promo
 */
class PromoListener extends Component implements BootstrapInterface
{

    public function bootstrap($application)
    {
        if ($application instanceof Application) {
            \Yii::$app->on(Application::EVENT_AFTER_ACTION, function ($e) {
                $this->recountBannerClick();
            });
        }
    }

    /**
     * Подсчет кликов по баннерам
     */
    private function recountBannerClick()
    {

        $isSimpleUser = User::isGuest() || !User::isDeveloper();
        $isUtmSourceEmail = isset($_GET['utm_source']) && $_GET['utm_source'] === 'email';
        $bannerId = \Yii::$app->request->get('bid');

        //[DEPRECATED] Считаем клики только от гостей или простых пользователей
        //Учет кликов переведен на учет непосредственно клика по баннеру
        if (false && $bannerId && $isSimpleUser && !$isUtmSourceEmail) {
            SsShare::updateAllCounters(['count_click' => 1], 'id = :id', [':id' => $bannerId]);
        }

        //Считаем клики только от гостей или простых пользователей при переходе из рассылки
        if ($bannerId && $isSimpleUser && $isUtmSourceEmail) {
            SsShare::updateAllCounters(['count_click_email' => 1], 'id = :id', [':id' => $bannerId]);
        }
    }

}
