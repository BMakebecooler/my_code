<?php

/**
 * php ./yii test/email/send-our-subscribers
 * php ./yii test/email/send-mail
 */
namespace console\controllers\test;

use common\components\email\services\modules\newsLetters\GRCreateNewsLettersOptions;
use console\controllers\export\ExportController;
use modules\shopandshow\models\shop\ShopOrder;
use modules\shopandshow\models\shop\ShopOrderStatus;
use rvkulikov\yii2\getResponse\exceptions\GR_1008_ThereIsAnotherResourceWithTheSameValueOfUniqueProperty;
use rvkulikov\yii2\getResponse\modules\contacts\GRCreateContactOptions;
use yii\base\Exception;
use yii\helpers\Console;


/**
 * Class EmailController
 * @package console\controllers
 */
class EmailController extends ExportController
{

    public function actionSendOurSubscribers()
    {
        $grClient = \Yii::$app->getResponseService;
        $campaign = $grClient->getCampaigns()->getCampaign('M');

        $ourEmails = [
            /*[
                'name' => 'kovalenko_ai',
                'email' => 'kovalenko_ai@shopandshow.ru',
                'dayOfCycle' => 1,
                'campaign' => [
                    'campaignId' => $campaign['campaignId'],
                    'campaign' => $campaign['name'],
                ],
                'customFieldValues' => [],
            ],
            [
                'name' => 'anisimov_da',
                'email' => 'anisimov_da@shopandshow.ru',
                'dayOfCycle' => 1,
                'campaign' => [
                    'campaignId' => $campaign['campaignId'],
                    'campaign' => $campaign['name'],
                ],
                'customFieldValues' => [],
            ],
            [
                'name' => 'podrebinnikov_pp',
                'email' => 'podrebinnikov_pp@shopandshow.ru',
                'dayOfCycle' => 1,
                'campaign' => [
                    'campaignId' => $campaign['campaignId'],
                    'campaign' => $campaign['name'],
                ],
                'customFieldValues' => [],
            ],
            [
                'name' => 'soskov_da',
                'email' => 'soskov_da@shopandshow.ru',
                'dayOfCycle' => 1,
                'campaign' => [
                    'campaignId' => $campaign['campaignId'],
                    'campaign' => $campaign['name'],
                ],
                'customFieldValues' => [],
            ],*/
            [
                'name' => 'shiktvru',
                'email' => 'shiktvru@yandex.ru',
                'dayOfCycle' => 1,
                'campaign' => [
                    'campaignId' => $campaign['campaignId'],
                    'campaign' => $campaign['name'],
                ],
                'customFieldValues' => [],
            ],

        ];

        foreach ($ourEmails as $email) {

            $newContact = new GRCreateContactOptions($email);

            try {
                $createContact = $grClient->getContacts()->createContact($newContact);
//                \yii\helpers\VarDumper::dump($createContact);
            } catch (Exception $exception) {
                var_dump($exception->getMessage());
            }
        }
    }

    /**
     * https://apidocs.getresponse.com/v3/resources/newsletters#newsletters.create
     */
    public function actionSendMail()
    {
        $grClient = \Yii::$app->getResponseService;
        $campaign = $grClient->getCampaigns()->getCampaign($grClient->getCampaignToken());

//        $contacts = $grClient->getContacts()->getContacts();

        $testHtml = <<<HTML

    <style type="text/css">
    .tg  {border-collapse:collapse;border-spacing:0;margin:0px auto;}
    .tg td{font-family:Arial, sans-serif;font-size:14px;padding:10px 5px;border-style:solid;border-width:1px;overflow:hidden;word-break:normal;}
    .tg th{font-family:Arial, sans-serif;font-size:14px;font-weight:normal;padding:10px 5px;border-style:solid;border-width:1px;overflow:hidden;word-break:normal;}
    .tg .tg-yw4l{vertical-align:top}
    </style>
    <table class="tg">
      <tr>
        <th class="tg-yw4l">1</th>
        <th class="tg-yw4l">2</th>
        <th class="tg-yw4l">3</th>
      </tr>
      <tr>
        <td class="tg-yw4l">4</td>
        <td class="tg-yw4l">5</td>
        <td class="tg-yw4l">6</td>
      </tr>
    </table>

HTML;

        $newLetter = new GRCreateNewsLettersOptions([
            'name' => 'Название рассылки на новом сайте (Взять из админки)',
            'type' => 'broadcast', // draft - черновик broadcast - рассылка
            'editor' => 'html2',
            'subject' => 'Тема рассылки на новом сайте (Взять из админки)', //*
            'campaign' => [
                'campaignId' => $campaign['campaignId'],
            ], //*
            'fromField' => [
                'fromFieldId' => $campaign['confirmation']['fromField']['fromFieldId']
            ], //*

            'replyTo' => null,
            'content' => [
                'html' => $testHtml
            ], //*

//            'flags' => 'test', //Message flags. Allowed values: openrate, clicktrack and google_analytics
//            'attachments' => 'test',

            'sendSettings' => [
                'selectedCampaigns' => [
                    $campaign['campaignId']
                ],
                /*                'selectedContacts' => [
                                    $campaign['campaignId']
                                ],*/
                'timeTravel' => 'false',
                'perfectTiming' => 'true',
            ], //*
        ]);

        $createNewsLetters = $grClient->getNewsLetters()->sendNewsletter($newLetter);

        \yii\helpers\VarDumper::dump($createNewsLetters);
    }
}



