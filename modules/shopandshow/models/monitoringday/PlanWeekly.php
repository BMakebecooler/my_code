<?php

namespace modules\shopandshow\models\monitoringday;

use common\helpers\ArrayHelper;
use common\lists\TreeList;
use common\models\cmsContent\CmsContentElement;
use modules\shopandshow\components\api\base\ApiResponse;
use modules\shopandshow\models\mediaplan\AirBlock;
use modules\shopandshow\models\mediaplan\AirDayProductTime;
use modules\shopandshow\models\shares\SsShareProduct;
use modules\shopandshow\models\shop\ShopBasket;
use modules\shopandshow\models\shop\ShopDiscount;
use modules\shopandshow\models\shop\ShopOrder;
use modules\shopandshow\models\shop\ShopProduct;
use skeeks\cms\components\Cms;
use skeeks\cms\models\Tree;

/**
 * @property array $plans
 * @property array $orders
 * @property array $baskets
 * @property array $airBlocks
 * @property array $airLots
 * @property array $producsInShares
 *
 * Class PlanWeekly
 * @package modules\shopandshow\models\monitoringday
 */
class PlanWeekly extends \yii\base\Model
{
    const SUBMIT_HTML = 1;
    const SUBMIT_EMAIL = 2;

    const BIG_DATA_DAYS = 28;

    public $date_from;
    public $date_to;
    public $email;
    public $submitType = 0;

    public $numDays = 0;
    public $products = [];

    // массив со всеми данными в формате [date => [k => v]]
    private $data = [];
    private $bigData = [];

    private $_plans = [];

    // поправляющий коэффициент (я не хотел, меня заставили)
    public $factor = 0;

    public function init()
    {
        parent::init();
        set_time_limit(600);

        if (!$this->date_from) {
            $this->date_from = date('Y-m-d', strtotime('this week'));
        }

        if (!$this->date_to) {
            $this->date_to = date('Y-m-d');
        }

        $this->factor = floatval(\Yii::$app->shopAndShowSettings->monitoringDayFactor) / 100;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['date_from', 'date_to'], 'required'],
            [['date_from', 'date_to'], 'string'],
            [['email'], 'email'],
            [['submitType'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'date_from' => 'Дата с',
            'date_to' => 'Дата по',
            'email' => 'E-mail',
        ];
    }

    /**
     * Инициализирует основной массив с инфой
     * @param bool $initWithBigData
     */
    public function initData($initWithBigData = true)
    {
        // по медиаплану
        $this->initOnair();

        // по баннерам (акциям)
        $this->initProductsInShares();

        // по заказам
        $this->initOrders();

        // по корзинам (depends onair and banners)
        $this->initBaskets();

        // общая инфа
        $this->initCommon();

        if ($initWithBigData) {
            // GA
            $this->initGaSessions();

            // старые данные для аналитики
            $this->initBigData();
        }

        // кол-во дней
        $this->numDays = sizeof($this->plans);
    }

    /**
     * просто враппер для получения отформатированной даты
     * @param $timestamp
     * @return false|string
     */
    public static function getDateFromTimestamp($timestamp)
    {
        return date('Y-m-d', $timestamp - HOUR_8);
    }

    /**
     * короткое наименование даты в формате дд.мм из Y-m-d
     * @param $date
     * @return string
     */
    public static function formatDate($date)
    {
        return join('.', array_reverse(explode('-', substr($date, 5))));
    }

    /**
     * получает день недели (пн, вт, ср ...) из Y-m-d
     * @param $date string
     * @param $withStyles bool
     * @return mixed
     */
    public static function getDayOfWeek($date, $withStyles = true)
    {
        static $daysStyled = ['<span style="color: red">вс</span>', 'пн', 'вт', 'ср', 'чт', 'пт', '<span style="color: red">сб</span>'];
        static $days = ['вс', 'пн', 'вт', 'ср', 'чт', 'пт', 'сб'];

        if ($withStyles) {
            return $daysStyled[date('w', strtotime($date))];
        }
        return $days[date('w', strtotime($date))];
    }

    /**
     * Получает акции на указанную дату Y-m-d
     * @param $date
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getShopDiscounts($date)
    {
        return ShopDiscount::find()
            ->andWhere('active_from <= :time AND active_to >= :time', [
                ':time' => strtotime(sprintf('%s 12:00:00', $date)),
            ])
            ->all();
    }

    /**
     * Получает список дневных планов
     * @return array|\yii\db\ActiveRecord[]|PlanDay[]
     */
    public function getPlans()
    {
        if (!$this->_plans) {
            $this->_plans = PlanDay::find(PlanDay::TYPE_SITE)
                ->andWhere(['BETWEEN', 'date', $this->date_from, $this->date_to])
                ->indexBy('date')
                ->orderBy('date')
                ->all();
        }

        return $this->_plans;
    }

    /**
     * Получает список дневных планов эфира
     * @return array|\yii\db\ActiveRecord[]|PlanDay[]
     */
    public function getPlansEfir()
    {
        static $plans = [];

        if (!$plans) {
            $plans = PlanDay::find(PlanDay::TYPE_EFIR)
                ->andWhere(['BETWEEN', 'date', $this->date_from, $this->date_to])
                ->indexBy('date')
                ->all();
        }

        return $plans;
    }

    /**
     * получает список заказов
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getOrders()
    {
        return ShopOrder::find()
            ->select(['id', 'created_at', 'price', 'source', 'price_delivery'])
            ->where(['BETWEEN', 'created_at', $this->getPeriodBegin($this->date_from), $this->getPeriodEnd($this->date_to)])
            ->asArray()
            ->all();
    }

    /**
     * получает список корзин
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getBaskets()
    {
        return ShopBasket::find()
            ->select([
                'shop_basket.id',
                'shop_basket.order_id',
                'shop_basket.product_id',
                'shop_basket.main_product_id',
                'shop_basket.quantity',
                'shop_basket.price',
                'shop_order.created_at'
            ])
            ->innerJoin('shop_order', 'shop_order.id = shop_basket.order_id')
            ->where(['IN', 'order_id', array_column($this->orders, 'id')])
            ->asArray()
            ->all();
    }

    /**
     * Получает ифнормацию по эфирам
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getAirBlocks()
    {
        return AirBlock::find()
            ->select(['block_id', 'begin_datetime', 'end_datetime', 'section_name', 'section_id'])
            ->where(['BETWEEN', 'begin_datetime', $this->getPeriodBegin($this->date_from), $this->getPeriodEnd($this->date_to)])
            ->asArray()
            ->all();
    }

    /**
     * Получает ифнормацию по товарам в эфире
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getAirLots()
    {
        return AirDayProductTime::find()
            ->select(['lot_id', 'block_id', 'begin_datetime', 'end_datetime'])
            ->where(['BETWEEN', 'begin_datetime', $this->getPeriodBegin($this->date_from), $this->getPeriodEnd($this->date_to)])
            ->asArray()
            ->all();
    }

    /**
     * Получает список товаров в акциях (сборках баннеров)
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getProducsInShares()
    {
        return SsShareProduct::find()
            ->select(['product_id', 'begin_datetime'])
            ->innerJoin('ss_shares', 'ss_shares.id = ss_shares_products.banner_id')
            ->where(['BETWEEN', 'begin_datetime', $this->getPeriodBegin($this->date_from), $this->getPeriodEnd($this->date_to)])
            ->asArray()
            ->all();
    }

    /**
     * Список категорий для постоения меню категорий
     * @param int $pid
     * @return \skeeks\cms\models\Tree[]
     */
    public function getCategories($pid = TreeList::CATALOG_ID)
    {
        $treeList = [];
        if ($pid > 0) {
            $treeList = TreeList::getTreeById($pid)->getChildren()->onCondition(['active' => Cms::BOOL_Y, 'redirect_tree_id' => null])->indexBy('id')->all();
        }

        if ($pid == TreeList::CATALOG_ID) {
            $mbt = new Tree();
            $mbt->name = 'МБТ';
            $mbt->id = -1;

            $treeList[] = $mbt;
            $treeList[] = TreeList::getTreeByCode('tovary-dlya-dachi');
        } // Дом
        elseif ($pid == 1622) {
            unset($treeList[TreeList::getTreeByCode('klimat')->id]);
            unset($treeList[TreeList::getTreeByCode('tovary-dlya-uborki-i-stirki')->id]);
            unset($treeList[TreeList::getTreeByCode('tovary-dlya-glazhki')->id]);
            unset($treeList[TreeList::getTreeByCode('tovary-dlya-dachi')->id]);
        } // МБТ
        elseif ($pid == -1) {
            $treeList[] = TreeList::getTreeByCode('tovary-dlya-uborki-i-stirki');
            $treeList[] = TreeList::getTreeByCode('tovary-dlya-glazhki');
            $treeList[] = TreeList::getTreeByCode('klimat');
        }

        return $treeList;
    }

    /**
     * Считает сумму элементов корзины для указанной категории и всех ее подкатегорий
     * @param string $date
     * @param int $treeId
     * @return float|int
     */
    public function getSumForCategory($date, $treeId)
    {
        static $cache = [];

        $result = 0;
        if (!isset($this->data[$date]['baskets'])) {
            return $result;
        }

        foreach ($this->data[$date]['baskets'] as $basket) {
            // исключаем цтс
            if (array_key_exists('cts', $this->data[$date]) && !empty($this->data[$date]['cts']) && $this->data[$date]['cts']->id == $basket['main_product_id']) {
                continue;
            }

            $basketTreeId = $this->products[$basket['main_product_id']]['tree_id'];

            // бывают лоты без привязки к дереву... например допродажи
            if (!$basketTreeId) {
                continue;
            }

            if (!isset($cache[$basketTreeId])) {
                $basketTree = TreeList::getTreeById($basketTreeId);

                if ($basketTree) {
                    // сад
                    if (1934 == $basketTreeId || in_array(1934, (array)$basketTree->pids)) {
                        $basketTree->pids = [1934];
                    } // глажка
                    elseif (2001 == $basketTreeId || in_array(2001, (array)$basketTree->pids)) {
                        $basketTree->pids = [-1];
                    } // уборка
                    elseif (2008 == $basketTreeId || in_array(2008, (array)$basketTree->pids)) {
                        $basketTree->pids = [-1];
                    } // климат
                    elseif (1662 == $basketTreeId || in_array(1662, (array)$basketTree->pids)) {
                        $basketTree->pids = [-1];
                    }
                    $cache[$basketTreeId] = $basketTree->pids;
                }
            }

            if ($treeId && ($treeId == $basketTreeId || in_array($treeId, $cache[$basketTreeId]))) {
                $result += $this->getGoodSum($basket['quantity'] * $basket['price']);
            }
        }

        return $result;
    }

    /**
     * @param string $date
     * @return double
     * @throws \yii\db\Exception
     */
    public function getMarge($date)
    {
        $query = <<<SQL
SELECT 
  sum((sb.price - if(margin.value IS NULL OR margin.value = '' OR margin.value = 0, sb.price, margin.value)) * sb.quantity) sum_margin
FROM shop_order so
LEFT JOIN shop_basket sb ON sb.order_id = so.id
LEFT JOIN cms_content_element_property margin ON margin.element_id = sb.main_product_id AND margin.property_id = :purchase_property_id
WHERE so.created_at >= :begin_datetime AND so.created_at <= :end_datetime
SQL;


        $marge = \Yii::$app->db->createCommand($query, [
            ':begin_datetime' => $this->getPeriodBegin($date),
            ':end_datetime' => $this->getPeriodEnd($date),
            ':purchase_property_id' => PlanHelper::PURCHASE_PROPERTY_ID
        ])->queryScalar();

        return $marge ?: 0;
    }

    /**
     * Отдает информацию по использованию категорий в часе в рамках дня
     * @return array
     */
    public function getAirBlocksData()
    {
        $result = [];
        foreach ($this->data as $date => $block) {
            if (array_key_exists('air_blocks', $block)) {
                $efirAirBlock = $this->filterEfirBlocks($date, $block['air_blocks']);
                $items = array_count_values(array_column($efirAirBlock, 'section_name'));

                $row = 0;
                foreach ($items as $name => $value) {
                    $result[$row++][$date][$name] = $value;
                }
            }
        }

        return $result;
    }

    /**
     * @param $attr
     * @param null $date
     * @param int $default
     * @return mixed
     */
    public function getData($attr, $date = null, $default = 0)
    {
        if ($date) {
            $result = $this->data[$date][$attr] ?? $default;
        } else {
            $result = array_sum(array_column($this->data, $attr));
        }

        if (is_numeric($result) && $result == 0 && $default != 0) {
            return $default;
        }
        return $result;
    }

    /**
     * @param $attr
     * @param int $default
     * @return mixed
     */
    public function getBigData($attr, $default = 0)
    {
        $result = $this->bigData[$attr] ?? $default;

        if (is_numeric($result) && $result == 0 && $default != 0) {
            return $default;
        }
        return $result;
    }

    private function initOnair()
    {
        foreach ($this->airBlocks as $airBlock) {
            $date = self::getDateFromTimestamp($airBlock['begin_datetime']);
            $this->data[$date]['air_blocks'][] = $airBlock;
        }

        foreach ($this->airLots as $airLot) {
            $date = self::getDateFromTimestamp($airLot['begin_datetime']);
            $this->data[$date]['air_lots'][$airLot['lot_id']] = 1;
        }

        foreach ($this->plans as $plan) {
            $this->data[$plan->date]['air_actions'] = [];

            $actions = \Yii::$app->cache->getOrSet('mediaplan_dayinfo_' . $plan->date, function () use ($plan) {
                $result = [];
                $attempt = 0;
                do {
                    try {
                        $response = \Yii::$app->mediaPlanApi->airDayInfo($plan->date);
                    } catch (\yii\httpclient\Exception $e) {
                        $attempt++;
                        sleep(2);
                    } catch (\Exception $e) {
                        throw $e;
                    }
                } while (empty($response) && $attempt < 10);

                if ($response && ($response instanceof ApiResponse) && $response->isOk) {
                    if ($response->data['day']['action1']) {
                        $result['action1'] = $response->data['day']['action1'];
                    }
                    if ($response->data['day']['action2']) {
                        $result['action2'] = $response->data['day']['action2'];
                    }
                } else {
                    $result['action1'] = '(не удалось получить акцию)';
                }

                return $result;
            }, HOUR_1);

            if (isset($actions['action1'])) {
                $this->data[$plan->date]['air_actions']['action1'] = $actions['action1'];
            }
            if (isset($actions['action2'])) {
                $this->data[$plan->date]['air_actions']['action2'] = $actions['action2'];
            }

        }
    }

    private function initProductsInShares()
    {
        foreach ($this->producsInShares as $productInShare) {
            $date = self::getDateFromTimestamp($productInShare['begin_datetime']);
            $this->data[$date]['lots_in_banners'][$productInShare['product_id']] = 1;
        }
    }

    private function initOrders()
    {
        foreach ($this->orders as $order) {
            $date = self::getDateFromTimestamp($order['created_at']);

            $this->data[$date]['orders'][] = $order;

            if (!isset($this->data[$date]['orders_sum'])) {
                $this->data[$date]['orders_sum'] = 0;
            }
            if (!isset($this->data[$date]['orders_count'])) {
                $this->data[$date]['orders_count'] = 0;
            }
            if (!isset($this->data[$date]['orders_delivery_sum'])) {
                $this->data[$date]['orders_delivery_sum'] = 0;
            }
            $this->data[$date]['orders_count']++;
            $this->data[$date]['orders_sum'] += $this->getGoodSum($order['price']);

            if ($order['source'] == ShopOrder::SOURCE_SITE) {
                $order['price_delivery'] = Plan::PRICE_DELIVERY_SITE;
            }
            $this->data[$date]['orders_delivery_sum'] += $this->getGoodSum($order['price_delivery']);
        }
    }

    private function initBaskets()
    {
        $products = [];
        foreach ($this->baskets as $basket) {
            $date = self::getDateFromTimestamp($basket['created_at']);

            $this->data[$date]['baskets'][] = $basket;

            if (!isset($this->data[$date]['baskets_quantity'])) {
                $this->data[$date]['baskets_quantity'] = 0;
            }
            $this->data[$date]['baskets_quantity'] += $basket['quantity'];

            $onAir = $this->isOnAir($date, $basket['main_product_id']);
            $onBanner = $this->isOnBanner($date, $basket['main_product_id']);

            if (!isset($this->data[$date]['baskets_onair_sum'])) {
                $this->data[$date]['baskets_onair_sum'] = 0;
                $this->data[$date]['baskets_onair_quantity'] = 0;
                $this->data[$date]['baskets_onbanner_sum'] = 0;
                $this->data[$date]['baskets_onbanner_quantity'] = 0;
                $this->data[$date]['baskets_other_sum'] = 0;
                $this->data[$date]['baskets_other_quantity'] = 0;
            }

            $basketPrice = $this->getGoodSum($basket['quantity'] * $basket['price']);

            if ($onAir) {
                $this->data[$date]['baskets_onair_sum'] += $basketPrice;
                $this->data[$date]['baskets_onair_quantity'] += $basket['quantity'];
            } elseif ($onBanner) {
                $this->data[$date]['baskets_onbanner_sum'] += $basketPrice;
                $this->data[$date]['baskets_onbanner_quantity'] += $basket['quantity'];
            } else {
                $this->data[$date]['baskets_other_sum'] += $basketPrice;
                $this->data[$date]['baskets_other_quantity'] += $basket['quantity'];
            }

            $products[$basket['main_product_id']] = 1;
        }
        $this->products = CmsContentElement::find()
            ->select(['id', 'tree_id'])
            ->where(['id' => array_keys($products)])
            ->indexBy('id')
            ->asArray()
            ->all();
        unset($products);
    }

    private function initCommon()
    {
        foreach ($this->plans as $plan) {
            $this->data[$plan->date]['discounts'] = self::getShopDiscounts($plan->date);

            $this->data[$plan->date]['cts'] = null;
            $this->data[$plan->date]['cts_product'] = null;
            $this->data[$plan->date]['cts_sum'] = 0;

            $cts = \modules\shopandshow\lists\Shares::getCtsProduct($plan->date);

            if ($cts && $cts->product) {
                $this->data[$plan->date]['cts'] = $cts->product;
                $this->data[$plan->date]['cts_product'] = ShopProduct::getInstanceByContentElement($cts->product);

                // считает доход от цтс
                if (isset($this->data[$plan->date]['baskets'])) {
                    foreach ($this->data[$plan->date]['baskets'] as $basket) {
                        if ($basket['main_product_id'] == $cts->product->id) {
                            $this->data[$plan->date]['cts_sum'] += $this->getGoodSum($basket['quantity'] * $basket['price']);
                        }
                    }
                }

                $this->data[$plan->date]['efir_cts_sum'] =
                    PlanHelper::getCtsTotalSum(
                        date('Y-m-d H:i:s', $this->getPeriodBegin($plan->date)),
                        date('Y-m-d H:i:s', $this->getPeriodEnd($plan->date)),
                        $cts->product
                    );
            }

            $this->data[$plan->date]['efir_total_sum'] = array_sum(
                PlanHelper::getEfirTotalSum(
                    date('Y-m-d H:i:s', $this->getPeriodBegin($plan->date)),
                    date('Y-m-d H:i:s', $this->getPeriodEnd($plan->date))
                )
            );


            $this->data[$plan->date]['marge'] = $this->getGoodSum($this->getMarge($plan->date));
        }
    }

    private function initGaSessions()
    {
        foreach ($this->plans as $plan) {
            $this->data[$plan->date]['sessions'] = 0;

            $google = new \common\models\api\ga\v4\GoogleAnalytics($plan->date);
            $gaData = $google->getSessions();
            foreach ($gaData['sessions']['dataTable']['rows'] as $row) {
                $this->data[$plan->date]['sessions'] += $row['c'][1]['v'];
            }
        }
    }

    private function initBigData()
    {
        $dateTo = strtotime(sprintf('%s 00:00:00', $this->date_from));
        $dateFrom = $dateTo - self::BIG_DATA_DAYS * DAYS_1;

        $ordersQuery = ShopOrder::find()
            ->select(new \yii\db\Expression("SUM(price + IF(source = '" . ShopOrder::SOURCE_SITE . "', " . Plan::PRICE_DELIVERY_SITE . ", 0)) as price_sum, 
                COUNT(*) AS amount"))
            ->where(['BETWEEN', 'created_at', $dateFrom, $dateTo]);

        $ordersData = $ordersQuery->asArray()->one();

        $this->bigData['orders_sum'] = $ordersData['price_sum'];
        $this->bigData['orders_amount'] = $ordersData['amount'];
        $this->bigData['orders_avg_sum'] = $ordersData['price_sum'] / self::BIG_DATA_DAYS;

        /*if (\Yii::$app instanceof \yii\web\Application) {
            return;
        }*/

        $planMonth = new self([
            'date_from' => date('Y-m-d', $dateFrom),
            'date_to' => date('Y-m-d', $dateTo - 1)
        ]);

        $planMonth->initData(false);

        $categoriesSum = $planMonth->getCategoriesSum();

        $this->bigData['sum_onair_categories'] = $categoriesSum['onair'];
        $this->bigData['sum_not_onair_categories'] = $categoriesSum['not_onair'];
    }

    /**
     * суммы продаж по категориям эфирных и неэфирных рубрик
     * @return array
     */
    public function getCategoriesSum()
    {
        // сумма по категориям (эфир/неэфир)
        $categoriesSum = [];
        // число часов категории в эфире
        $categoriesOnairHours = [];
        // кол-во дней, где категория была в эфире/ не эфире
        $categoriesDays = [];

        $categories = $this->getCategories();
        $dailyAirBlocks = $this->getDailyAirBlocks();

        foreach ($dailyAirBlocks as $date => $airBlocks) {
            // по всем категориям
            foreach ($categories as $category) {
                $sumForCategory = $this->getSumForCategory($date, $category->id);
                $onairKey = 'not_onair';
                if (array_key_exists($category->id, $airBlocks)) {
                    $onairKey = 'onair';
                    $categoriesOnairHours[$category->id] = ($categoriesOnairHours[$category->id] ?? 0) + sizeof($airBlocks[$category->id]);
                } else {
                    $categoriesDays[$category->id] = ($categoriesDays[$category->id] ?? 0) + 1;
                }

                $categoriesSum[$onairKey][$category->id] = ($categoriesSum[$onairKey][$category->id] ?? 0) + $sumForCategory;
            }
        }

        // усредняем суммы
        foreach ($categoriesSum as $onair => $_categories) {
            foreach ($_categories as $categoryId => $sum) {
                // для эфирной категории делим на число часов
                if ($onair == 'onair') {
                    $sum /= $categoriesOnairHours[$categoryId];
                } // для неэфира делим сумму на число дней, которое эта категория была в неэфире
                else {
                    $sum /= $categoriesDays[$categoryId];
                }

                $categoriesSum[$onair][$categoryId] = $sum;
            }
        }

        return $categoriesSum;
    }

    /**
     * Отдает информацию по использованию категорий в часе в рамках дня
     * @return array
     */
    public function getDailyAirBlocks()
    {
        $result = [];
        foreach ($this->data as $date => $block) {
            if (array_key_exists('air_blocks', $block)) {
                $efirAirBlock = $this->filterEfirBlocks($date, $block['air_blocks']);
                $result[$date] = ArrayHelper::index($efirAirBlock, 'block_id', 'section_id');
            }
        }

        return $result;
    }

    /**
     * показывает отклонение по категории от среднего за прошлый месяц
     * @param $categoryId
     * @param $onair
     * @param $airBlocks
     *
     * @return float
     */
    public function getCategoryAvg($categoryId, $onair, $airBlocks)
    {
        if ($onair) {
            $avgSum = $this->getBigData('sum_onair_categories');
            $avgCategorySumHour = $avgSum[$categoryId] ?? 0; //Бывает что нет данных по категории
            $avgCategorySum = sizeof($airBlocks) * $avgCategorySumHour;
        } else {
            $avgSum = $this->getBigData('sum_not_onair_categories');
            $avgCategorySum = $avgSum[$categoryId];
        }
        return $avgCategorySum;
    }

    /**
     * Показываем часы эфира только с 8 до 22
     * @param $date
     * @param $blocks
     * @return array
     */
    private function filterEfirBlocks($date, $blocks)
    {
        $beginOfAirDate = strtotime(sprintf('%s 00:00:00', $date)) + HOUR_8;
        $endOfAirDate = strtotime(sprintf('%s 00:00:00', $date)) + HOUR_1 * 22;

        return array_filter($blocks, function ($block) use ($beginOfAirDate, $endOfAirDate) {
            return $block['begin_datetime'] >= $beginOfAirDate && $block['begin_datetime'] < $endOfAirDate;
        });
    }

    private function isOnAir($date, $productId)
    {
        return $this->data[$date]['air_lots'][$productId] ?? 0;
    }

    private function isOnBanner($date, $productId)
    {
        return $this->data[$date]['lots_in_banners'][$productId] ?? 0;
    }

    /**
     * Считает правильную сумму, учитывая волшебный коэффициент
     * @param $sum
     * @return float
     */
    private function getGoodSum($sum)
    {
        return $sum + $sum * $this->factor;
    }

    private function getPeriodBegin($date)
    {
        return strtotime(sprintf('%s 00:00:00', $date)) + HOUR_8;
    }

    private function getPeriodEnd($date)
    {
        return strtotime(sprintf('%s 23:59:59', $date)) + HOUR_8;
    }
}