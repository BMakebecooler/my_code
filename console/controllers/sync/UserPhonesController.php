<?php

/**
 * php ./yii sync/user-phones
 */

namespace console\controllers\sync;

use modules\shopandshow\models\users\ContactDataBitrixUser;
use yii\helpers\Console;


/**
 * Class UserPhonesController
 *
 * @package console\controllers
 */
class UserPhonesController extends SyncController
{

    public function actionIndex()
    {

        $query = <<<SQL
                SELECT b_user.id, b_user.email, b_user.personal_phone, b_user.work_phone, guids.GUID AS guid
                FROM front2.b_user AS b_user
                LEFT JOIN contact_data_bitrix_users AS contact_data ON contact_data.id = b_user.id 
                LEFT JOIN front2.sands_guid_storage AS guids ON guids.LOCAL_ID = b_user.id AND ENTITY = 'USER'
                WHERE contact_data.id IS NULL
SQL;

        $this->stdout("Updating bitrix users ", Console::FG_YELLOW);

        $bitrixUsers = \Yii::$app->db->createCommand($query)->queryAll();

//        $this->stdout("Found " . sizeof($bitrixUsers) . " users", Console::FG_YELLOW);

        foreach ($bitrixUsers as $user) {
//            $this->stdout("User Id: {$user['id']}: ", Console::FG_GREEN);

            $contact = new ContactDataBitrixUser();

            $contact->id = $user['id'];
            $contact->guid = $user['guid'];
            $contact->email = $this->parseEmail($user['email']);

            if (empty($contact->email)) {
//                $this->stdout("empty email" . PHP_EOL, Console::FG_RED);
                continue;
            }

            try {
                $contact->phone = $this->parsePhones($user['personal_phone'], $user['work_phone']);
            } catch (\Exception $e) {
                $this->stdout($e->getMessage() . PHP_EOL, Console::FG_RED);
                continue;
            }

            if ($contact->save()) {
//                $this->stdout("OK email: {$contact->email}, phone: {$contact->phone}:" . PHP_EOL, Console::FG_GREEN);
            } else {
                $this->stdout(" failed to save " . print_r($contact->getErrors(), true) . PHP_EOL, Console::FG_RED);
            }
        }

        return true;
    }

    private function parseEmail($email)
    {
        return strtolower(trim($email));
    }

    // нЕкогда заморачиваться с func_get_args, телефонов и так всего 2...
    //мОжно просто использовать массивы :)
    private function parsePhones($phone1, $phone2)
    {
        if (!empty($phone1) && $parsedPhone1 = $this->parsePhone($phone1)) return $parsedPhone1;
        if (!empty($phone2) && $parsedPhone2 = $this->parsePhone($phone2)) return $parsedPhone2;

        if (!empty($phone1)) {
            throw new \yii\base\ErrorException("Cant parse $phone1");
        }
        if (!empty($phone2)) {
            throw new \yii\base\ErrorException("Cant parse $phone2");
        }

        return null;
    }

    private function parsePhone($phone)
    {
        $country = 'RU';
        $phoneNumberUtil = \libphonenumber\PhoneNumberUtil::getInstance();

        try {

            $numberProto = $phoneNumberUtil->parse($phone, $country);

            if ($phoneNumberUtil->isValidNumber($numberProto)) {
                return $phoneNumberUtil->format($numberProto, \libphonenumber\PhoneNumberFormat::INTERNATIONAL);
            }

        } catch (\libphonenumber\NumberParseException $e) {
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

}