<?php
namespace modules\shopandshow\controllers;

use common\helpers\Dates;
use common\models\cmsContent\ContentElementFaq;
use modules\shopandshow\models\shares\SharesStat;
use modules\shopandshow\models\statistic\PassTime;
use modules\shopandshow\models\statistic\ProductRange;
use modules\shopandshow\models\statistic\SalesFunnelByStatus;
use modules\shopandshow\models\statistic\StatisticsForm;
use modules\shopandshow\models\statistic\UserStatistics;
use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use skeeks\cms\modules\admin\traits\AdminModelEditorStandartControllerTrait;
use Yii;
use yii\helpers\ArrayHelper;
use modules\shopandshow\models\statistic\Statistics;
use modules\shopandshow\models\users\UserEmailSearch;
use yii\web\Response;

/**
 * Class StatisticsController
 * @package modules\shopandshow\controllers
 */
class StatisticsController extends AdminModelEditorController
{
    use AdminModelEditorStandartControllerTrait;

    public function init()
    {
        $this->modelClassName = ContentElementFaq::className();
        $this->name = 'Статистика';
        $this->modelShowAttribute = "id";

        parent::init();
    }

    public function actions()
    {
        $actions = parent::actions();
        ArrayHelper::remove($actions, 'create');
        return $actions;
    }

    public function actionYesterdayTop()
    {
        $this->name = 'Топ-50 продаж за вчерашний день';

        $dataProvider = Statistics::getYesterdayTopData();

        return $this->render('yesterday_top', ['dataProvider' => $dataProvider]);
    }

    /**
     * Логи брошенных корзин
     * @return string
     */
    public function actionAbandonedBaskets()
    {
        $this->name = 'Логи брошенных корзин';

        $dataProviderAllLogs = Statistics::getAbandonedData();
        $dataProviderReport = Statistics::getAbandonedDataReport();

        return $this->render('abandoned_baskets', [
            'dataProviderAllLogs' => $dataProviderAllLogs,
            'dataProviderReport' => $dataProviderReport,
        ]);
    }

    /**
     * Ссылка на аналитическую систему за которую Анисимов "получит нобелевскую премию ШШ" (Олег)
     * p.s. и проставится разработчикам (разработчики)
     * @return string
     */
    public function actionRealtimeEfir()
    {
        set_time_limit(600);

        $this->name = 'RealTime аналитика эфира (ПЭЧ таблица)';

        $searchDate = \Yii::$app->request->get('searchdate') ?: time();

        $dataProvider = Statistics::getRealtimeEfirData($searchDate);

        return $this->render('realtime_efir', ['dataProvider' => $dataProvider, 'searchDate' => $searchDate]);
    }

    public function actionRealtimeEfirDetail()
    {
        $url = \skeeks\cms\helpers\UrlHelper::construct(['statistics/realtime-efir'])
            ->enableAdmin()
            ->normalizeCurrentRoute()->toString();

        $this->name = '<a href="' . $url . '" style="color: white; ">&xlarr; На страницу с лотами дня</a> &gt; RealTime аналитика эфира (ПЭЧ текущий лот)';

        $statisticsForm = new StatisticsForm();
        $statisticsForm->load(\Yii::$app->request->get());
        $statisticsForm->init();

        $dataProvider = Statistics::getRealtimeEfirData($statisticsForm->timestamp);

        return $this->render('realtime_efir_detail', ['dataProvider' => $dataProvider, 'model' => $statisticsForm]);
    }

    public function actionCouponsPromos()
    {
        $this->name = "Статистика продаж по купонным акциям";

        //Получим список все промоакций в условиях которых есть промо код
        $couponsPromosData = Statistics::getCouponsPromosData();

        return $this->render('coupons_promos', ['dataProvider' => $couponsPromosData]);
    }

    /**
     * Статистика по баннерам (просмотры, клики, продажи...)
     *
     * Для альтернативного варианта отображения добавить в url ?v=2
     * @return string
     */
    public function actionBanners()
    {
        $this->name = "Статистика кликабельности баннеров";

        $model = new SharesStat();

        if (\Yii::$app->request->isPost) {
            if (!$model->load(\Yii::$app->request->post())) {
                return print_r($model->getErrors(), true);
            } else {
                $model->time = Dates::beginEfirPeriod(strtotime($model->date));
            }
        }

        $viewFile = Yii::$app->request->get('v') ? 'banners' : 'banners2';

        return $this->render('shares/daily/' . $viewFile, ['model' => $model]);
    }

    /**
     * Статистика по баннерам (просмотры, клики, продажи...) в разрезе их типов и периодов
     *
     * @return string
     */
    public function actionBannersByType()
    {
        $this->name = "Статистика по типам баннеров";

        $model = new SharesStat();

        if (\Yii::$app->request->isPost) {
            if (!$model->load(\Yii::$app->request->post())) {
                return print_r($model->getErrors(), true);
            } else {
                $model->timeFrom = Dates::beginEfirPeriod(strtotime($model->dateFrom));
                $model->timeTo = Dates::endEfirPeriod(strtotime($model->dateTo)); //06:59:59 следующего дня
            }
        }
        return $this->render('shares/banners_by_type', ['model' => $model]);
    }

    /**
     * Мониторинг подписчиков - когда, кто и откуда попал (общий список)
     *
     * @return string
     */
    public function actionSubscribersMonitoring()
    {
        $this->name = "Мониторинг подписчиков";

        $searchModel = new UserEmailSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        return $this->render('subscribers_monitoring', ['searchModel' => $searchModel, 'dataProvider' => $dataProvider]);
    }

    /**
     * Мониторинг подписчиков - когда, кто и откуда попал (группированно по источникам) + валидность мыл
     *
     * @return string
     */
    public function actionSubscribersReport()
    {
        $this->name = "Мониторинг подписчиков - отчет";
        $model = new Statistics();

        if (\Yii::$app->request->isPost) {
            if (!$model->load(\Yii::$app->request->post())) {
                return print_r($model->getErrors(), true);
            }
        }

        return $this->render('subscribers_report', ['model' => $model]);
    }

    /**
     * Статистика по времени авторизации после запроса пароля по смс
     * @return mixed|string
     */
    public function actionAuthPassTime()
    {
        $model = new PassTime();

        if (\Yii::$app->request->isPost) {
            if (!\Yii::$app->request->post('ajax')) {
                if (!$model->load(\Yii::$app->request->post())) {
                    return print_r($model->getErrors(), true);
                }
            }
        }

        return $this->render('auth_pass_time', [
            'model' => $model,
        ]);
    }

    /**
     * Статистика по ранжированию товаров
     * @return mixed|string
     */
    public function actionProductRange()
    {
        Yii::$app->response->format = Response::FORMAT_RAW;

        $model = new ProductRange();

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="range-products.csv"');

        echo $model->getDataForExportCsv();
    }

    /**
     * Статистика по воронке заказов в разрезе их статусов
     * @return mixed|string
     */
    public function actionSalesFunnelByStatus()
    {
        $model = new SalesFunnelByStatus();

        if (\Yii::$app->request->isPost) {
            if (!\Yii::$app->request->post('ajax')) {
                if (!$model->load(\Yii::$app->request->post())) {
                    return print_r($model->getErrors(), true);
                }
            }
        }

        return $this->render('sales_funnel_by_status', [
            'model' => $model,
        ]);
    }

    /**
     * Статистика по выкупу заказов клиентами
     * @return mixed|string
     */
    public function actionUserCompleteOrders()
    {
        $model = new UserStatistics();

        if ((!empty(\Yii::$app->request->get('show')) || !empty(\Yii::$app->request->get('export')))
            && !$model->load(\Yii::$app->request->get())
        ) {
            return print_r($model->getErrors(), true);
        }

        if (!empty(\Yii::$app->request->get('export'))) {
            $dataProvider = $model->getOrdersByStatusData();
            $model->exportOrdersComplete($dataProvider);
        }

        return $this->render('users_complete_orders', [
            'model' => $model,
        ]);
    }
}