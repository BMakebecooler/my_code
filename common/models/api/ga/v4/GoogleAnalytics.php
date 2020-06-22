<?php

/**
 * Модель для работы с Google Analytics
 */

namespace common\models\api\ga\v4;

class GoogleAnalytics
{

    //* GA API v4 *//

    const VERSION = 'v4';

    public $date;

    public function __construct($date = '')
    {
        $this->date = $date;
    }

    /**
     * Получение JSON'а с данными аналитики на указанную дату
     *
     * @return array|json
     */
    public function getData()
    {
        $this->date = !empty($_REQUEST['date']) ? $_REQUEST['date'] : date("Y-m-d");

        return $this->getAnalytics();
    }

    public function getSessions()
    {
        if (empty($this->date)) {
            $this->date = date('Y-m-d');
        }

        //Инициируем подключение к сервису аналитики
        $analytics = $this->initializeAnalytics();
        //Получаем данные самой аналитики
        $response = $this->getSessionsReport($analytics);

        //Для совместимости необходимо переформатировать формат данных под старую версию репортинга (v3)
        //Что бы не пришлось переделывать еще и механизм обработки данных в отчет
        $analyticsData = $this->prepareResults($response, ['sessions']);

        return $analyticsData;
    }

    /**
     * Центральный метод подготовки данных аналитики
     *
     * @return array
     */
    private function getAnalytics()
    {
        //Инициируем подключение к сервису аналитики
        $analytics = $this->initializeAnalytics();
        //Получаем данные самой аналитики
        $response = $this->getReport($analytics);

        //Для совместимости необходимо переформатировать формат данных под старую версию репортинга (v3)
        //Что бы не пришлось переделывать еще и механизм обработки данных в отчет
        $analyticsData = $this->prepareResults($response, ['visitors', 'purchases']);

        return $analyticsData;
    }

    /**
     * Инициализация, подключение к GA
     *
     * @return \Google_Service_AnalyticsReporting
     */
    private function initializeAnalytics()
    {
        // Creates and returns the Analytics Reporting service object.

        // Use the developers console and download your service account
        // credentials in JSON format. Place them in this directory or
        // change the key file location if necessary.
        //$KEY_FILE_LOCATION = __DIR__ . '/service-account-credentials.json';
        $KEY_FILE_LOCATION = __DIR__ . '/SandS-ba9514b064f2.json';

        // Create and configure a new client object.
        $client = new \Google_Client();
        $client->setApplicationName("SandS Analytics Reporting");
        $client->setAuthConfig($KEY_FILE_LOCATION);
        $client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
        $analytics = new \Google_Service_AnalyticsReporting($client);

        return $analytics;
    }

    /**
     * Непосредственно получение данных GA
     *
     * @param $analytics
     * @return mixed
     */
    private function getReport($analytics)
    {

        // Replace with your view ID, for example XXXX.
        //$VIEW_ID = "<REPLACE_WITH_VIEW_ID>";
        $VIEW_ID = "151138637"; //представление

        // Create the DateRange object.
        $dateRange = new \Google_Service_AnalyticsReporting_DateRange();
        //$dateRange->setStartDate("30daysAgo");
        $dateRange->setStartDate($this->date);
        //$dateRange->setEndDate("today");
        $dateRange->setEndDate($this->date);

        //* Metrics *//

        $metricsUsers = new \Google_Service_AnalyticsReporting_Metric();
        $metricsUsers->setExpression("ga:users");
        //$metricsUsers->setAlias("users");

        $uniquePurchases = new \Google_Service_AnalyticsReporting_Metric();
        $uniquePurchases->setExpression("ga:uniquePurchases");
        //$uniquePurchases->setAlias("purchases");

        $itemRevenue = new \Google_Service_AnalyticsReporting_Metric();
        $itemRevenue->setExpression("ga:itemRevenue");
        //$itemRevenue->setAlias("itemRevenue");

        //* /Metrics *//

        //* Dimensions *//

        $dimensionSource = new \Google_Service_AnalyticsReporting_Dimension();
        $dimensionSource->setName('ga:source');

        $dimensionMedium = new \Google_Service_AnalyticsReporting_Dimension();
        $dimensionMedium->setName('ga:medium');

        $dimensionCampaign = new \Google_Service_AnalyticsReporting_Dimension();
        $dimensionCampaign->setName('ga:campaign');

        $dimensionProductCategory = new \Google_Service_AnalyticsReporting_Dimension();
        $dimensionProductCategory->setName('ga:productCategory');

        //* /Dimensions *//

        // Create the ReportRequest object.

        //Visitors
        $requestUsers = new \Google_Service_AnalyticsReporting_ReportRequest();
        $requestUsers->setViewId($VIEW_ID);
        $requestUsers->setDateRanges($dateRange);
        $requestUsers->setMetrics(array($metricsUsers));
        $requestUsers->setDimensions(array($dimensionSource, $dimensionMedium, $dimensionCampaign));

        //Purchases
        $requestPurchases = new \Google_Service_AnalyticsReporting_ReportRequest();
        $requestPurchases->setViewId($VIEW_ID);
        $requestPurchases->setDateRanges($dateRange);
        $requestPurchases->setMetrics(array($uniquePurchases, $itemRevenue));
        $requestPurchases->setDimensions(array($dimensionSource, $dimensionMedium, $dimensionCampaign, $dimensionProductCategory));

        $body = new \Google_Service_AnalyticsReporting_GetReportsRequest();
        $body->setReportRequests(array($requestUsers, $requestPurchases));
        return $analytics->reports->batchGet($body);
    }

    /**
     * Непосредственно получение данных GA
     *
     * @param $analytics
     * @return mixed
     */
    private function getSessionsReport($analytics)
    {

        // Replace with your view ID, for example XXXX.
        //$VIEW_ID = "<REPLACE_WITH_VIEW_ID>";
        $VIEW_ID = "170503334"; //представление Medianation - рабочее представление

        // Create the DateRange object.
        $dateRange = new \Google_Service_AnalyticsReporting_DateRange();
        //$dateRange->setStartDate("30daysAgo");
        $dateRange->setStartDate($this->date);
        //$dateRange->setEndDate("today");
        $dateRange->setEndDate($this->date);

        //* Metrics *//

        $metricsSessions = new \Google_Service_AnalyticsReporting_Metric();
        $metricsSessions->setExpression("ga:sessions");
        //$metricsSessions->setAlias("sessions");

        //* /Metrics *//

        //* Dimensions *//

        $dimensionDateHourMinute = new \Google_Service_AnalyticsReporting_Dimension();
        $dimensionDateHourMinute->setName('ga:hour');

        //* /Dimensions *//

        // Create the ReportRequest object.

        //Sessions
        $requestSessions = new \Google_Service_AnalyticsReporting_ReportRequest();
        $requestSessions->setViewId($VIEW_ID);
        $requestSessions->setDateRanges($dateRange);
        $requestSessions->setMetrics(array($metricsSessions));
        $requestSessions->setDimensions(array($dimensionDateHourMinute));

        $body = new \Google_Service_AnalyticsReporting_GetReportsRequest();
        $body->setReportRequests(array($requestSessions));
        return $analytics->reports->batchGet($body);
    }

    /**
     * Форматирование даных GA в формат понятный для уже реализованного отчета в Excell
     *
     * @param array $reports
     * @param array $fields
     * @return array
     */
    private function prepareResults($reports, $fields = [])
    {
        $arAnalytics = [];
        foreach ($fields as $field) {
            $arAnalytics[$field] = [
                'columnHeaders' => [],
                'dataTable' => [
                    'rows' => []
                ]
            ];
        }

        for ($reportIndex = 0; $reportIndex < count($reports); $reportIndex++) {
            $reportSlug = $fields[$reportIndex] ?? null;
            if($reportSlug == null) {
                die('Unknown report type');
            }

            $report = $reports[$reportIndex];
            $header = $report->getColumnHeader();
            $dimensionHeaders = $header->getDimensions();
            $metricHeaders = $header->getMetricHeader()->getMetricHeaderEntries();
            $rows = $report->getData()->getRows();

            if ($dimensionHeaders) {
                for ($i = 0; $i < count($dimensionHeaders); $i++) {
                    $arAnalytics[$reportSlug]['columnHeaders'][] = array(
                        'name' => $dimensionHeaders[$i],
                        'columnType' => 'DIMENSION',
                        'dataType' => 'STRING'
                    );
                }
            }

            if ($metricHeaders) {
                for ($j = 0; $j < count($metricHeaders); $j++) {
                    $entry = $metricHeaders[$j];

                    $arAnalytics[$reportSlug]['columnHeaders'][] = array(
                        'name' => $entry->getName(),
                        'columnType' => 'METRIC',
                        'dataType' => $entry->getType()
                    );
                }
            }

            for ($rowIndex = 0; $rowIndex < count($rows); $rowIndex++) {

                $row = $rows[$rowIndex];
                $dimensions = $row->getDimensions();
                $metrics = $row->getMetrics();

                $arAnalytics[$reportSlug]['dataTable']['rows'][$rowIndex]['c'] = array();
                for ($i = 0; $i < count($dimensionHeaders) && $i < count($dimensions); $i++) {
                    $arAnalytics[$reportSlug]['dataTable']['rows'][$rowIndex]['c'][]['v'] = $dimensions[$i];
                }

                for ($j = 0; $j < count($metricHeaders) && $j < count($metrics); $j++) {
                    $entry = $metricHeaders[$j];
                    $values = $metrics[$j];

                    for ($valueIndex = 0; $valueIndex < count($values->getValues()); $valueIndex++) {
                        $value = $values->getValues()[$valueIndex];
                        $arAnalytics[$reportSlug]['dataTable']['rows'][$rowIndex]['c'][]['v'] = $value;
                        //print($entry->getName() . ": " . $value . "<br>");
                    }
                }
            }
        }

        return $arAnalytics;
    }
}