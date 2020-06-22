<?php

/**
 * php ./yii tools/gr/send-subscribers-by-email
 */

namespace console\controllers\tools;

use console\controllers\export\ExportController;
use Exception;
use modules\shopandshow\models\users\UserEmail;
use yii\helpers\Console;

/**
 * Class GrController
 * @package console\controllers
 */
class GrController extends ExportController
{

    public function actionSendSubscribersByEmail()
    {

        $sql = <<<SQL
SELECT 
  t.value AS email,
  t.source,
  t.source_detail
FROM cms_user_email AS t 
WHERE t.created_at >= UNIX_TIMESTAMP(CURDATE())-7200 AND t.created_at <= UNIX_TIMESTAMP(NOW())
SQL;

        $emails = \Yii::$app->db->createCommand($sql)->queryAll();

        $count = count($emails);

        $this->stdout($this->ansiFormat("Внимание!", Console::FG_RED, Console::BOLD) . " Будут отправлены {$count} емайлов!\n");
        $this->delay(5);

        $grClient = \Yii::$app->getResponseService;
        $grClient->setCampaignToken(\Yii::$app->params['getresponse']['tokens']['subscription']);
        $campaign = $grClient->getCampaigns()->getCampaign($grClient->getCampaignToken());

        foreach ($emails as $data) {

            $email = $data['email'];

            if (false == filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->stdout(" skipped not valid email: {$email} \n", Console::FG_RED);
                continue;
            }

            //* Источник мыла в базе *//

            if ($data['source'] && $data['source_detail']) {

                if ($data['source'] == UserEmail::SOURCE_PHONE) {
                    $sourceValue = 'callcenter';
                } elseif ($data['source'] == UserEmail::SOURCE_SITE) {
                    $sourceValue = $data['source_detail'];
                } else {
                    $sourceValue = "{$data['source']}_{$data['source_detail']}";
                }

            } else {
                $sourceValue = 'unknown';
            }

            //* /Источник мыла в базе *//

            try {

                $contactOptions = [
                    'email' => $email,
                    'campaign' => $campaign,
                    'customFieldValues' => [
                        [
                            'customFieldId' => '9r', //source
                            'values' => [$sourceValue]
                        ]
                    ]
                ];

                $contact = new \rvkulikov\yii2\getResponse\modules\contacts\GRCreateContactOptions($contactOptions);

                $createContacts = $grClient->getContacts()->createContact($contact);
                \yii\helpers\VarDumper::dump($createContacts);
                $this->stdout(" OK\n", Console::FG_GREEN);

            } catch (Exception $e) {

                // There is another resource with the same value of unique property
                // @see https://apidocs.getresponse.com/en/v3/errors/1008
                // You tried to add contact that is already on your blacklist
                // @see https://apidocs.getresponse.com/v3/errors/1002

                if ($e->getCode() == 1008 || $e->getCode() == 1002) {
                    $this->stdout(" OK\n", Console::FG_GREEN);
                } else {
                    $this->stdout(" Error\n", Console::FG_RED);
                    $this->stdout(" Data: " . json_encode($data) . "\n", Console::FG_YELLOW);
                    $this->stdout("Error: " . $e->getMessage() . "\n", Console::FG_PURPLE);
                    var_dump($e);
                }
            }
        }
    }
}



