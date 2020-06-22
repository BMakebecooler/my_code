<?php

/**
 * php ./yii tools/users/import-email-from-site
 * php ./yii tools/users/import-email-from-phone
 * php ./yii tools/users/generate-users phoneNumBegin phonesNum //8000010001 2000
 * php ./yii tools/users/export-for-sync [N] //N - limit
 * php ./yii tools/users/fix-phones [N] //N - limit
 * php ./yii tools/users/sync-kfss [N] [M] //N - limit | M - offset
 */

namespace console\controllers\tools;

use common\components\rbac\CmsManager;
use common\helpers\Strings;
use common\helpers\User;
use common\models\generated\models\CmsUserProperty;
use common\models\Guid;
use console\controllers\export\ExportController;
use modules\shopandshow\models\users\UserEmail;
use skeeks\cms\components\Cms;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

/**
 * Class UsersController
 * @package console\controllers
 */
class UsersController extends ExportController
{


    /**
     * Оставленные емайлы при регитрации на сайте
     * @return int
     */
    public function actionImportEmailFromSite()
    {
        $sql = <<<SQL
SELECT
  user.id AS user_id, 
  user.email AS email,
  user.created_at AS created_at,
  user.updated_at AS updated_at,
  'site' AS source,
  'register' AS source_detail
FROM cms_user AS user
WHERE user.email NOT REGEXP '^newsite_|(.*).newsite.ru|@no.reg'
SQL;

        $insertSql = <<<SQL
INSERT IGNORE INTO cms_user_email (user_id, value, created_at, updated_at, source, source_detail)
SQL;

        $result = \Yii::$app->db->createCommand($insertSql . $sql)->execute();

        $this->stdout(" actionImportEmailFromSite {$result} add base \n", Console::FG_GREEN);

        return $result;
    }


    /**
     * Оставленные емайлы оператору по телефону
     * @return int
     */
    public function actionImportEmailFromPhone()
    {

        $emailsAddedNum = 0;

        $sourceAirPhone = UserEmail::SOURCE_PHONE;
        $sourceSitePhone = UserEmail::SOURCE_SITE_PHONE;
        $sourceDetailCheckOrder = UserEmail::SOURCE_DETAIL_CHECK_ORDER;

        //Вычитаем интервал равный периоду запуска агента что бы не потерять данные за этот период при смене дней
        $sql = <<<SQL
SELECT 
  prop.value AS email,
  UNIX_TIMESTAMP(ord.DATE_INSERT) AS created_at,
  UNIX_TIMESTAMP(ord.DATE_UPDATE) AS updated_at,
  IF(prop_source.VALUE = 'NEW_SITE', '$sourceSitePhone', '$sourceAirPhone') AS source,
  '$sourceDetailCheckOrder' AS source_detail
FROM front2.b_sale_order_props_value AS prop 
INNER JOIN front2.b_sale_order AS ord ON ord.id = prop.order_id
LEFT JOIN front2.b_sale_order_props_value AS prop_source ON prop_source.ORDER_PROPS_ID=12 AND prop_source.ORDER_ID=prop.ORDER_ID
WHERE 
  prop.order_props_id = 2 
  AND ord.DATE_INSERT >= CURDATE() - INTERVAL 90 MINUTE 
  AND length(prop.value) > 7 
  AND prop.value NOT REGEXP '^newsite_|(.*).newsite.ru|@no.reg'
SQL;
        $emails = \Yii::$app->db->createCommand($sql)->queryAll();

        if ($emails) {
            foreach ($emails as $emailData) {
                $email = $emailData['email'];
                $userEmail = UserEmail::findOne(['value' => $email]);

                if (!$userEmail) {
                    //в доп параметрах оно не нуно
                    unset($emailData['email']);

                    $addResult = UserEmail::addToBase($email, $emailData);

                    if (!$addResult) {
                        $this->stdout("Ошибка при попытке добавления '{$email}' в базу email'ов");
                    } else {
                        $emailsAddedNum++;
                    }
                } else {
                    $this->stdout("Email '{$email}' уже есть в базе!", Console::FG_CYAN);
                }
            }
        }

        $this->stdout(" actionImportEmailFromPhone {$emailsAddedNum} add base \n", Console::FG_GREEN);

        return $emailsAddedNum;
    }

    public function actionGenerateUsers($phoneNumBegin = 8000010001, $phonesNum = 1, $isDemo = true)
    {
        $this->stdout("Генерирую {$phonesNum} пользователей по номерам телефонов начиная с '{$phoneNumBegin}'" . PHP_EOL, Console::FG_CYAN);

        $phones = \common\helpers\Common::generatePhones($phoneNumBegin, $phonesNum);

        if ($phones) {
            if (!\Yii::$app->has('user', false)) {
                \Yii::$app->set('user', [
                    'class' => 'yii\web\User',
                    'identityClass' => 'common\models\user\User'
                ]);
            }

            $roleDemo = \Yii::$app->authManager->getRole(CmsManager::ROLE_DEMO);

            $count = count($phones);
            $counterStep = $count / 100; //каждый 1 процента, сколько это в штуках

            $counterGlobal = 0;
            $counter = 0;
            Console::startProgress(0, $count);
            foreach ($phones as $phone) {
                $counterGlobal++;
                $counter++;

                if ($counter >= $counterStep || $counterGlobal == $count) {
                    $counter = 0;
                    Console::updateProgress($counterGlobal, $count);
                }

                //Проверяем на дубли
                $phoneExists = false;
                $user = \common\lists\Users::findByPhone($phone);
                if ($user) {
                    $phoneExists = true;
                }

                if (!$phoneExists) {
                    $registration = new \common\models\user\authorizations\SignupForm();
                    $registration->setScenario(\common\models\user\User::SCENARIO_RIGISTRATION_FROM_BITRIX);

                    $registration->setAttributes([
                        'email' => '',
                        'username' => $phone,
                        'password' => 'password',
                        'name' => \common\helpers\Strings::onlyInt($phone),
                        'surname' => '',
                        'patronymic' => '',
                        'isSubscribe' => false,
                        'phone' => $phone,
                        'bitrix_id' => null,
                        'source' => UserEmail::SOURCE_SITE,
                        'source_detail' => UserEmail::SOURCE_DETAIL_REGISTER_TEST,
                    ], false);

                    if ($user = $registration->signup()) {
                        //var_dump($user);
                        if ($isDemo) {
                            \Yii::$app->authManager->assign($roleDemo, $user->id);
                        }
                    } else {
                        $this->stdout("Не могу согдать пользователя для номера '{$phone}'" . PHP_EOL, Console::FG_RED);
                    }
                } else {
                    $this->stdout("Номер уже существует '{$phone}'" . PHP_EOL, Console::FG_CYAN);
                    if ($isDemo) {
                        //Проверяем, может роли нет и ее надо добавить
//                        $this->stdout("Проверка демо-роли для userId='{$user->id}' - " .(isset($userRoles[CmsManager::ROLE_DEMO]) ? 'Есть':'Нет') . PHP_EOL, Console::FG_CYAN);
                        if (!User::hasRole($user->id, CmsManager::ROLE_DEMO)) {
                            \Yii::$app->authManager->assign($roleDemo, $user->id);
                        }
                    }
                }
            }
        }

        $this->stdout("Готово" . PHP_EOL, Console::FG_GREEN);

        return true;
    }

    public function actionExportForSync($limit = 1000)
    {
        $filename = __DIR__ . "/users_list_" . date('Ymd') . ".csv";
        $file = fopen($filename, 'wb');

        $this->stdout("Экспортирую данные в файл '{$filename}'" . PHP_EOL, Console::FG_CYAN);

        $fields = [
            'ID',
            'GUID_site',
            'phone',
            'surname',
            'name',
            'patronymic',
            'GUID_kfss', //empty
            'is_blocked',
            'reason_blocked',
        ];

        fputcsv($file, $fields);

        $usersQuery = \common\models\User::find()
            ->where(['!=', 'phone', ''])
            ->andWhere(['not', ['phone' => null]]);

        if ($limit) {
            $usersQuery->limit((int)$limit);
        }

        $countTotal = $usersQuery->count();
        $count = $limit ?: $countTotal;
        $counterStep = $count / 100; //каждый 1 процент, сколько это в штуках

        $this->stdout("Пользователей найдено (всего) - {$countTotal}" . PHP_EOL, Console::FG_GREEN);

        if ($limit) {
            $this->stdout(" > будет выгружен часть - {$limit}" . PHP_EOL, Console::FG_GREEN);
        }

        $counterGlobal = 0;
        $counter = 0;
        Console::startProgress(0, $count);
        /** @var \common\models\User $user */
        foreach ($usersQuery->each() as $user) {

            $counterGlobal++;
            $counter++;

            if ($counter >= $counterStep || $counterGlobal == $count) {
                $counter = 0;
                Console::updateProgress($counterGlobal, $count);
            }

            $phone = Strings::getPhoneClean($user->phone);
            $fo = CmsUserProperty::find()
                ->where([
                    'element_id' => $user->id,
                    'property_id' => [1, 3] //1 - фамилия, 3 - отчество
                ])
//                ->asArray()
                ->indexBy('property_id')
                ->orderBy(['id' => SORT_ASC])
                ->all();

            //Если вменяемого телефона нет, то такого пользака не выгружаем
            if ($phone) {
                $userData = [
                    $user->id,
                    $user->guid->guid ?? '',
                    $phone,
                    !empty($fo[1]) ? $fo[1]->value : '',
                    $user['name'] ?: '',
                    !empty($fo[3]) ? $fo[3]->value : '',
                    '', //empty
                    '',
                    '',
                ];

                fputcsv($file, $userData);
            }
        }

        $this->stdout("Done" . PHP_EOL, Console::FG_GREEN);

        return true;
    }

    public function actionFixPhones($limit = 1)
    {
        //TODO Поправить условия после миграции чистого поля на место основного!
        $usersQuery = \common\models\User::find()
            ->select(['id', 'phone'])
            ->andWhere(['not', ['phone' => null]])
            ->andWhere(['!=', 'phone', ''])
            ->andWhere(['phone_int' => null])
            ->limit($limit)
            ->orderBy(['id' => SORT_ASC]);

        $countTotal = $usersQuery->count();
        $count = $limit ?: $countTotal;
        $counterStep = $count / 100; //каждый 1 процент, сколько это в штуках

        $this->stdout("Приведение телефонов пользователей к одному виду." . PHP_EOL);
        $this->stdout("С непустым телефоном пользователей: " . $countTotal . PHP_EOL);


        if ($countTotal) {
            $counterGlobal = 0;
            $counter = 0;
            Console::startProgress(0, $count);
            /** @var \common\models\User $user */
            foreach ($usersQuery->each() as $user) {
                $counterGlobal++;
                $counter++;

                if ($counter >= $counterStep || $counterGlobal == $count) {
                    $counter = 0;
                    Console::updateProgress($counterGlobal, $count);
                }

                $userPhoneProper = Strings::getPhoneClean($user->phone);

                if ($userPhoneProper) {
                    if ($user->phone === $userPhoneProper) {
                        $this->stdout("[{$counterGlobal}] TelOk: {$user->phone} [userID: {$user->id}]" . PHP_EOL, Console::FG_GREEN);
                        //Если тел нормальный - его тоже надо сохранить в нормальное поле
                        $user->phone_int = $userPhoneProper;
                        $user->save(true, ['phone_int']);
                    } else {
                        //Тел не ок - сохраняем
                        $user->phone_int = $userPhoneProper;
                        $saveError = $user->save(true, ['phone_int']) ? '' : var_export($user->getErrors(), true);

                                                $this->stdout(("[{$counterGlobal}] {$user->phone} -> {$userPhoneProper} | ") .
                                                    (!$saveError ? "Телефон обновлен успешно!" : "Телефон НЕ обновлен! " . $saveError) . PHP_EOL,
                                                    $saveError ? Console::FG_RED : Console::FG_GREEN);
                    }
                }else{
                    $this->stdout("[{$counterGlobal}] TelBAD - SKIP: {$user->phone} [userID: {$user->id}]" . PHP_EOL, Console::FG_GREEN);
                }

            }
        }

        echo "Done" . PHP_EOL;

        return true;
    }


    public function actionSyncKfss($limit = 1, $offset = 0)
    {
        $usersPhonesQuery = \common\models\User::find()
            //->andWhere(['not', ['phone' => null]]) //ПОСЛЕ ПЕРЕХОДА ЧИСТОГО ПОЛЯ НА МЕСТО ОСНОВНОГО
            ->andWhere(['not', ['phone_int' => null]]) //ПРЕДВАРИТЕЛЬНАЯ синхранизация
            ->andWhere(['kfss_status' => null])
            ->orderBy([
                'phone_int' => SORT_ASC,  //phone_int || phone
                'id' => SORT_ASC
            ])
            ->limit($limit)
            ->offset($offset);

        /** @var \common\models\User $user */
        $i = 0;
        foreach ($usersPhonesQuery->each() as $user) {
            $i++;
            $userGuidSite = $user->guid->getGuid() ?? '';

            //Ищем через кфсс
            $kfssUser = \Yii::$app->kfssLkApiV2->getUserByPhone($user->phone_int); //phone_int || phone
            $kfssUserGuid = $kfssUser['guid'] ?? '';

            //Закоменчено пока тестим, что бы записи не блочились от выборки
            $user->kfss_status = $kfssUser['_status'] ?? \common\models\User::KFSS_STATUS_UNKNOWN;

            if ($kfssUser && !empty($kfssUserGuid)){
                $user->setGuid($kfssUserGuid);

                //Для связи таблицы ГУИДов и связанного ИД в товаре просто установить ГУИД в связанной с товаром записи некорректно
                //Необходимо проверить, возможно такой ГУИД уже есть (например если пользователь уже есть и сейчас у нас дубль)
                //Таким образом просто обновить ГУИД неполучится, будет ошибка уникальности
                //Так что ищим по ГУИДу и если находим - обновляем ID связи в пользователе
                $user->save(false, ['kfss_status']);
            }else{
                $user->updateAttributes(['kfss_status' => $user->kfss_status]);
            }

            if ($kfssUserGuid){
                if ($userGuidSite != $kfssUserGuid){
                    $syncInfo = "Set GUID='{$kfssUserGuid}'";
                    $rowColor = Console::FG_GREEN;
                }else{
                    $syncInfo = 'GUID not update';
                    $rowColor = Console::FG_YELLOW;
                }
            }else{
                $syncInfo = "GUID NOT FOUND";
                $rowColor = Console::FG_RED;
            }

            $this->stdout("[{$i} | {$user->id}] {$userGuidSite} | {$syncInfo}" . PHP_EOL, $rowColor);
        }

        return true;
    }
}



