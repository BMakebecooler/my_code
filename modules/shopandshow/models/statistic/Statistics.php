<?php
namespace modules\shopandshow\models\statistic;

use common\helpers\ArrayHelper;
use common\helpers\Dates;
use common\models\user\User;
use modules\shopandshow\models\shares\SsShare;
use modules\shopandshow\models\shares\SsShareSchedule;
use modules\shopandshow\models\shares\SsShareSeller;
use modules\shopandshow\models\shop\ShopBasket;
use modules\shopandshow\models\shop\ShopDiscount;
use modules\shopandshow\models\shop\ShopDiscountCoupon;
use modules\shopandshow\models\users\UserEmail;
use skeeks\cms\components\Cms;
use skeeks\cms\models\StorageFile;
use Yii;
use yii\base\Model;
use yii\data\ArrayDataProvider;
use yii\data\SqlDataProvider;

class Statistics extends Model
{
    const GETRESPONSE_SEGMENT_PHONE                 = 'callcenter';
    const GETRESPONSE_SEGMENT_SITE_ALL              = 'source_site_all';
    const GETRESPONSE_SEGMENT_SITE_REG_DESKTOP      = 'source_register';
    const GETRESPONSE_SEGMENT_SITE_REG_MOBILE       = 'source_register_mobile';
    const GETRESPONSE_SEGMENT_SITE_CHECKOUT         = 'source_checkout_desktop';
    const GETRESPONSE_SEGMENT_SITE_CHECKOUT_MOBILE  = 'source_checkout_mobile';
    const GETRESPONSE_SEGMENT_PROMOCODE_DESKTOP     = 'source_promocode_desktop';
    const GETRESPONSE_SEGMENT_PROMOCODE_MOBILE      = 'source_promocode_mobile';
    const GETRESPONSE_SEGMENT_FORM_DESKTOP          = 'source_form_desktop';

    public $dateTo;
    public $dateFrom;

    public function init()
    {

        if (!$this->dateFrom){
            $this->dateFrom = date('Y-m-d', time() - DAYS_7);
        }

        if (!$this->dateTo){
            $this->dateTo = date('Y-m-d');
        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['dateTo', 'dateFrom'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'dateFrom' => 'Дата С',
            'dateTo' => 'Дата По',
        ];
    }

    public static function getYesterdayTopData()
    {
        $dataProvider = new SqlDataProvider([
            'sql' => StatisticsQuery::getYesterdayTopQuery(),
            'params' => [
                ':begin_datetime' => Dates::beginOfDate(strtotime('yesterday')),
                ':end_datetime' => Dates::endOfDate(strtotime('yesterday')),
            ],
//            'totalCount' => $totalCount,
            'pagination' => false,
            'sort' => false,
        ]);

        return $dataProvider;
    }

    public static function getRealtimeEfirData($searchDate = null)
    {
        $dataProvider = new SqlDataProvider([
            'sql' => StatisticsQuery::getRealtimeEfirQuery(),
            'params' => [
                ':begin_datetime' => Dates::beginOfDate($searchDate),
                ':end_datetime' => Dates::endOfDate($searchDate),
            ],
//            'totalCount' => $totalCount,
            'pagination' => false,
            'sort' => false,
        ]);

        return $dataProvider;
    }

    public static function getCountViewedData($productId, $searchDate = null, $interval = 600)
    {
        $dataProvider = new SqlDataProvider([
            'sql' => StatisticsQuery::getCountViewedQuery($interval),
            'params' => [
                ':begin_datetime' => Dates::beginOfDate($searchDate),
                ':end_datetime' => Dates::endOfDate($searchDate),
                ':product_id' => $productId
            ],
//            'totalCount' => $totalCount,
            'pagination' => false,
            'sort' => false,
        ]);

        return $dataProvider;
    }

    public static function getCountViewedSummaryData($productId, $searchDate = null, $interval = 600)
    {
        $dataProvider = new SqlDataProvider([
            'sql' => StatisticsQuery::getCountViewedQuery($interval), //StatisticsQuery::getCountViewedSummaryQuery($interval),
            'params' => [
                ':begin_datetime' => Dates::beginOfDate($searchDate),
                ':end_datetime' => Dates::endOfDate($searchDate),
                ':product_id' => $productId
            ],
//            'totalCount' => $totalCount,
            'pagination' => false,
            'sort' => false,
        ]);

        //return $dataProvider;
        $y = 0;
        $result = [];
        foreach ($dataProvider->getModels() as $row) {
            $x = $row['x'];
            $y += $row['y'];
            $result[] = ['x' => $x, 'y' => $y];
        }

        return $result;
    }

    public static function getCountBasketData($productId, $searchDate = null, $interval = 600)
    {
        $dataProvider = new SqlDataProvider([
            'sql' => StatisticsQuery::getCountBasketQuery($interval),
            'params' => [
                ':begin_datetime' => Dates::beginOfDate($searchDate),
                ':end_datetime' => Dates::endOfDate($searchDate),
                ':product_id' => $productId
            ],
//            'totalCount' => $totalCount,
            'pagination' => false,
            'sort' => false,
        ]);

        return $dataProvider;
    }

    public static function getCountBasketSummaryData($productId, $searchDate = null, $interval = 600)
    {
        $dataProvider = new SqlDataProvider([
            'sql' => StatisticsQuery::getCountBasketQuery($interval), //StatisticsQuery::getCountBasketSummaryQuery($interval),
            'params' => [
                ':begin_datetime' => Dates::beginOfDate($searchDate),
                ':end_datetime' => Dates::endOfDate($searchDate),
                ':product_id' => $productId
            ],
//            'totalCount' => $totalCount,
            'pagination' => false,
            'sort' => false,
        ]);

        //return $dataProvider;
        $y = 0;
        $result = [];
        foreach ($dataProvider->getModels() as $row) {
            $x = $row['x'];
            $y += $row['y'];
            $result[] = ['x' => $x, 'y' => $y];
        }

        return $result;
    }

    public static function getBasketConvercyData($productId, $searchDate = null, $interval = 600)
    {
        $dataProvider = new SqlDataProvider([
            'sql' => StatisticsQuery::getBasketConvercyQuery($interval),
            'params' => [
                ':begin_datetime' => Dates::beginOfDate($searchDate),
                ':end_datetime' => Dates::endOfDate($searchDate),
                ':product_id' => $productId
            ],
//            'totalCount' => $totalCount,
            'pagination' => false,
            'sort' => false,
        ]);

        return $dataProvider;
    }

    public static function getBasketConvercySummaryData($productId, $searchDate = null, $interval = 600)
    {
        $result = [];

        $countViewedSummaryData = self::getCountViewedSummaryData($productId, $searchDate, $interval);
        if (!$countViewedSummaryData) {
            return $result;
        }
        $minX = $countViewedSummaryData[0]['x'];

        $countBasketSummaryData = self::getCountBasketSummaryData($productId, $searchDate, $interval);
        $countBasketSummaryData = ArrayHelper::map($countBasketSummaryData, 'x', 'y');

        foreach($countViewedSummaryData as $row) {
            $x = $row['x'];
            // ищем ближайшее значение добавлений в корзину
            do {
                $y = isset($countBasketSummaryData[$x]) ? $countBasketSummaryData[$x] : -1;
                $x -= $interval;
            } while ($y < 0 && $x >= $minX);

            // так и не нашли, ставим 0
            if ($y < 0) {
                $y = 0;
            }

            // считаем конверсию (в %, поэтому умножаем на 100)
            $y = round($y / $row['y'], 2) * 100;

            $result[] = ['x' => $row['x'], 'y' => $y];
        }

        return $result;
    }

    public static function getCountOrderData($productId, $searchDate = null, $interval = 600)
    {
        $dataProvider = new SqlDataProvider([
            'sql' => StatisticsQuery::getCountOrderQuery($interval),
            'params' => [
                ':begin_datetime' => Dates::beginOfDate($searchDate),
                ':end_datetime' => Dates::endOfDate($searchDate),
                ':product_id' => $productId
            ],
//            'totalCount' => $totalCount,
            'pagination' => false,
            'sort' => false,
        ]);

        return $dataProvider;
    }

    public static function getCountOrderSummaryData($productId, $searchDate = null, $interval = 600)
    {
        $dataProvider = new SqlDataProvider([
            'sql' => StatisticsQuery::getCountOrderQuery($interval), //StatisticsQuery::getCountBasketSummaryQuery($interval),
            'params' => [
                ':begin_datetime' => Dates::beginOfDate($searchDate),
                ':end_datetime' => Dates::endOfDate($searchDate),
                ':product_id' => $productId
            ],
//            'totalCount' => $totalCount,
            'pagination' => false,
            'sort' => false,
        ]);

        //return $dataProvider;
        $y = 0;
        $result = [];
        foreach ($dataProvider->getModels() as $row) {
            $x = $row['x'];
            $y += $row['y'];
            $result[] = ['x' => $x, 'y' => $y];
        }

        return $result;
    }

    public static function getOrderConvercyData($productId, $searchDate = null, $interval = 600)
    {
        $dataProvider = new SqlDataProvider([
            'sql' => StatisticsQuery::getOrderConvercyQuery($interval),
            'params' => [
                ':begin_datetime' => Dates::beginOfDate($searchDate),
                ':end_datetime' => Dates::endOfDate($searchDate),
                ':product_id' => $productId
            ],
//            'totalCount' => $totalCount,
            'pagination' => false,
            'sort' => false,
        ]);

        return $dataProvider;
    }

    public static function getOrderConvercySummaryData($productId, $searchDate = null, $interval = 600)
    {
        $result = [];

        $countViewedSummaryData = self::getCountViewedSummaryData($productId, $searchDate, $interval);
        if (!$countViewedSummaryData) {
            return $result;
        }
        $minX = $countViewedSummaryData[0]['x'];

        $countBasketSummaryData = self::getCountOrderSummaryData($productId, $searchDate, $interval);
        $countBasketSummaryData = ArrayHelper::map($countBasketSummaryData, 'x', 'y');

        foreach($countViewedSummaryData as $row) {
            $x = $row['x'];
            // ищем ближайшее значение добавлений в корзину
            do {
                $y = isset($countBasketSummaryData[$x]) ? $countBasketSummaryData[$x] : -1;
                $x -= $interval;
            } while ($y < 0 && $x >= $minX);

            // так и не нашли, ставим 0
            if ($y < 0) {
                $y = 0;
            }

            // считаем конверсию (в %, поэтому умножаем на 100)
            $y = round($y / $row['y'], 2) * 100;

            $result[] = ['x' => $row['x'], 'y' => $y];
        }

        return $result;
    }

    public static function getBasketAvgConvercy(\yii\data\DataProviderInterface $dataProvider)
    {
        $curtime = time();
        $avgConvercy = 0;
        $count = 0;
        $models = $dataProvider->getModels();

        foreach ($models as $data) {
            $realTime = $data['begin_datetime'];
            /*if ($data['begin_datetime'] != $data['end_datetime']) {
                $realTime = $data['begin_datetime'] - date('Z');
            }*/
            if ($curtime < $realTime) {
                break;
            }
            $avgConvercy += $data['convercy_add_basket_day'];
            $count++;
        }
        if ($count) {
            $avgConvercy = $avgConvercy / $count;
        }

        return $avgConvercy;
    }

    public static function getOrderAvgConvercy(\yii\data\DataProviderInterface $dataProvider)
    {
        $curtime = time();
        $avgConvercy = 0;
        $count = 0;
        $models = $dataProvider->getModels();

        foreach ($models as $data) {
            $realTime = $data['begin_datetime'];
            /*if ($data['begin_datetime'] != $data['end_datetime']) {
                $realTime = $data['begin_datetime'] - date('Z');
            }*/
            if ($curtime < $realTime) {
                break;
            }
            $avgConvercy += $data['convercy_add_order_day'];
            $count++;
        }
        if ($count) {
            $avgConvercy = $avgConvercy / $count;
        }

        return $avgConvercy;
    }

    public static function getEfirTotal(\yii\data\DataProviderInterface $dataProvider, $searchDate)
    {
        if (!\Yii::$app->has('front_db')) {
            return [];
        }

        $models = $dataProvider->getModels();

        $params = [];

        $lotsIds = array_column($models, 'product_id');
        $condition = \Yii::$app->db->getQueryBuilder()->buildCondition(['IN', 'cce.id', $lotsIds], $params);
        $query = StatisticsQuery::getEfirTotalQuery($condition);

        $params[':begin_datetime'] = date('Y-m-d 00:00:00', $searchDate);
        $params[':end_datetime'] = date('Y-m-d 23:59:59', $searchDate);

        $result = \Yii::$app->db->createCommand($query, $params)->queryAll();

        return ArrayHelper::map($result, 'id', 'sum_efir');
    }

    /**
     * Брошенные корзины
     * @return SqlDataProvider
     */
    public static function getAbandonedData()
    {
        $dataProvider = new SqlDataProvider([
            'sql' => StatisticsQuery::getAbandonedBasketQuery(),
            'pagination' => [
                'pageSize' => 50,
            ],
            'sort' => false,
        ]);

        return $dataProvider;
    }

    /**
     * Брошенные корзины - отчет
     * @return SqlDataProvider
     */
    public static function getAbandonedDataReport()
    {
        $dataProvider = new SqlDataProvider([
            'sql' => StatisticsQuery::getAbandonedBasketReportQuery(),
            'pagination' => [
                'pageSize' => 50,
            ],
            'sort' => false,
        ]);

        return $dataProvider;
    }

    public static function getCouponsPromosData(){

        $query = ShopDiscount::find()
            ->alias('shop_discount')
            ->select([
                'shop_discount.id',
                'shop_discount.name',
                'shop_discount.code',
                'shop_discount.active_from',
                'shop_discount.active_to',
                'COUNT(1) AS coupons_num',
                'GROUP_CONCAT(discount_coupon.coupon SEPARATOR ", ") AS coupons',
                'SUM((
                SELECT COUNT(*) AS num 
                FROM shop_order2discount_coupon AS coupons_orders 
                WHERE coupons_orders.discount_coupon_id = discount_coupon.id
                )) AS orders_num',
                'SUM((
           SELECT SUM(shop_order.price) AS orders_price
           FROM shop_order2discount_coupon AS coupons_orders
             INNER JOIN shop_order ON coupons_orders.order_id = shop_order.id
           WHERE coupons_orders.discount_coupon_id = discount_coupon.id
         )) AS orders_price'
            ])
            ->leftJoin('ss_shop_discount_entity AS discount_entity', "discount_entity.class='ForPromoCode'")
            ->leftJoin("ss_shop_discount_configuration AS discount_config", "discount_config.shop_discount_entity_id=discount_entity.id")
            ->leftJoin(ShopDiscountCoupon::tableName().' AS discount_coupon', "discount_coupon.shop_discount_id = shop_discount.id")
            ->where('shop_discount.id=discount_config.shop_discount_id')
            ->groupBy('shop_discount.id');

        $queryCount = ShopDiscount::find()
            ->alias('shop_discount')
            ->select(['id'])
            ->leftJoin('ss_shop_discount_entity AS discount_entity', "discount_entity.class='ForPromoCode'")
            ->leftJoin("ss_shop_discount_configuration AS discount_config", "discount_config.shop_discount_entity_id=discount_entity.id")
            ->where('shop_discount.id=discount_config.shop_discount_id');

        return new SqlDataProvider([
            'totalCount'    => $queryCount->count(),
            'sql' => $query->createCommand()->getRawSql(),
            'pagination'    => [
                'pageSize' => 50
            ]
        ]);
    }

    /**
     * Подготовка данных для отчета по пользователям/подписчикам
     *
     * @return ArrayDataProvider
     */
    public function getSubscribersReportDataProvider(){

        $timeFrom = strtotime($this->dateFrom . ' 00:00:00');
        $timeTo = strtotime($this->dateTo . ' 23:59:59');

        //* МЫЛА из GetResponse *//

        $grClient = \Yii::$app->getResponseService;

        $grSubscribersOptions = [
            'dateFrom'  => $this->dateFrom,
            'dateTo'    => $this->dateTo,
        ];

        //Пока гР отключаем до лучшей поры
        $grSubscribers = [
            'phone'                     => 0, //$grClient->getSegmentContacts(self::GETRESPONSE_SEGMENT_PHONE,               $grSubscribersOptions),
            'site_phone'                => 0, //$grClient->getSegmentContacts(self::GETRESPONSE_SEGMENT_SITE_PHONE,          $grSubscribersOptions),
            'siteAll'                   => 0, //$grClient->getSegmentContacts(self::GETRESPONSE_SEGMENT_SITE_ALL,            $grSubscribersOptions),
            'site_register'             => 0, //$grClient->getSegmentContacts(self::GETRESPONSE_SEGMENT_SITE_REG_DESKTOP,    $grSubscribersOptions),
            'site_register_mobile'      => 0, //$grClient->getSegmentContacts(self::GETRESPONSE_SEGMENT_SITE_REG_MOBILE,     $grSubscribersOptions),
            'site_check_order'          => 0, //$grClient->getSegmentContacts(self::GETRESPONSE_SEGMENT_SITE_CHECKOUT,       $grSubscribersOptions),
            'site_check_order_mobile'   => 0, //$grClient->getSegmentContacts(self::GETRESPONSE_SEGMENT_SITE_CHECKOUT_MOBILE, $grSubscribersOptions),
            'site_promocode_desktop'    => 0, //$grClient->getSegmentContacts(self::GETRESPONSE_SEGMENT_PROMOCODE_DESKTOP,   $grSubscribersOptions),
            'site_promocode_mobile'     => 0, //$grClient->getSegmentContacts(self::GETRESPONSE_SEGMENT_PROMOCODE_MOBILE,    $grSubscribersOptions),
            'site_form_desktop'         => 0, //$grClient->getSegmentContacts(self::GETRESPONSE_SEGMENT_FORM_DESKTOP,        $grSubscribersOptions),
        ];

        //* /МЫЛА из GetResponse *//

        //* МЫЛА НА САЙТЕ *//

        //Подсчет кол-ва валидных мыл на сайте
        $siteSubscribersSubQuery = "SELECT COUNT(1) AS num 
                  FROM " . UserEmail::tableName() . " AS user_email_is_valid 
                  WHERE user_email_is_valid.source=user_email.source
                    AND user_email_is_valid.source_detail=user_email.source_detail 
                    AND user_email_is_valid.is_valid_site='".Cms::BOOL_Y."'
                    AND user_email_is_valid.created_at>='{$timeFrom}'
                    AND user_email_is_valid.created_at<='{$timeTo}'";

        //* AIR PHONE *//

        $phoneSubscribers = UserEmail::find()
            ->alias('user_email')
            ->select([
                'COUNT(1) AS num',
                'source',
                'source_detail',
                "CONCAT(source, '_', source_detail) AS source_detail_ext",
                "($siteSubscribersSubQuery) AS valid_num_site"
            ])
            ->where(['source' => UserEmail::SOURCE_PHONE])
            ->andWhere(['>=', 'created_at', $timeFrom])
            ->andWhere(['<=', 'created_at', $timeTo])
            ->asArray()
            ->indexBy('source')
            ->groupBy('source')
            ->all();

        if (!$phoneSubscribers){
            $phoneSubscribers = [
                UserEmail::SOURCE_PHONE => [
                    'num'               => 0,
                    'source'            => UserEmail::SOURCE_PHONE,
                    'source_detail'     => UserEmail::SOURCE_DETAIL_CHECK_ORDER,
                    'source_detail_ext' => UserEmail::SOURCE_PHONE . '_' .UserEmail::SOURCE_DETAIL_CHECK_ORDER,
                ]
            ];
        }

        $phoneSubscribers[ UserEmail::SOURCE_PHONE ]['valid_num_gr'] = (is_array($grSubscribers['phone']) && $grSubscribers['phone'])
            ? count($grSubscribers['phone']) : 0;

        //* /AIR PHONE *//

        //* AIR PHONE EMAILS (BITRIX) *//

        $sqlGetAirPhoneEmails = <<<SQL
SELECT
  user.EMAIL
FROM
  front2.b_user AS user
  LEFT JOIN front2.b_sale_order AS orders ON orders.USER_ID = user.ID
  LEFT JOIN front2.b_sale_order_props_value AS prop_source ON prop_source.ORDER_ID = orders.ID AND prop_source.ORDER_PROPS_ID = 12
  LEFT JOIN front2.b_sale_order_props_value as order_phone ON orders.ID = order_phone.ORDER_ID and order_phone.ORDER_PROPS_ID = 16
WHERE
  user.DATE_REGISTER >= :dateFrom
  AND user.DATE_REGISTER <= :dateTo
  AND order_phone.VALUE NOT IN ('88007752250', '88003016010')
  AND prop_source.VALUE != 'NEW_SITE'
  AND length(user.EMAIL) > 7 
  AND user.EMAIL NOT REGEXP 'no.reg|newsite' 
  AND user.EMAIL LIKE '%@%'
SQL;

        $bitrixAirPhoneEmails = \Yii::$app->db->createCommand($sqlGetAirPhoneEmails, [
            ':dateFrom' => $this->dateFrom . ' 00:00:00',
            ':dateTo' => $this->dateTo . ' 23:59:59'
        ])->queryAll();

        //Мыла пользователей с телефона (эфира)
        $bitrixAirPhoneEmailsNum = count($bitrixAirPhoneEmails) ?: 0;

        $phoneSubscribers[ UserEmail::SOURCE_PHONE ]['num'] .= " | Bi: {$bitrixAirPhoneEmailsNum}";

        //* /AIR PHONE EMAILS (BITRIX) *//

        //* SITE PHONE *//

        $sitePhoneSubscribers = UserEmail::find()
            ->alias('user_email')
            ->select([
                'COUNT(1) AS num',
                'source',
                'source_detail',
                "CONCAT(source, '_', source_detail) AS source_detail_ext",
                "($siteSubscribersSubQuery) AS valid_num_site"
            ])
            ->where(['source' => UserEmail::SOURCE_SITE_PHONE])
            ->andWhere(['>=', 'created_at', $timeFrom])
            ->andWhere(['<=', 'created_at', $timeTo])
            ->asArray()
            ->indexBy('source')
            ->groupBy('source')
            ->all();

        if (!$sitePhoneSubscribers){
            $sitePhoneSubscribers = [
                UserEmail::SOURCE_SITE_PHONE => [
                    'num'               => 0,
                    'source'            => UserEmail::SOURCE_SITE_PHONE,
                    'source_detail'     => UserEmail::SOURCE_DETAIL_CHECK_ORDER,
                    'source_detail_ext' => UserEmail::SOURCE_SITE_PHONE . '_' .UserEmail::SOURCE_DETAIL_CHECK_ORDER,
                ]
            ];
        }

        $sitePhoneSubscribers[ UserEmail::SOURCE_SITE_PHONE ]['valid_num_gr'] = (is_array($grSubscribers['site_phone']) && $grSubscribers['site_phone'])
            ? count($grSubscribers['site_phone']) : 0;

        //* /SITE PHONE *//

        //* SITE PHONE EMAILS (BITRIX) *//

        $sqlGetSitePhoneEmails = <<<SQL
SELECT
  user.EMAIL
FROM
  front2.b_user AS user
  LEFT JOIN front2.b_sale_order AS orders ON orders.USER_ID = user.ID
  LEFT JOIN front2.b_sale_order_props_value AS prop_source ON prop_source.ORDER_ID = orders.ID AND prop_source.ORDER_PROPS_ID = 12
  LEFT JOIN front2.b_sale_order_props_value as order_phone ON orders.ID = order_phone.ORDER_ID and order_phone.ORDER_PROPS_ID = 16
WHERE
  user.DATE_REGISTER >= :dateFrom
  AND user.DATE_REGISTER <= :dateTo
  AND order_phone.VALUE IN ('88007752250', '88003016010')
  AND prop_source.VALUE != 'NEW_SITE'
  AND length(user.EMAIL) > 7 
  AND user.EMAIL NOT REGEXP 'no.reg|newsite' 
  AND user.EMAIL LIKE '%@%'
SQL;

        $bitrixSitePhoneEmails = \Yii::$app->db->createCommand($sqlGetSitePhoneEmails, [
            ':dateFrom' => $this->dateFrom . ' 00:00:00',
            ':dateTo' => $this->dateTo . ' 23:59:59'
        ])->queryAll();

        //Мыла пользователей с телефона (сайта)
        $bitrixSitePhoneEmailsNum = count($bitrixSitePhoneEmails) ?: 0;

        $sitePhoneSubscribers[ UserEmail::SOURCE_SITE_PHONE ]['num'] .= " | Bi: {$bitrixSitePhoneEmailsNum}";

        //* /SITE PHONE EMAILS (BITRIX) *//

        $siteSubscribers = UserEmail::find()
            ->alias('user_email')
            ->select([
                'COUNT(1) AS num',
                'source',
                'source_detail',
                "CONCAT(source, '_', source_detail) AS source_detail_ext",
                "($siteSubscribersSubQuery) AS valid_num_site"
            ])
            ->where(['source' => UserEmail::SOURCE_SITE])
            ->andWhere(['>=', 'created_at', strtotime($this->dateFrom . ' 00:00:00')])
            ->andWhere(['<=', 'created_at', strtotime($this->dateTo . ' 23:59:59')])
            ->groupBy('source_detail')
            ->asArray()
            ->indexBy('source_detail_ext')
            ->all();

        if ($siteSubscribers){
            foreach ($siteSubscribers as $sourceDetailExt => $siteSubscriber) {
                $siteSubscribers[$sourceDetailExt]['valid_num_gr'] = 0;
                if ( isset($grSubscribers[$sourceDetailExt]) && is_array($grSubscribers[$sourceDetailExt]) ){
                    $siteSubscribers[$sourceDetailExt]['valid_num_gr'] = count($grSubscribers[$sourceDetailExt]);
                }
            }
        }

        $siteAll = [
            'num' => $siteSubscribers ? ArrayHelper::arraySumColumn($siteSubscribers, 'num') : 0,
            'valid_num_site' => $siteSubscribers ? ArrayHelper::arraySumColumn($siteSubscribers, 'valid_num_site') : 0,
            'valid_num_gr' => (is_array($grSubscribers['siteAll']) && $grSubscribers['siteAll']) ? count($grSubscribers['siteAll']) : 0,
            'source' => UserEmail::SOURCE_SITE,
            'source_detail' => UserEmail::SOURCE_DETAIL_ALL,
            'source_detail_ext' => UserEmail::SOURCE_SITE . '_' . UserEmail::SOURCE_DETAIL_ALL
        ];

        $subscribers = array_merge(
            $phoneSubscribers,
            $sitePhoneSubscribers,
            [UserEmail::SOURCE_SITE . '_' . UserEmail::SOURCE_DETAIL_ALL => $siteAll],
            $siteSubscribers
        );

        //* /МЫЛА НА САЙТЕ *//

        //* Юзеры *//

        //Пользователи эфира (все, и с мылом и без):
        //выбираем связанные с пользователем заказы за нужный период, заказы отбираем только эфирные (не тел сайта, не источник "новый сайт"),
        //у пользователей этих заказов смотрим дату создания, она должна так же быть в нашем выбранном периоде.
        $sqlGetAirUsers = <<<SQL
SELECT
  user.DATE_REGISTER,
  user.NAME,
  user.EMAIL AS USER_EMAIL,
  user.PERSONAL_PHONE,
  order_phone.VALUE AS OUR_PHONE,
  prop_source.VALUE AS source
FROM
  front2.b_user AS user
  LEFT JOIN front2.b_sale_order AS orders ON orders.USER_ID = user.ID
  LEFT JOIN front2.b_sale_order_props_value AS prop_source ON prop_source.ORDER_ID = orders.ID AND prop_source.ORDER_PROPS_ID = 12
  LEFT JOIN front2.b_sale_order_props_value as order_phone ON orders.ID = order_phone.ORDER_ID and order_phone.ORDER_PROPS_ID = 16
WHERE
  user.DATE_REGISTER >= :dateFrom
  AND user.DATE_REGISTER <= :dateTo
  AND order_phone.VALUE NOT IN ('88007752250', '88003016010')
  AND prop_source.VALUE != 'NEW_SITE'
GROUP BY user_id
SQL;

        $bitrixAirUsers = \Yii::$app->db->createCommand($sqlGetAirUsers, [
            ':dateFrom' => $this->dateFrom . ' 00:00:00',
            ':dateTo' => $this->dateTo . ' 23:59:59'
        ])->queryAll();

        //phone
        //Новая Схема на основе source*
        $usersSitePhoneNum = (int)User::find()
            ->andWhere(['source' => UserEmail::SOURCE_SITE_PHONE])
            ->andWhere(['>=', 'created_at', strtotime($this->dateFrom . ' 00:00:00')])
            ->andWhere(['<=', 'created_at', strtotime($this->dateTo . ' 23:59:59')])
            ->count();

        //Пользователи с телефона (эфира)
        $usersAirPhoneNum = count($bitrixAirUsers) ?: 0;

        //site
        //Новая схема через source*
        $usersSite = User::find()
            ->select([
                'COUNT(1) AS num',
                'source',
                'source_detail',
                "CONCAT(source, '_', source_detail) AS source_detail_ext"
            ])
            ->andWhere(['source' => UserEmail::SOURCE_SITE])
            ->andWhere(['>=', 'created_at', strtotime($this->dateFrom . ' 00:00:00')])
            ->andWhere(['<=', 'created_at', strtotime($this->dateTo . ' 23:59:59')])
            ->groupBy('source_detail')
            ->asArray()
            ->indexBy('source_detail_ext')
            ->all();

        $subscribers[ UserEmail::SOURCE_PHONE]['users']                 = $usersAirPhoneNum;
        $subscribers[ UserEmail::SOURCE_SITE_PHONE]['users']            = $usersSitePhoneNum;
        $subscribers[ UserEmail::SOURCE_SITE . '_' . UserEmail::SOURCE_DETAIL_ALL ]['users']    = !empty($usersSite)  ? ArrayHelper::arraySumColumn($usersSite, 'num') : 0;

        if ($usersSite){
            foreach ($usersSite as $userSourceDetailExt => $userSite) {
                $subscribers[$userSourceDetailExt]['users'] = $userSite['num'];
            }
        }

        //* /Юзеры *//

        return new ArrayDataProvider([
            'allModels' => $subscribers,
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);
    }
}