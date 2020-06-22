<?php

/**
 * php ./yii sync/promo/promo
 */

namespace console\controllers\sync\promo;

use console\controllers\sync\SyncController;
use console\models\sync\promo\PromoManager;
use yii\helpers\Console;

class PromoController extends SyncController
{
    /**
     * Старт синхронизации
     */
    public function actionIndex()
    {

        $this->actionSyncPromo();

    }

    /**
     * Синхронизирует акции из bo
     */
    public function actionSyncPromo()
    {

        $this->stdout("Sync promo discounts\n", Console::FG_CYAN);

        $promoManager = new PromoManager($this);
        $promoManager->processPromoactions();
    }
}