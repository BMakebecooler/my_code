<?php

namespace modules\shopandshow\models\monitoringday;

use common\lists\TreeList;
use modules\shopandshow\models\mediaplan\AirDayProductTime;
use modules\shopandshow\models\shares\SsShare;
use modules\shopandshow\models\shop\ShopBasket;
use skeeks\cms\components\Cms;

class SalesEfir extends \yii\base\Model
{
    public $date;
    public $showCts = 0;

    public function init()
    {
        parent::init();

        if (!$this->date) {
            $this->date = date('Y-m-d');
        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['date'], 'required'],
            [['date'], 'string'],
            [['showCts'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'date' => 'Дата',
            'showCts' => 'Товар ЦТС отдельной строкой',
        ];
    }

    /**
     * Список категорий для дополнительного отчета
     * @return \skeeks\cms\models\Tree[]
     */
    public function getCategories()
    {
        $trees = TreeList::getTreeById(TreeList::CATALOG_ID)->getChildren()->onCondition(['active' => Cms::BOOL_Y])->all();

        $trees[] = TreeList::getTreeByCode('tovary-dlya-dachi');
        $trees[] = TreeList::getTreeByCode('kosmetika');

        return $trees;
    }

    /**
     * Формирует список категорий эфира, если указана одна дата, в противном случае генерит простой список часов дня
     * @param bool $fillToFullDay
     * @return array
     */
    public function getOnairCategories($fillToFullDay = true)
    {
        $plan = new Plan(['date' => $this->date]);
        return $plan->getOnairCategories($fillToFullDay);
    }

    /**
     * @return array
     */
    public function getBasketCtsSales()
    {
        $plan = new Plan(['date' => $this->date]);
        return $plan->getBasketCtsSales();
    }

    /**
     * @param array $onAirProducts
     * @return array|AirDayProductTime[]|ShopBasket[]|\yii\db\ActiveRecord[]
     */
    public function getOnairSales(array $onAirProducts)
    {
        $shopBasketsQuery = ShopBasket::find()
            ->select(new \yii\db\Expression('SUM(shop_basket.price * shop_basket.quantity) as product_price, UNIX_TIMESTAMP(DATE_FORMAT(FROM_UNIXTIME(shop_order.created_at), "%Y-%m-%d %H:00:00")) as order_date'))
            ->innerJoin('shop_order', 'shop_order.id = order_id')
            ->where(['BETWEEN', 'shop_order.created_at', $this->getPeriodBegin($this->date), $this->getPeriodEnd($this->date)])
            ->andWhere(['shop_basket.has_removed' => ShopBasket::HAS_REMOVED_FALSE])
            ->andWhere(['NOT', ['shop_basket.order_id' => null]])
            ->andWhere(['shop_basket.main_product_id' => array_values($onAirProducts)]);


        if ($this->showCts) {
            /** @var SsShare $cts */
            $cts = \modules\shopandshow\lists\Shares::getCtsProduct($this->date);
            if ($cts) {
                $shopBasketsQuery->andWhere(['NOT', ['main_product_id' => $cts->product->id]]);
            }
        }

        return $shopBasketsQuery
            ->groupBy('order_date')
            ->orderBy('order_date')
            ->indexBy('order_date')
            ->asArray()
            ->all();
    }

    /**
     * @return mixed
     * @throws \Exception
     * @throws \Throwable
     */
    public function getOnairProducts()
    {
        return \Yii::$app->db->cache(function () {
            $onAirProducts = AirDayProductTime::find()->alias('p')
                ->innerJoinWith('airBlock b')
                ->andWhere('b.begin_datetime >= :begin_datetime AND b.begin_datetime <= :end_datetime ', [
                    ':begin_datetime' => $this->getPeriodBegin($this->date),
                    ':end_datetime' => $this->getPeriodEnd($this->date),
                ])
                ->asArray()
                ->all();

            $result = [];
            foreach ($onAirProducts as $onAirProduct) {
                $result[$onAirProduct['airBlock']['begin_datetime']][] = $onAirProduct['lot_id'];
            }

            return $result;
        }, MIN_15);
    }

    /**
     * @param $treeId
     * @param null $isOnAir
     * @return array
     * @throws \Exception
     * @throws \Throwable
     */
    public function getBasketProductSales($treeId, $isOnAir = null)
    {
        $result = [];
        $planSales = $this->getBasketProductSalesData($treeId, $isOnAir);

        foreach ($planSales as $sales) {
            @$result[$sales['order_date']] += $sales['product_price'];
        }

        return $result;
    }

    /**
     * @param $treeId
     * @param null $isOnAir
     * @return array|\yii\db\ActiveRecord[]
     *
     * @throws \Exception
     * @throws \Throwable
     */
    public function getBasketProductSalesData($treeId, $isOnAir = null)
    {
        $shopBasketsQuery = ShopBasket::find()
            ->select(new \yii\db\Expression('SUM(shop_basket.price * shop_basket.quantity) as product_price, UNIX_TIMESTAMP(DATE_FORMAT(FROM_UNIXTIME(shop_order.created_at), "%Y-%m-%d %H:00:00")) as order_date'))
            ->innerJoin('shop_order', 'shop_order.id = order_id')
            ->leftJoin('ss_mediaplan_air_blocks ab', 'ab.begin_datetime = UNIX_TIMESTAMP(DATE_FORMAT(FROM_UNIXTIME(shop_order.created_at), "%Y-%m-%d %H:00:00"))')
            ->leftJoin('ss_mediaplan_air_day_product_time ap', 'ap.block_id = ab.block_id AND ap.lot_id = shop_basket.main_product_id')
            ->where(['BETWEEN', 'shop_order.created_at', $this->getPeriodBegin($this->date), $this->getPeriodEnd($this->date)])
            ->andWhere(['shop_basket.has_removed' => ShopBasket::HAS_REMOVED_FALSE])
            ->andWhere(['NOT', ['shop_basket.order_id' => null]]);

        if ($treeId) {
            $tree = \common\lists\TreeList::getTreeById($treeId);
            $treeQuery = $tree->getDescendants()->select('id');
            // исключаем сад
            if ($tree->code == 'dom') {
                $treeQuery->andWhere('dir NOT LIKE \'catalog/dom/tovary-dlya-dachi%\'');
            } // исключаем здоровье
            elseif ($tree->code == 'krasota-i-zdorove') {
                $treeQuery->andWhere('dir NOT LIKE \'catalog/krasota-i-zdorove/kosmetika%\'');
            }
            $trees = array_merge([$treeId], $treeQuery->asArray()->column());

            $shopBasketsQuery
                ->innerJoin('cms_content_element cce', 'cce.id = main_product_id')
                ->andWhere(['cce.tree_id' => $trees]);
        }

        if ($this->showCts) {
            /** @var SsShare $cts */
            $cts = \modules\shopandshow\lists\Shares::getCtsProduct($this->date);
            if ($cts && $cts->product) {
                $shopBasketsQuery->andWhere(['NOT', ['main_product_id' => $cts->product->id]]);
            }
        }

        if ($isOnAir === true) {
            $shopBasketsQuery->andWhere(['ab.section_id' => $treeId])->andWhere('ap.id IS NOT NULL');
        } elseif ($isOnAir === false) {
            $shopBasketsQuery->andWhere(['ab.section_id' => $treeId])->andWhere('ap.id IS NULL');
        } else {
            $shopBasketsQuery->andWhere(['<>', 'ab.section_id', $treeId]);
        }

        return $shopBasketsQuery
            ->groupBy('order_date')
            ->orderBy('order_date')
            ->asArray()
            ->all();
    }

    private function getPeriodBegin($date)
    {
        return strtotime(sprintf('%s 00:00:00', $date));
    }

    private function getPeriodEnd($date)
    {
        return strtotime(sprintf('%s 23:59:59', $date));
    }
}