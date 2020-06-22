<?php
namespace console\controllers;

use modules\shopandshow\components\mail\MonitoringDayHelper;
use modules\shopandshow\models\mail\MailDispatch;
use modules\shopandshow\models\mail\MailTemplate;
use modules\shopandshow\models\monitoringday\Plan;
use skeeks\cms\components\Cms;
use yii\helpers\Console;

/**
 * Class MailController
 *
 *  формирует новую ежедневную рассылку
 * php ./yii mail/generate-cts [campaign='']
 * php ./yii mail/generate-cts-test [campaign=C]
 * php ./yii mail/generate-cts-test-by-segments --campaignToken=M --segments=time_test03,time_test04
 * php ./yii mail/generate-simple-test-by-segments --campaignToken=Q --segments=time_test03,time_test04
 *
 *  формирует выбранную в TEMPLATE_COMMON рассылку
 * php ./yii mail/generate-common [campaign='']
 * php ./yii mail/generate-common-test [campaign=C]
 *
 *  формирует рассылку из конструктора рассылок
 * php ./yii mail/generate-constructor [campaign='']
 * php ./yii mail/generate-constructor-test [campaign=C]
 *
 *  отправка отчета на почту
 * php ./yii mail/generate-monitoring-today
 * php ./yii mail/generate-monitoring-yesterday
 *
 * @package console\controllers
 */
class MailController extends \yii\console\Controller
{
    // шаблон для ежедневной рассылки
    const TEMPLATE_CTS_DAILY = 'SandsCtsGrid';
    // шаблон для одноразовой рассылки
    const TEMPLATE_COMMON = 'Simple';
    // шаблон для рассылки шаблона из конструктора рассылок
    const TEMPLATE_CONSTRUCTOR = 'SandsConstructorGrid';

    private $isTest = false;

    public $campaignToken = 'C';
    public $segments = [];

    public function options($actionID)
    {
        return array_merge(parent::options($actionID), ['campaignToken','segments']);
    }

    /**
     * @param string $campaignToken
     *
     * генерирует рассылку из шаблона SandsCts
     */
    public function actionGenerateCts($campaignToken = '')
    {
        $this->generate(self::TEMPLATE_CTS_DAILY, $campaignToken);
    }

    /**
     * @param string $campaignToken
     *
     * генерирует рассылку из шаблона SandsCts учитывая сегменты
     */
    public function actionGenerateCtsBySegments($campaignToken = '')
    {
        $campaignToken = $this->campaignToken ?: $campaignToken;
        $this->generate(self::TEMPLATE_CTS_DAILY, $campaignToken, !empty($this->segments) ? $this->segments : []);
    }

    /**
     * @param string $campaignToken
     *
     * генерирует тестовую рассылку из шаблона SandsCts
     */
    public function actionGenerateCtsTest($campaignToken = 'C')
    {
        $this->isTest = true;
        $this->generate(self::TEMPLATE_CTS_DAILY, $campaignToken);
    }

    /**
     * @param string $campaignToken
     *
     * генерирует тестовую рассылку из шаблона SandsCts учитывая сегменты
     */
    public function actionGenerateCtsTestBySegments()
    {
        $this->isTest = true;
        $campaignToken = $this->campaignToken ?: 'C';
        $this->generate(self::TEMPLATE_CTS_DAILY, $campaignToken, !empty($this->segments) ? $this->segments : []);
    }

    /**генерирует тестовую рассылку из шаблона Simple учитывая сегменты
     */
    public function actionGenerateSimpleTestBySegments()
    {
        $this->isTest = true;
        $this->generate(self::TEMPLATE_COMMON, $this->campaignToken, !empty($this->segments) ? $this->segments : []);
    }

    //****************************************************************************************************
    /**
     * @param string $campaignToken
     *
     * генерирует рассылку из выбранного в TEMPLATE_COMMON шаблона
     */
    public function actionGenerateCommon($campaignToken = '')
    {
        $this->generate(self::TEMPLATE_COMMON, $campaignToken);
    }


    /**
     * @param string $campaignToken
     *
     * генерирует тестовую рассылку из шаблона TEMPLATE_COMMON
     */
    public function actionGenerateCommonTest($campaignToken = 'C')
    {
        $this->isTest = true;
        $this->generate(self::TEMPLATE_COMMON, $campaignToken);
    }

    //****************************************************************************************************
    /**
     * @param string $campaignToken
     *
     * генерирует рассылку из шаблона конструктора
     */
    public function actionGenerateConstructor($campaignToken = '')
    {
        $this->generate(self::TEMPLATE_CONSTRUCTOR, $campaignToken);
    }


    /**
     * @param string $campaignToken
     *
     * генерирует тестовую рассылку из шаблона конструктора
     */
    public function actionGenerateConstructorTest($campaignToken = 'C')
    {
        $this->isTest = true;
        $this->generate(self::TEMPLATE_CONSTRUCTOR, $campaignToken);
    }

    //****************************************************************************************************

    public function actionGenerateMonitoringYesterday()
    {
        $date = date('Y-m-d', strtotime('yesterday'));
        $this->generateMonitoring($date);
    }

    public function actionGenerateMonitoringToday()
    {
        $date = date('Y-m-d');
        $this->generateMonitoring($date);
    }

    //****************************************************************************************************

    /**
     * Отправляет рассылку по указанному шаблону на указанный токен
     *
     * @param string $mailTemplate - код шаблона
     * @param string $campaignToken - токен getresponse (по умолчанию берется боевой из конфига)
     * @param array $segments - массив названий сегментов рассылки
     */
    protected function generate($mailTemplate, $campaignToken = '', $segments = [])
    {
        /** @var MailTemplate[] $mailTemplates */
        $mailTemplates = MailTemplate::find()
            ->andWhere(['active' => Cms::BOOL_Y])
            ->andWhere(['template' => $mailTemplate])
            ->all();

        $this->stdout("Рассылка по шаблону: $mailTemplate \n", Console::FG_YELLOW);

        if (sizeof($mailTemplates) != 1) {
            $this->stdout('Шаблон с указанным именем на найден, или он неактивен', Console::FG_RED);
        }

        $this->stdout($this->ansiFormat("Внимание!", Console::FG_RED, Console::BOLD) . " Паслушай парень, если ты не прав, то еще есть время отменить рассылку!\n");
        $this->delay(10);

        foreach ($mailTemplates as $mailTemplate) {
            $this->stdout("\nGenerating {$mailTemplate->name}: ", Console::FG_YELLOW);

            try {
                $mailTemplate->mail_to = 'auto@getresponse.ru';
                $mailDispatch = $mailTemplate->generate(true);

                if ($this->isTest == true) {
                    $mailDispatch->subject = '[TEST] ' . $mailDispatch->subject;
                }

                $result = $this->send($mailDispatch, $campaignToken, $segments);

                if (isset($result['error'])){
                    $this->stdout($result['error'], Console::FG_RED);
                }else{
                    $this->stdout("\nLetter Id: " . $result['newsletterId'], Console::FG_YELLOW);
                    $this->stdout(' OK', Console::FG_GREEN);
                }

            } catch (\Exception $e) {
                $this->stdout($e->getMessage() . $e->getTraceAsString(), Console::FG_RED);
            }
        }
        $this->stdout("\ndone\n", Console::FG_YELLOW);
    }

    protected function send(MailDispatch $mailDispatch, $campaignToken = '', $segments = [])
    {
        $grClient = \Yii::$app->getResponseService;
        if (!empty($campaignToken)) {
            $grClient->setCampaignToken($campaignToken);
            echo "set campaign: {$campaignToken} \n";
        }

        $createNewsLetters = $grClient->sendMailDispatch($mailDispatch, $segments);

        if (is_array($createNewsLetters) && array_key_exists('error', $createNewsLetters)) {
            $mailDispatch->setStatus(MailDispatch::STATUS_CANCEL);
        } else {
            $mailDispatch->setStatus(MailDispatch::STATUS_SENT);
        }
        $mailDispatch->save();

        //\yii\helpers\VarDumper::dump($createNewsLetters);
        return $createNewsLetters;
    }

    protected function delay(int $sec)
    {
        $this->stdout("Sleeping for {$sec}sec. ", Console::FG_GREY);
        for ($k = 1; $k <= $sec; $k++) {
            $this->stdout("{$k}... ");
            sleep(1);
        }
        $this->stdout("\n");
    }

    //****************************************************************************************************

    protected function generateMonitoring($date = '')
    {
        $emails = [
//            'soskov_da@shopandshow.ru',
            'anisimov_da@shopandshow.ru',
//            'komarova_ae@shopandshow.ru',
            'selyansky@shopandshow.ru',
//            'panina_av@shopandshow.ru',
//            'fazilova_ev@shopandshow.ru',
            'gutorov_iv@shopandshow.ru',
            'savelyeva_av@shopandshow.ru',
        ];

        $model = new Plan(['date' => $date]);

        if (!$model) {
            $this->stdout("План на указанный день [{$date}] не найден");

            return false;
        }

        $monitoringDayHelper = new MonitoringDayHelper($model);
        if (!$monitoringDayHelper->needSendMail()) {
            return true;
        }

        // отпраляем Олегу, только если это обычная рассылка
        if ($monitoringDayHelper->isOkHour() && !$monitoringDayHelper->isPlanBad()) {
            $emails[] = 'vorobyev_oe@shopandshow.ru';
        }

        \Yii::$app->mailer->htmlLayout = false;
        \Yii::$app->mailer->textLayout = false;

        \Yii::$app->mailer->compose('@mail/modules/monitoringday/report', [
            'model' => $model
        ])
            ->setFrom([\Yii::$app->cms->adminEmail => \Yii::$app->cms->appName . ''])
            ->setTo($emails)
            ->setSubject('САЙТ: мониторинг продаж ' . $date . ' ' . date('H:i'))
            ->send();

    }

}