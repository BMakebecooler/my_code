<?php
/**
 *php yii user-email/r-r-load-emails-from-c-s-v
 */


namespace console\controllers;


use yii\base\Exception;
use yii\console\Controller;


class UserEmailController extends Controller
{
    /**
     *
     * обработать список емейлов из ретейл рокет
     * @throws yii\base\Exception|Exception
     *
     */
    public function actionRRLoadEmailsFromCSV()
    {
        //todo динамическая догрузка компонента в консольный апликейшин
        \Yii::$app->set('retailRocketService', [
            'class' => 'common\components\email\services\RetailRocket',
        ]);

        \Yii::$app->retailRocketService->loadEmailsFromCSV();

    }
}