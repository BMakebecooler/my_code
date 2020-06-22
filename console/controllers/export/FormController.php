<?php

/**
 * php ./yii export/form
 * php ./yii export/form/call-back
 * php ./yii export/form/subscribe
 * php ./yii export/form/send-coupon500r-requests
 * php ./yii export/form/send-black-friday
 */

namespace console\controllers\export;

use common\helpers\Strings;
use modules\shopandshow\models\users\UserEmail;
use skeeks\cms\components\Cms;
use skeeks\modules\cms\form2\models\Form2Form;
use skeeks\modules\cms\form2\models\Form2FormSend;
use yii\base\Exception;
use yii\helpers\Console;


/**
 * Class FormController
 * @package console\controllers
 */
class FormController extends ExportController
{

    const
        FORM_SUBSCRIBE = 5,
        FORM_CALLBACK = 4;

    const
        STATUS_CANCELED = 15,
        STATUS_EXPORTED = 20;

    //Дата начала отправки запросов на купон по новой схеме
    const COUPONS_SEND_DATE_BEGIN = '2019-06-01';

    public function actionIndex()
    {
        $this->actionCallBack();
//        $this->actionSubscribe();
//        $this->actionSubscribeEighthOfMarch();
        $this->sendSubscriberRecipes();
        $this->actionSendCoupon500rRequests();


        $this->actionSendBlackFriday();
    }

    /**
     * Обратный звонок
     */
    public function actionCallBack()
    {

        $this->stdout("Prepare to export Callback requests\n", Console::FG_CYAN);

        $requests = $this->getForms(self::FORM_CALLBACK);

        $total = count($requests);

        $this->stdout("Got {$total} requests to export\n", Console::FG_YELLOW);

        /** @var Form2FormSend $r */
        foreach ($requests as $r) {

            $params = $this->getCallbackExportData($r);

            $this->stdout("Callback request {{$params['phone']}} ", Console::FG_YELLOW);

            try {

                \Yii::$app->shopAndShow->sendCallBack($params);

                $msg
                    = "
Информационное сообщение сайта Shop & Show [НОВЫЙ САЙТ]
-------------------------------------------------------

Вам было отправлено сообщение через форму обратной связи [НОВЫЙ САЙТ]

Имя: {$params['name']}
Номер телефона: {$params['phone']}
Удобное время звонка: {$params['time_to_call']}
Тема звонка: {$params['theme']}


Сообщение сгенерировано автоматически.

";
//ToDo выяснить, почему не приходят уведомления на supervisors@shopandshow.ru
                $mail = \Yii::$app->mailer->compose()
                    ->setTextBody($msg)
                    ->setFrom('no-reply@shopandshow.ru')
                    ->setTo([
                        'Filippov_av@shopandshow.ru',
                        'filippov@shopandshow.ru',
                        'kiselev_af@shopandshow.ru',
                        'obora_yua@shopandshow.ru',
                        'zadvornykh_om@shopandshow.ru',
                        'polyakova_nm@shopandshow.ru',
                        'martirosyan_ta@shopandshow.ru',
                        'ryabov_yn@shopandshow.ru',
                        'anisimov_da@shopandshow.ru',
                        //'soskov_da@shopandshow.ru',
                        'supervisors@shopandshow.ru',
                        'support@shopandshow.ru',
                    ])
                    ->setSubject('[НОВЫЙ САЙТ] Shop & Show: Обратный звонок');

                //Отправка в данном случае идет через mailer aka mailgun
                //Если какие то проблемы с mg то возникающая ошибка не обновляет статус формы и она агентом пытается экспортироваться снова
                //Получаем ситуацию что в кфсс одна и та же форма отправляется снова и снова
                //Пока просто отключим отправку писем через mg, тем более что эта же отправка идет ранее через простой mail(), сразу при подписке пользователя
                if (false) {
                    $mailResult = $mail->send();
                }else{
                    $mailResult = true;
                }

                $r->status = self::STATUS_EXPORTED;
                $r->save(false);

//                mail('gadashevich_bv@shopandshow.ru','[НОВЫЙ САЙТ] Shop & Show: Обратный звоно',$msg);

                $this->stdout(" '{$mailResult}' OK\n", Console::FG_GREEN);

            } catch (Exception $e) {

                $this->stdout(" Error\n", Console::FG_RED);
                $this->stdout(" Data: " . json_encode($params) . "\n", Console::FG_YELLOW);
                $this->stdout("Error: " . $e->getMessage() . "\n", Console::FG_PURPLE);

            }

        }

    }

    /**
     * Признак маил ру почты
     * @param $email
     * @return bool
     */
    private function isEmailMailRu($email)
    {

        return false;

        $domains = [
            'mail.ru',
            'inbox.ru',
            'list.ru',
            'bk.ru',
        ];

        return Strings::containsArray($email, $domains);
    }

    /**
     * Вернуть ид компании
     * @param $email
     * @return array
     */
    private function getCampaignByEmail($email)
    {
        /**
         * Так как для маил рушных почт выделили другую компанию
         */
        return $this->isEmailMailRu($email) ? [
            'id' => '25',
            'token' => 'G',
        ] : [
            'id' => '1',
            'token' => '9',
        ]; //shopandshow_news_2 - id = 25 token = G; shopandshow - id = 1 token = 9
    }

    /**
     * Отправить подписчиков в ГР
     */
    public function actionSubscribe()
    {

        $this->stdout("Prepare to export Subscribe requests\n", Console::FG_CYAN);

        $requests = $this->getForms(self::FORM_SUBSCRIBE);

        $total = count($requests);

        $this->stdout("Got {$total} requests to export\n", Console::FG_YELLOW);

        if (!$requests) return;

        $grClient = \Yii::$app->getResponseService;

        /** @var Form2FormSend $r */
        foreach ($requests as $r) {

            $this->stdout("Subscribe {$r->relatedPropertiesModel->getAttribute('email')} ", Console::FG_YELLOW);

            $data = $this->getSubscribeExportData($r);

            try {

                $email = $data['email'];

                if (false == filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $r->status = self::STATUS_CANCELED;
                    $r->save(false);
                    $this->stdout(" skipped not valid email: {$email} \n", Console::FG_RED);
                    continue;
                }

                $contact = new \rvkulikov\yii2\getResponse\modules\contacts\GRCreateContactOptions([
                    "name" => null,
                    "email" => $email,
                    "dayOfCycle" => null,
                    "ipAddress" => $r->ip ?: null,
                    'campaign' => [
                        'campaignId' => $this->getCampaignByEmail($email)['token'],
                    ],
                ]);

                $createContacts = $grClient->getContacts()->createContact($contact);
                \yii\helpers\VarDumper::dump($createContacts);

                $r->status = self::STATUS_EXPORTED;
                $r->save(false);

                $this->stdout(" OK\n", Console::FG_GREEN);
            } catch (Exception $e) {

                // There is another resource with the same value of unique property
                // @see https://apidocs.getresponse.com/en/v3/errors/1008
                // You tried to add contact that is already on your blacklist
                // @see https://apidocs.getresponse.com/v3/errors/1002
                if ($e->getCode() == 1008 || $e->getCode() == 1002) {
                    $r->status = self::STATUS_CANCELED;

                    $r->save(false);
                    $this->stdout(" OK\n", Console::FG_GREEN);
                } else {
                    $this->stdout(" Error\n", Console::FG_RED);
                    $this->stdout(" Data: " . json_encode($data) . "\n", Console::FG_YELLOW);
                    $this->stdout("Error: " . $e->getMessage() . "\n", Console::FG_PURPLE);
                }
            }

            /**
             * Если почта не маил ру то генерируем купон
             */
            if (true) { //|| !$this->isEmailMailRu($email)

                try {

                    $queue = clone \Yii::$app->siteExchange;

                    $queue->vhost = 'production';
                    $queue->exchangeName = 'NEWSITE';
                    $queue->queueName = 'website.subscribers';
                    $queue->routingKey = 'website.subscriber.add';

                    $dataPush = [
                        'method' => 'add-subscriber',
                        'data' => $data,
                        'timestamp' => time(),
                    ];

                    $queue->push($dataPush);

                    $this->stdout(" OK\n", Console::FG_GREEN);

                } catch (Exception $e) {

                    $this->stdout(" Error\n", Console::FG_RED);
                    $this->stdout(" Data: " . json_encode($data) . "\n", Console::FG_YELLOW);
                    $this->stdout("Error: " . $e->getMessage() . "\n", Console::FG_PURPLE);

                }

            }
        }

    }

    /**
     * Отправка подписчиков для акции 8 Марта
     */
    public function actionSubscribeEighthOfMarch()
    {
        return;
        $this->sendSubscriberByForm(11, 'Y');
    }

    /**
     * Отправка подписчиков в компанию по форме
     * @param $formId
     * @param $gRCampaignToken
     */
    protected function sendSubscriberByForm($formId, $gRCampaignToken)
    {

        $this->stdout("Prepare to export sendSubscriberByForm requests\n", Console::FG_CYAN);

        $requests = $this->getForms($formId);

        $total = count($requests);

        $this->stdout("Got {$total} requests to export\n", Console::FG_YELLOW);

        if (!$requests) return;

        $grClient = \Yii::$app->getResponseService;
        $grClient->setCampaignToken(\Yii::$app->params['getresponse']['tokens']['subscription']);
        $campaign = $grClient->getCampaigns()->getCampaign($gRCampaignToken);

        /** @var Form2FormSend $r */
        foreach ($requests as $r) {

            $this->stdout("Subscribe {$r->relatedPropertiesModel->getAttribute('email')} ", Console::FG_YELLOW);

            $data = $this->getSubscribeExportData($r);

            try {

                if (false == filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                    $r->status = self::STATUS_CANCELED;
                    $r->save(false);
                    $this->stdout(" skipped not valid email: {$data['email']} \n", Console::FG_RED);
                    continue;
                }

                $contact = new \rvkulikov\yii2\getResponse\modules\contacts\GRCreateContactOptions([
                    "name" => null,
                    "email" => $data['email'],
                    "dayOfCycle" => null,
                    "ipAddress" => $r->ip ?: null,
                    'campaign' => [
                        'campaignId' => $campaign['campaignId'],
                    ],
                ]);

                $createContacts = $grClient->getContacts()->createContact($contact);
                \yii\helpers\VarDumper::dump($createContacts);

                $r->status = self::STATUS_EXPORTED;
                $r->save(false);

                $this->stdout(" OK\n", Console::FG_GREEN);
            } catch (Exception $e) {

                // There is another resource with the same value of unique property
                // @see https://apidocs.getresponse.com/en/v3/errors/1008
                // You tried to add contact that is already on your blacklist
                // @see https://apidocs.getresponse.com/v3/errors/1002
                if ($e->getCode() == 1008 || $e->getCode() == 1002) {
                    $r->status = self::STATUS_CANCELED;

                    $r->save(false);
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


    /**
     * Отправка подписчиков в компанию рецептов
     */
    protected function sendSubscriberRecipes()
    {

        $this->stdout("Prepare to export sendSubscriberRecipes requests\n", Console::FG_CYAN);

        $requests = $this->getForms(12);

        $total = count($requests);

        $this->stdout("Got {$total} requests to export\n", Console::FG_YELLOW);

        if (!$requests) return;

        $grClientRecipes = \Yii::$app->getResponseService;
        $grClientRecipes->setCampaignToken(\Yii::$app->params['getresponse']['tokens']['subscription']);
        $campaignRecipes = 'V'; //$grClientRecipes->getCampaigns()->getCampaign('V');

        $grClientMain = \Yii::$app->getResponseService;


        \Yii::$app->mailer->htmlLayout = false;
        \Yii::$app->mailer->textLayout = false;


        /** @var Form2FormSend $r */
        foreach ($requests as $r) {

            $this->stdout("Subscribe {$r->relatedPropertiesModel->getAttribute('email')} ", Console::FG_YELLOW);

            $data = $this->getSubscribeExportData($r);

            if (false == filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $r->status = self::STATUS_CANCELED;
                $r->save(false);
                $this->stdout(" skipped not valid email: {$data['email']} \n", Console::FG_RED);
                continue;
            }


            /////////////////////////////////////////
            $theme = sprintf('7 лучших рецептов от шеф-повара для ВАС');

            $message = \Yii::$app->mailer->compose('@templates/mail/promo/recipes', [
                'theme' => $theme,
                'data' => [
                    'URL' => 'https://shopandshow.ru/v2/common/docs/book_recipes.pdf',
                    'IMG' => '/v2/common/img/newsletter/recipes/recipes-700x450.jpg',
                    'SUBJECT' => $theme,
                ],
            ])
                ->setFrom([\Yii::$app->cms->adminEmail => \Yii::$app->cms->appName])
                ->setSubject($theme);

            $debugMode = false;

            //В debug режиме не будем реально отправлять письма адресатам
            if ($debugMode) {
                //Отправляем не адресату, а разработчикам
                $message->setTo([
                    'anisimov_da@shopandshow.ru',
                    'soskov_da@shopandshow.ru',
                    'panina_av@shopandshow.ru',
                    'fazilova_ev@shopandshow.ru',
                ]);
            } else {
                $message->setTo($data['email']);
            }

            $result = $message->send();

            if ($result) {
                $this->stdout("send recipes ok \n", Console::FG_GREEN);
            }


            try {

                //Добавляем в главную компанию
                $contactMainCompaign = new \rvkulikov\yii2\getResponse\modules\contacts\GRCreateContactOptions([
                    "name" => null,
                    "email" => $data['email'],
                    "dayOfCycle" => null,
                    "ipAddress" => $r->ip ?: null,
                    'campaign' => [
                        'campaignId' => $this->getCampaignByEmail($data['email'])['token'],
                    ],
                ]);

                $createContacts = $grClientMain->getContacts()->createContact($contactMainCompaign);
                //\yii\helpers\VarDumper::dump($createContacts);

                $r->status = self::STATUS_EXPORTED;
                $r->save(false);

                $this->stdout(" OK\n", Console::FG_GREEN);

            } catch (Exception $e) {

                // There is another resource with the same value of unique property
                // @see https://apidocs.getresponse.com/en/v3/errors/1008
                // You tried to add contact that is already on your blacklist
                // @see https://apidocs.getresponse.com/v3/errors/1002
                if ($e->getCode() == 1008 || $e->getCode() == 1002) {
                    $r->status = self::STATUS_CANCELED;

                    $r->save(false);
                    $this->stdout(" OK\n", Console::FG_GREEN);
                } else {
                    $this->stdout(" Error\n", Console::FG_RED);
                    $this->stdout(" Data: " . json_encode($data) . "\n", Console::FG_YELLOW);
                    $this->stdout("Error: " . $e->getMessage() . "\n", Console::FG_PURPLE);
                    var_dump($e);
                }
            }


            try {

                //ДОБАВЛЯЕМ в компанию с рецептами
                $contactRecipes = new \rvkulikov\yii2\getResponse\modules\contacts\GRCreateContactOptions([
                    "name" => null,
                    "email" => $data['email'],
                    "dayOfCycle" => null,
                    "ipAddress" => $r->ip ?: null,
                    'campaign' => [
                        'campaignId' => $campaignRecipes, //['campaignId'],
                    ],
                ]);

                $createContacts = $grClientRecipes->getContacts()->createContact($contactRecipes);
                \yii\helpers\VarDumper::dump($createContacts);

                $this->stdout(" OK\n", Console::FG_GREEN);

            } catch (Exception $e) {
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

    /** Было сделано для акции триколор */
    public function actionTricolorSubscribe()
    {

        return false;

        $form = $this->getFormByCode('FORM_TRICOLOR_LANDING');

        if ($form == null) {
            $this->stdout("Form with code FORM_TRICOLOR_LANDING not found\n", Console::FG_YELLOW);

            return false;
        }

        $requests = $this->getForms($form->id);

        $total = count($requests);

        $this->stdout("Got {$total} subscription to export\n", Console::FG_YELLOW);

        /** @var Form2FormSend $r */
        foreach ($requests as $r) {

            $this->stdout("\nPrepare to export form ID:" . $r->id, Console::FG_YELLOW);

            try {
                /** @var SyncQueue */
                $queue = \Yii::$app->queue;
                $queue->queueName = 'from_site';

                $data = [
                    'function' => 'register-tricolor-subscription',
                    'data' => [
                        'email' => $r->relatedPropertiesModel->getAttribute('email'),
                    ],
                    'timestamp' => time(),
                ];

                $queue->push($data);

                $r->status = self::STATUS_EXPORTED;
                $r->save(false);

                $this->stdout(" OK", Console::FG_GREEN);

            } catch (Exception $e) {

//                $this->stdout(" Error\n", Console::FG_RED);
//                $this->stdout(" Data: ".json_encode($data)."\n", Console::FG_YELLOW);
//                $this->stdout("Error: ".$e->getMessage(), Console::FG_PURPLE);

            }

        }

    }

    protected function getCallbackExportData($data)
    {
        return [
            'name' => $data->relatedPropertiesModel->getAttribute('name'),
            'phone' => $data->relatedPropertiesModel->getAttribute('phone'),
            'theme' => $data->relatedPropertiesModel->getAttribute('theme'),
            'time_to_call' => $data->relatedPropertiesModel->getAttribute('time'),
        ];
    }

    protected function getSubscribeExportData($data)
    {
        return [
            'email' => $data->relatedPropertiesModel->getAttribute('email'),
        ];
    }


    protected function getForms($formId)
    {
        return Form2FormSend::find()
            ->andWhere('form_id=:FORM_ID', [':FORM_ID' => $formId])
            ->andWhere('status=:STATUS_ID', [':STATUS_ID' => Form2FormSend::STATUS_NEW])
            ->all();

    }

    /**
     * Вернуть форму по ее коду
     * @param $code
     * @return array|null|\yii\db\ActiveRecord
     */
    protected function getFormByCode($code)
    {
        return Form2Form::find()->andWhere(
            'code = :code', [':code' => $code]
        )->one();
    }

    /**
     * Для мыл из базы отправка запроса на получение купона на 500р
     */
    public function actionSendCoupon500rRequests()
    {

        $usersEmails = UserEmail::find()
            ->where(['!=', 'is_send_coupon_500r', Cms::BOOL_Y])
            ->andWhere(['is_valid_site' => Cms::BOOL_Y])
            ->andWhere(['>=', 'created_at', strtotime(self::COUPONS_SEND_DATE_BEGIN)])
            ->orderBy(['id' => SORT_ASC])
            ->limit(150)// TODO Лимит что бы при первом запуске не попытался отправить всю базу одним разом, можно потом убрать
            ->all();

        $this->stdout("Найдено мыл для отправки: " . count($usersEmails) . "\n", Console::FG_GREEN);

        sleep(3);

        if ($usersEmails) {
            $queue = clone \Yii::$app->siteExchange;

            $queue->vhost = '/';
            $queue->exchangeName = 'PromoExchange';
            $queue->routingKey = 'SITE.PROMO';

            $info = [
                "Type" => "PROMO",
                "Version" => "1.0", //Версия сообщения (string)
                "Source" => 'SITE', //Источник сообщения (string)
                "SourceDetail" => SS_SITE . '_' . YII_ENV . '_' . \Yii::$app->shopAndShowSettings->channelSaleGuid, //Детализация источника (string)
                "Date" => date('Y-m-d H:i:s'), // "2017-08-08T10:00:00+03:00" //дата и время отправки сообщения в очередь
            ];

            foreach ($usersEmails as $userEmail) {

                /** @var $userEmail \modules\shopandshow\models\users\UserEmail */
                $data = [
                    'Email' => $userEmail->value,
                    'PromoCodeType' => 1,
                ];

                //Отправляем запрос на купон в очередь
                try {

                    $dataPush = [
                        'Info' => $info,
                        'Data' => $data,
                    ];

                    $queue->push($dataPush);

                    $this->stdout(" OK\n", Console::FG_GREEN);

                    $userEmail->is_send_coupon_500r = Cms::BOOL_Y;

                    if (!$userEmail->save(false)) {
                        $this->stdout("Ошибка при сохранении email='{$userEmail->value}'\n", Console::FG_RED);
                        $this->stdout(var_export($userEmail->getErrors(), true) . "\n", Console::FG_RED);
                    }

                } catch (\Exception $e) {

                    $this->stdout(" Error\n", Console::FG_RED);
                    $this->stdout(" Data: " . json_encode($data) . "\n", Console::FG_YELLOW);
                    $this->stdout("Error: " . $e->getMessage() . "\n", Console::FG_PURPLE);

                }
            }
        }

        return;
    }

    /**
     * Черная пятница 2018
     */
    public function actionSendBlackFriday()
    {
        if ($form = $this->getFormByCode('black-friday-2018')) {
            $this->sendSubscriberByForm($form->id, 't');
        }
    }

}