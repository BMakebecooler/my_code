<?php

/**
 * php ./yii imports/coupons/disabled
 * php ./yii imports/coupons/enabled
 */

namespace console\controllers\imports;

use modules\shopandshow\models\shop\ShopDiscountCoupon;
use yii\helpers\Console;

/**
 * Class CouponsController
 * @package console\controllers
 */
class CouponsController extends \yii\console\Controller
{

    const DISABLED = 0;
    const ENABLED = 1;

    public function init()
    {
        parent::init();
    }

    /**
     * Отключить купоны
     */
    public function actionDisabled()
    {
        $files = [__DIR__ . '/files/coupons.csv',];
//        $files = [];

        $this->importFromFiles($files);

        $this->stdout("Купоны отключены!\n", Console::FG_GREEN);

        $this->stdout("Отключение купонов из файла закончен!\n", Console::FG_YELLOW);
    }

    /**
     * Включить купоны
     */
    public function actionEnabled()
    {
        $files = [__DIR__ . '/files/coupons.csv',];
//        $files = [];

        $this->importFromFiles($files, self::ENABLED);

        $this->stdout("Купоны активированы!\n", Console::FG_GREEN);

        $this->stdout("Включение купонов из файла закончен!\n", Console::FG_YELLOW);
    }

    /**
     * @param array $files
     * @param int $type
     */
    protected function importFromFiles(array $files, $type = self::DISABLED)
    {
        foreach ($files as $file) {
            $this->stdout("Начинаем сбор данных из файла: $file\n", Console::FG_YELLOW);

            if (!file_exists($file)) {
                $this->stdout("Файл '$file' не найден\n", Console::FG_RED);

                continue;
            }

            $rows = file($file);

            foreach ($rows as $row) {
                if (empty($row)) {
                    continue;
                }

                $coupon = trim($row);
                $coupon = ShopDiscountCoupon::findOne(['coupon' => $coupon]);

                // активность меняем, только если купон еще не юзали
                if ($coupon && $coupon->use_count == 0) {

                    $coupon->is_active = $type;
                    // todo: разовая операция активации купонов, при следующем импорте поставить верные даты
                    $coupon->active_from = strtotime('2018-04-01 00:00:00');
                    $coupon->active_to = strtotime('2018-04-30 23:59:59');

                    $coupon->save(false, [
                        'is_active', 'active_from', 'active_to'
                    ]);
                }
            }
        }
    }
}