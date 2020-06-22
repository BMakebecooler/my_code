<?php

namespace common\components\email\services;

use common\helpers\App;
use common\helpers\Common;
use common\helpers\Strings;
use modules\shopandshow\models\users\UserEmail;
use skeeks\cms\base\Component;
use yii\di\Instance;
use yii\httpclient\Client;
use yii\base\Exception;

/**
 * Класс работы с сервисом RetailRocket
 * Class RetailRocket
 * @package common\components\email\services
 */
class RetailRocket extends Component
{
    /**
     * файл csv от Retail Rocket со списком всех емейлов наших клиентов в их системе
     *
     * @var string $fileCsv
     */
    public static $fileCsv = '@frontend/web/uploads/rr_emails.csv';

    public static $notifyEmails = [
//        'b.gadashevich@makebecool.com',
        'kusyukbaev_ss@shopandshow.ru',
    ];

    //https://api.retailrocket.net/api/1.0/partner/564d72a46636b420a06cf01a/
    //              transactionalemail/5b35c2df97a528853858deb3?apikey= 564d72a46636b420a06cf01b&email=<TargetEmail>

    //https://api.retailrocket.net/api/1.0/partner/<PARTNER_TOKEN>/
    //              transactionalemail/<MAIL_TEMPLATE_TOKEN>?apikey=<API_KEY>&email=<TargetEmail>

    /**
     * @var Client $httpClient
     */
    public $httpClient;

    public $baseUrl;
    public $partnerToken;
    public $mailTemplatesTokens;
    public $apiKey;

    public function init()
    {
        if (is_array($this->httpClient)) {
            $this->httpClient = Instance::ensure($this->httpClient);
        }

        parent::init();
    }

    public function sendEmailWithTemplate($email, $template, $params = [])
    {

        if (!array_key_exists($template, $this->mailTemplatesTokens)) {
            //Шалон отправки не найден
            return false;
        }

        $requestUrl = sprintf("%s%s/transactionalemail/%s?apikey=%s&email=%s",
            $this->baseUrl,
            $this->partnerToken,
            $this->mailTemplatesTokens[$template],
            $this->apiKey,
            $email
        );

        $request = $this->httpClient->createRequest()
            ->setMethod('POST')
            ->setUrl($requestUrl);

        if ($params) {
            $request
                ->setHeaders(['content-type' => 'application/json'])
                ->setContent(\GuzzleHttp\json_encode($params));
        } else {
            $request->setOptions([
                CURLOPT_POSTFIELDS => ""
            ]);
        }

        $response = $request->send();

        if ($response->isOk) {
            $sended = true;
//            var_dump('Response OK');
        } else {
            $sended = false;
            // TODO: find better place for log errors
//            var_dump('Bad response');
//            var_dump($response);
        }
        return $sended;
    }

    public static function getDoubleOptInUrl($email)
    {
        return \Yii::$app->urlManager->createUrl([
            'site/check-retail-rocket-email',
            'email' => $email
        ]);
    }

    /**
     * @throws yii\base\Exception|Exception
     *
     */
    public function loadEmailsFromCSV()
    {
        $pathToFile = \Yii::getAlias(static::$fileCsv);

        if (!file_exists($pathToFile) || !is_readable($pathToFile)) {
            throw new Exception('Error open file ' . $pathToFile);
        }

        $validator = new \yii\validators\EmailValidator();
        $count = 0;

        if (App::isConsoleApplication()) {
            echo 'Start import Emails from file' . PHP_EOL;
        }

        Common::startTimer("RRLoadEmailsFromCSV");

        if (($handle = fopen($pathToFile, 'r')) !== false) {

            //определяем разделитель строки
            $header = fgets($handle);
            $char = Strings::getSplitChar($header);

            while (($row = fgetcsv($handle, 1000, $char)) !== false) {
                $email = $row[0];
                if ($validator->validate($email)) {

                    $count++;

                    //todo для отладки!
//                    echo 'Email ' . $email, PHP_EOL;

                    $attributes = [];
                    $attributes['approved_rr'] = $row[1] == 'True' ? Common::BOOL_Y_INT : Common::BOOL_N_INT;

                    $model = UserEmail::find()->andWhere(['value' => $email])->one();
                    if (!$model) {
                        $attributes['source'] = UserEmail::SOURCE_RR;
                        $attributes['source_detail'] = UserEmail::SOURCE_DETAIL_RR_API;
                    }

                    UserEmail::addToBase($email, $attributes);

                }
            }
        }

        $time = Common::getTimerTime("RRLoadEmailsFromCSV", false);

        if (App::isConsoleApplication()) {
            echo 'Number of processed emails: ' . $count . PHP_EOL;
            echo 'Time: ' . $time . PHP_EOL;
        }

        //Отправка письма о успешной загрузке
        \Yii::$app->mailer->compose()
            ->setFrom([\Yii::$app->cms->adminEmail => \Yii::$app->cms->appName])
            ->setTo(self::$notifyEmails)
            ->setSubject('Загрузка списка Email клиентов завершена успешно.')
            ->setTextBody('Загрузка списка Email клиентов завершена успешно. Количество емейлов ' . $count)
            ->setHtmlBody('<b>Выгрузка списка Email клиентов завершена успешно. Количество емейлов ' . $count . '</b>')
            ->send();


    }

}