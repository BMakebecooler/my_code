<?php

/**
 * php ./yii test/gt-metrix/run
 */
namespace console\controllers\test;

use console\controllers\export\ExportController;
use common\components\gtmetrix\ServicesWTFTest;

/**
 * Class GtMetrixController
 * @package console\controllers
 */
class GtMetrixController extends ExportController
{

    protected function test($url)
    {
        $test = new ServicesWTFTest("reklama@shopandshow.ru", "7681689dada47b3d9c75a10ad6773f96", true);

        echo "Testing $url\n";

        $testid = $test->test(array(
            'url' => $url
        ));

        if ($testid) {
            echo "Test started with $testid\n";
        } else {
            die("Test failed: " . $test->error() . "\n");
        }

        echo "Waiting for test to finish\n";

        $test->get_results();

        if ($test->error()) {
            die($test->error());
        }

        return $test;
    }

    public function actionRun()
    {

        $tests = [
            'Главная' => $this->test('https://shopandshow.ru/'),
            'Каталог' => $this->test('https://shopandshow.ru/catalog/moda/'),
            'Карточка товара' => $this->test('https://shopandshow.ru/catalog/moda/platya/1019944-4988132-004-988-132/'),
            'Сегодня в эфире' => $this->test('https://shopandshow.ru/onair/'),
//            'Пустая страница' => $this->test('https://shopandshow.ru/slujebnoe/pustaya-stranitsa-dlya-testirovaniya-otveta-servera/'),
        ];


//        $this->sendEmailTest($tests);
        $this->runTest($tests);


        /* Sample output:

Testing https://shopandshow.ru/
Test started with ZNnfmMCf
Waiting for test to finish
Test completed succesfully with ID ZNnfmMCf

  Время загрузки => 8113
  first contentful paint time => 3136
  page elements => 259
  report url => https://gtmetrix.com/reports/shopandshow.ru/S2Ymv5mq
  redirect duration => 0
  first paint time => 3136
  dom content loaded duration =>
  dom content loaded time => 5816
  dom interactive time => 5816
  page bytes => 3235399
  page load time => 8113
  html bytes => 30030
  fully loaded time => 10257
  Время загрузки html => 1315
  rum speed index => 3729
  оценка yslow => 48
  pagespeed score => 44
  backend duration => 638
  onload duration => 60
  Соединение => 677

Resources
  Resource: report_pdf https://gtmetrix.com/api/0.1/test/ZNnfmMCf/report-pdf
  Resource: pagespeed https://gtmetrix.com/api/0.1/test/ZNnfmMCf/pagespeed
  Resource: har https://gtmetrix.com/api/0.1/test/ZNnfmMCf/har
  Resource: pagespeed_files https://gtmetrix.com/api/0.1/test/ZNnfmMCf/pagespeed-files
  Resource: report_pdf_full https://gtmetrix.com/api/0.1/test/ZNnfmMCf/report-pdf?full=1
  Resource: yslow https://gtmetrix.com/api/0.1/test/ZNnfmMCf/yslow
  Resource: screenshot https://gtmetrix.com/api/0.1/test/ZNnfmMCf/screenshot
Loading test id ZNnfmMCf
Deleting test id ZNnfmMCf

Locations GTmetrix can test from:
GTmetrix can run tests from: Vancouver, Canada using id: 1 default (1)
GTmetrix can run tests from: London, UK using id: 2 default ()
GTmetrix can run tests from: Sydney, Australia using id: 3 default ()
GTmetrix can run tests from: Dallas, USA using id: 4 default ()
GTmetrix can run tests from: Mumbai, India using id: 5 default ()
GTmetrix can run tests from: São Paulo, Brazil using id: 6 default ()
GTmetrix can run tests from: Hong Kong, China using id: 7 default ()


        */
    }

    private function runTest(array $tests)
    {
        /**
         * @var ServicesWTFTest $test
         */
        foreach ($tests as $test) {
            $test->get_test_id();
            $test->results();
        }
    }


    /**
     * @param array $tests
     * @return bool
     */
    private function sendEmailTest(array $tests)
    {

        try {

            \Yii::$app->mailer->htmlLayout = false;
            \Yii::$app->mailer->textLayout = false;

            $emails = [
                'anisimov_da@shopandshow.ru',
                'selyansky@shopandshow.ru',
                'gutorov_iv@shopandshow.ru',
            ];

            $message = \Yii::$app->mailer->compose('@templates/mail/developers/gt-metrix', [
                'tests' => $tests,
            ])
                ->setFrom([\Yii::$app->cms->adminEmail => \Yii::$app->cms->appName])
                ->setTo($emails)
                ->setSubject(sprintf('Отчет GtMetrix за %s', date('Y-m-d H:i:s')));

            $result = $message->send();

            return $result;

        } catch (\Exception $exception) {
            var_dump($exception->getMessage());
        }
    }

}



