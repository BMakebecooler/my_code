<?php


namespace console\jobs;

use yii\base\Exception;

class RetailRocketImportEmailsJob extends \yii\base\Object implements \yii\queue\Job
{
    /**
     * обработать список емейлов из ретейл рокет
     * @throws yii\base\Exception|Exception
     *
     */
    public function execute($queue)
    {
        //Добавить запись о старте импорта юзеров емейл из csv файла
        \Yii::error('Start import user emails from csv ', __METHOD__);

        //todo динамическая догрузка компонента в консольный апликейшин
        \Yii::$app->set('retailRocketService', [
            'class' => 'common\components\email\services\RetailRocket',
        ]);
        \Yii::$app->retailRocketService->loadEmailsFromCSV();
    }
}