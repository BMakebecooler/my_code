<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 18/01/2019
 * Time: 12:13
 */

namespace modules\shopandshow\components\export;


use common\helpers\App;
use common\helpers\Color;
use common\helpers\Common;
use common\models\Brand;
use common\models\CmsContentElement;
use common\models\Product;
use common\models\query\CmsContentElementQuery;


class BaseHandler extends \skeeks\cms\exportShopYandexMarket\ExportShopYandexMarketHandler
{

    protected $partSize = 500;
    protected static $brands = [];
    protected static $colors = [];

//    public static $profileMode = true;
    public static $profileMode = false;

    public $analyticsProductsForFeed = [];
    public $flashPriceProducts = [];

    public function init()
    {
        self::initTimer("baseInit");

        if (App::isConsoleApplication()) { //Так же задействуется в админке что приводит к зависанию, учитываем
            $this->getAnalyticsProductsForFeed();
            $this->flashPriceProducts = $this->getFlashPriceProducts();
        }

        /**
         * получить массив справочник всех брендов
         */
        $queryBrands = Brand::find()->select(['id', 'name'])->asArray();
        foreach ($queryBrands->each() as $brand) {
            self::$brands[$brand['id']] = $brand['name'];
        }

        parent::init();

        self::showTimer("baseInit");

        return true;
    }

    public function getAnalyticsProductsForFeed()
    {

        self::initTimer("getAnalyticsProductsForFeed");

        $analyticsProductsForFeed = $this->getAnalyticsProductsForFeedArray();

        $products = Product::find()
            ->onlyLot()
            ->select(['id', 'code', 'name'])
            ->andWhere(['code' => array_keys($analyticsProductsForFeed)])
            ->indexBy('code')
            ->asArray()
            ->all();

        echo "Товар из аналитики получено: " . count($analyticsProductsForFeed) . PHP_EOL;

        $result = [];

        $i = 0;
        foreach ($analyticsProductsForFeed as $analyticsProduct) {
            $i++;
//            echo "[{$i}]" . PHP_EOL;
//            echo "Get data for product {$analyticsProduct['LOT_CODE']}" . PHP_EOL;
            if (isset($products[$analyticsProduct['LOT_CODE']])) {

                try {
                    $product = $products[$analyticsProduct['LOT_CODE']];

                    $result[$product['id']] = [
                        'id' => $product['id'],
                        'code' => $product['code'],
                        'name' => $product['name'],
                        'actual' => $analyticsProduct['ACTUAL'],
                    ];
                } catch (\Exception $e) {
                    echo $e->getMessage() . PHP_EOL;
                }

            } else {
                echo "CANT FIND" . PHP_EOL;
                //Почему то вызывает ошибку, точнее выполнение скрипта останавливается и товары не попадают в фид
//                echo "Can't find product '{$analyticsProductsForFeed['LOT_CODE']}'" . PHP_EOL;
            }
        }

        $this->analyticsProductsForFeed = $result;

        self::showTimer("getAnalyticsProductsForFeed");

        return $this;
    }

    public function getFlashPriceProducts()
    {
        self::initTimer("getFlashPriceProducts");

        $products = Product::find()
            ->select(['id'])
            ->onlyLot()
            ->onlyActive()
            ->andWhere(['badge_1' => Product::BADGE1_FLASH_PRICE])
            ->indexBy('id')
            ->asArray()
            ->all();

        if (App::isConsoleApplication()) {
            echo "Товаров (лотов) Выгода дня получено: " . count($products) . PHP_EOL;
        }

        self::showTimer("getFlashPriceProducts");

        return $products;
    }

    /**
     * @return CmsContentElementQuery
     */
    public function getProductQuery()
    {
        self::initTimer("getProductQuery");

        $return = Product::getQueryForFeed()
            ->forFeed();

        self::showTimer("getProductQuery");

        return $return;
    }

    public function _getExcludedCategoriesIds()
    {
        return [];
    }

    public function isForFeedByAnalytics(Product $product)
    {
        //Не добавляем товары которых нет в аналитике (проверяем карты, а в аналитике лоты, не путать)
        if ($product->isCard() && !isset($this->analyticsProductsForFeed[$product->parent_content_element_id])) {
            return false;
        } else {
            return true;
        }
    }

    public function isArrForFeedByAnalytics(array $product)
    {
        //Не добавляем товары которых нет в аналитике (проверяем карты, а в аналитике лоты, не путать)
        if ($product['content_id'] == Product::CARD && !$this->analyticsProductsForFeed[$product['parent_content_element_id']]) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * получить массив всех товаров из таблицы аналитики
     * @return array
     */
    protected function getAnalyticsProductsForFeedArray()
    {
        return \common\models\BUFECommFeed::find()
            ->select(['LOT_CODE', 'ACTUAL' => 'F'])
            ->andWhere(['!=', 'LOT_CODE', ''])
            ->andWhere(['not', ['LOT_CODE' => null]])
            ->indexBy('LOT_CODE')
//            ->limit(1000) //для тестов
            ->asArray()
            ->orderBy(['LOT_CODE' => SORT_ASC])
            ->all();
    }


    /**
     * установить массив справочник всех цветов
     * @return void
     */
    protected function setAllColors()
    {
        self::initTimer("get_all_colors");

        $colors = \Yii::$app->cache->getOrSet(
            "get_all_colors",
            function () {

                $colorsQuery = CmsContentElement::find()
                    ->select([
                        'cms_content_element.id',
                        'cms_content_element.name',
                    ])
                    ->andWhere(['cms_content_element.content_id' => [Color::COLOR_CONTENT_ID, Color::COLOR_BX_CONTENT_ID]]);

                foreach ($colorsQuery->each() as $color) {
                    $colors[$color->id] = $color->name;
                }

                return $colors;
            }, HOUR_2
        );

        self::$colors = $colors;

        self::showTimer("get_all_colors");

    }

    protected static function initTimer(string $name)
    {
        if (App::isConsoleApplication() && self::$profileMode) {
            Common::startTimer($name);
        }
    }

    protected static function showTimer(string $name)
    {
        if (App::isConsoleApplication() && self::$profileMode) {
            echo Common::getTimerTime($name) . PHP_EOL;
        }
    }

}