<?php

namespace common\components\slider;

use common\helpers\ArrayHelper;
use common\helpers\Category;
use common\lists\TreeList;
use common\models\OnAir\OnAirSchedule;
use common\models\Product;
use common\models\ProductAbc;
use common\models\SsShare;
use frontend\controllers\FrontendController;
use skeeks\cms\controllers\ElfinderController;
use yii\base\Component;
use yii\db\Expression;

class Slider extends Component
{
    const TYPE_PRODUCTS = 'products';

    const PRODUCTS_DAY = 1;
    const PRODUCTS_WEEK = 2;
    const PRODUCTS_ON_AIR = 3;
    const PRODUCTS_CTS = 4;
    const PRODUCTS_CTS_ALL = 5;
    const PRODUCTS_ON_AIR_CURRENT_HOUR = 6; //пока не используется
    const PRODUCTS_TOP = 7;
    const PRODUCTS_RELATED_CATEGORY = 8;


    public static $sliderNames = [
        self::PRODUCTS_DAY => ['name' => 'Товары дня', 'ctsRelated' => false],
        self::PRODUCTS_WEEK => ['name' => 'Товары недели', 'ctsRelated' => false],
        self::PRODUCTS_ON_AIR => ['name' => 'Товары в эфире', 'ctsRelated' => false],
        self::PRODUCTS_CTS => ['name' => 'Актуальное сегодня', 'ctsRelated' => true],
        self::PRODUCTS_CTS_ALL => ['name' => 'Актуальное сегодня', 'ctsRelated' => true],
        self::PRODUCTS_ON_AIR_CURRENT_HOUR => ['name' => 'Товары в эфире в текущий час'],
        self::PRODUCTS_TOP => ['name' => 'Актуальное сегодня', 'ctsRelated' => true],
        self::PRODUCTS_RELATED_CATEGORY => ['name' => 'Товары, привязанные к категории', 'ctsRelated' => false]
    ];

    public function getTitle(int $sliderId)
    {
        return self::$sliderNames[$sliderId]['name'] ?? '';
    }

    public function getCtsRelated(int $sliderId)
    {
        return self::$sliderNames[$sliderId]['ctsRelated'] ?? false;
    }

    public function getData(int $sliderId, $params = [])
    {
        $return = [];
        switch ($sliderId) {
            default:
            case self::PRODUCTS_DAY:
                $return = self::getProductsDay();
                break;
            case self::PRODUCTS_WEEK:
                $return = self::getProductsWeek();
                break;
            case self::PRODUCTS_CTS:
                $return = self::getProductsCTS();
                break;
            case self::PRODUCTS_CTS_ALL:
                $return = self::getProductsCTSAll();
                break;
            case self::PRODUCTS_ON_AIR:
                $return = self::getProductsOnair();
                break;
            case self::PRODUCTS_ON_AIR_CURRENT_HOUR:
                break;
            case self::PRODUCTS_TOP:
                $return = self::getProductsTop();
                break;
            case self::PRODUCTS_RELATED_CATEGORY:
                $treeId = $params['identityId'] ?? Category::$rootCategoryId;
                $return = self::getProductsRelatedCategory($treeId);
                break;
        }
        return $return;
    }


    public static function getProductsDay()
    {
        return ProductAbc::findDay();
    }

    public static function getProductsWeek()
    {
        return ProductAbc::findWeek();
    }

    public static function getProductsCTSAll()
    {
        return \common\helpers\Product::getCtsProductsQuery()->all();
    }

    public static function getProductsCTS()
    {
        $debug = false;

        $products = [];
        $ctsShares = SsShare::getSharesByTypeEfir(SsShare::BANNER_TYPE_CTS, SsShare::DEFAULT_LIMIT_CTS);
        if (isset($ctsShares[0])) {
            $share = $ctsShares[0];
            $productsAbcQuery = ProductAbc::find()->byType(ProductAbc::TYPE_CTS)->orderBy('order')->select('product_id');

            //Вычисляемое условие для поиска товаров допродаж
            //Обязательно QUERY! Если передать IDs то сбивается сортировка
            $productsToSearch = null;

            //* Связь с конкретным ЦТС (в аналитике мультиЦТСные допродажи) *//

            $ctsProduct = isset($share->product) ? Product::findOne($share->product->id) : null;
            if ($ctsProduct){
                $productsAbcExactCtsQuery = clone $productsAbcQuery;
                $productsToSearch = $productsAbcExactCtsQuery->andWhere(['addition' => $ctsProduct->id]);
                if ($debug && $productsToSearch->count()){
                    \Yii::error("CtsRelProductsSrc [{$ctsProduct->new_lot_num}] - Exact", 'debug');
                }
            }

            //* /Связь с конкретным ЦТС (в аналитике мультиЦТСные допродажи) *//

            //Если для конкретного ЦТС ничего не нашлось - пробуем взять допродажи без указания связанного ЦТС
            if (!$productsToSearch){
                $productsAbcEmptyCtsQuery = clone $productsAbcQuery;
                $productsToSearch = $productsAbcEmptyCtsQuery->andWhere(['addition' => null]);
                if ($debug && $productsToSearch->count()){
                    \Yii::error("CtsRelProductsSrc [{$ctsProduct->new_lot_num}] - Empty", 'debug');
                }
            }

            //Если нет ни к конкретному ЦТС ни к пустому - берем что попало
            if (!$productsToSearch->count()){
                $productsToSearch = $productsAbcQuery;

                if ($debug) {
                    \Yii::error("CtsRelProductsSrc [{$ctsProduct->new_lot_num}] - All", 'debug');
                }
            }

//            $products = $share->product->getSimilarProducts($productsAbc);
            //Если нашлись товары под конкретный ЦТС - ищем по ним, если не нашлось - выберем любые
            $products = Product::find()->onlyLot()->canSale()->andWhere(['id' => $productsToSearch])->all();
        }
        return $products;
    }

    public static function getProductsTop()
    {
        $products = [];
        if ($productsTopSrc = ProductAbc::find()->byType(ProductAbc::TYPE_CTS)->orderBy('order')->select(['product_id'])) {
            //Первые 2 товара оставляем неизменными
            $productsTopPrimary = Product::find()->canSale()->where(['id' => $productsTopSrc])->limit(2)->all();

            //Остальные рандомайзим
            $productsTopSecondaryIds = $productsTopSrc->limit(1000)->offset(2)->column();
            $productsTopSecondary = Product::find()->canSale()->where(['id' => $productsTopSecondaryIds])->orderBy(new Expression('rand()'))->limit(18)->all();

            $products = ArrayHelper::merge($productsTopPrimary, $productsTopSecondary);

            //Подменяем плашки
            $i = 0;
            $products = array_map(function ($product) use (&$i) {
                /** @var Product $product */
                $i++;
                $product->badge_2 = $i <= 2 ? Product::BADGE2_ADD2CTS : Product::BADGE2_PAY_ATTENTION;
                return $product;
            }, $products);
        }
        return $products;

    }

    public static function getProductsRelatedCategory($treeId)
    {
        return Product::find()
            ->canSale()
            ->innerJoin(ProductAbc::tableName() . ' AS stat', "cms_content_element.id=stat.product_id")
            ->andWhere(['cms_content_element.tree_id' => TreeList::getDescendantsById($treeId)])
            ->andWhere(['type_id' => ProductAbc::TYPE_TOP6])
            ->limit(FrontendController::RELATED_PRODUCTS_LIMIT)
            ->all();
    }

    public static function getProductsOnair()
    {
        $products = [];
        $nowOnAir = OnAirSchedule::create(time())->make();
        $lots = [];
        if ($nowOnAir['products']) {
            foreach ($nowOnAir['products'] as $k => $product) {
                if (count($lots) < OnAirSchedule::LOT_SLIDER_LIMIT) {
                    $lots[$product['lot_id']] = $product['lot_id'];
                }
            }
        }
        foreach ($lots as $lot_id) {
            $products[] = Product::findOne($lot_id);
        }
        return $products;
    }
}