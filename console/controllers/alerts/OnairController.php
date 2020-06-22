<?php

/**
 * php ./yii alerts/onair/
 */

namespace console\controllers\alerts;

use common\components\sms\Sms;
use common\helpers\Msg;
use console\controllers\export\ExportController;
use modules\shopandshow\lists\Products;
use yii\helpers\Console;

/**
 * Class OnairController
 * @package console\controllers
 */
class OnairController extends ExportController
{

    public function actionIndex()
    {

        $hour = (int)date('G');

        if ($hour >= 8 && $hour < 22) {
            $this->checkOnairProduct();
        }
    }

    /**
     * Проверка на то что приходит товар в эфире
     * @return bool
     */
    private function checkOnairProduct()
    {
        $hour = date('Y-m-dH', strtotime('- 3 hour')); // 2018-10-2517 (-3 из за неверного ЧП в слушателеле эфиров (nodejs))

        $text = 'Проблема с товаром в эфире!';

        $result = \Yii::$app->redis->get('onair_product_time_' . $hour);

        if (!$result && !Products::getOnairProductId()) {
            \Yii::error($text);
            $this->stdout("$text\n", Console::FG_RED);

            return false;
        }

        $text = 'С товаром в эфире проблем нет!';

        $this->stdout("$text\n", Console::FG_GREEN);

        return true;
    }
}
