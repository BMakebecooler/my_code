<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 01/03/2019
 * Time: 11:24
 */

namespace console\controllers;


use modules\shopandshow\models\monitoringday\Plan;
use yii\console\Controller;

class NotifyController extends Controller
{


    public function actionSendPlanFact()
    {


        $plan = new Plan();
        $dataProvider = $plan->getDataProvider();

        $plan = 0;
        $fact = 0;
        $needFact = 0;
        $count = 0;
        foreach ($dataProvider->getModels() as $model) {
            if (!empty($model['sum_fact'])) {
                $needFact += $model['sum_plan'];
            }
            $plan += $model['sum_plan'];
            $fact += $model['sum_fact'];
            $count += $model['amount'];
        }

        $message = '<b>План/Факт на ' . date('Y-m-d') . '</b>' . PHP_EOL;
        $message .= 'Показатели на <i>' . date("H:i") . '</i>' . PHP_EOL;
        $message .= PHP_EOL;
        $message .= 'Дневной план <i>' . $plan . '</i>' . PHP_EOL;
        $message .= 'Факт <i>' . $fact . '</i>' . PHP_EOL;
        $message .= 'План выполнен на <i>' . round($fact / $plan * 100) . '%</i>' . PHP_EOL;
        $message .= PHP_EOL;
        $message .= 'Необходимый показатель для выполнения плана <i>' . round($needFact / $plan * 100) . '%</i>' . PHP_EOL;
        $message .= 'Необходимый доход <i>' . $needFact . '</i>' . PHP_EOL;
        $message .= PHP_EOL;
        $message .= 'Заказов <i>' . $count . '</i>' . PHP_EOL;


        if (round($fact / $plan * 100) > 100) {
            $message .= '<b>План выполняетсяю</b>' . PHP_EOL;
        } else {
            $message .= '<b>План не выполняется</b>' . PHP_EOL;
        }

        $message .= 'подробнее по ссылке  (<a href="https://shopandshow.ru/~sx/shopandshow/monitoringday/plan/show">https://shopandshow.ru/~sx/shopandshow/monitoringday/plan/show</a>)';


        \Yii::$app->telegramBotEcom->sendMessage(-1001238268476, $message, 'html');
    }
}