<?php
/**
 * Created by PhpStorm.
 * User: ubuntu5
 * Date: 23.09.17
 * Time: 16:25
 */

namespace common\helpers;

use common\models\PromoSchedule;
use common\models\Setting;
use common\models\Tree;
use modules\shopandshow\models\shares\SsShare;
use yii\db\Expression;
use common\models\PromoTree;

/**
 * Class Promo
 * @package common\helpers
 */
class Promo
{
//    public static $countBannersNeed = 3;
    public static $countBannersNeed = 100;

    /**
     * Признак 999
     * @return bool
     */
    public static function is999()
    {
        return Setting::getIs999() || (date('Y-m-d H') >= date('2020-01-25 00') && date('Y-m-d H') <= date('2020-01-27 00'));
        //return date('Y-m-d H') >= date('2019-03-31 00') && date('Y-m-d H:i:s') <= date('2019-04-01 07:59:59');
    }

    /**
     * Признак КБ
     * @return bool
     */
    public static function isCyberMonday()
    {
        return date('Y-m-d H') >= date('2019-01-28 08') && date('Y-m-d H:i:s') <= date('2019-02-01 07:59:59');
    }

    /**
     * Признак 8Марта
     * @return bool
     */
    public static function is8March()
    {
        return false; //date('Y-m-d H') >= date('2019-03-0 08') && date('Y-m-d H:i:s') <= date('2018-03-12 07:59:59');
    }

    public static function isCyberApril()
    {
        $prizeCounter = new \common\widgets\promo\april2018\PrizeCounter();

        return false; //date('Y-m-d H:i:s') >= date($prizeCounter->getDateStart()) && date('Y-m-d H:i:s') <= date($prizeCounter->getDateEnd());
    }


    /**
     * Признак акция ко дню расии
     * @return bool
     */
    public static function isRussiaDay()
    {
        return false; //date('Y-m-d H') >= date('2018-06-10 08') && date('Y-m-d H:i:s') <= date('2018-06-12 07:59:59');
    }

    /**
     * Признак акция Дневная распродажа
     * @return bool
     */
    public static function isDaySale()
    {
        return (
            (date('Y-m-d H') >= date('2018-10-19 07') && date('Y-m-d H:i:s') <= date('2018-10-19 19:59:59'))
            || (date('Y-m-d H') >= date('2018-10-20 07') && date('Y-m-d H:i:s') <= date('2018-10-20 19:59:59'))
        );
    }

    /**
     * Признак акция Ночная распродажа
     * @return bool
     */
    public static function isNightSale()
    {
        return (
            (date('Y-m-d H') >= date('2018-10-18 20') && date('Y-m-d H:i:s') <= date('2018-10-19 06:59:59'))
            || (date('Y-m-d H') >= date('2018-10-19 20') && date('Y-m-d H:i:s') <= date('2018-10-20 06:59:59'))
        );
    }

    /**
     * Признак акция Fix Price
     * @return bool
     */
    public static function isFixPriceSale()
    {
        return date('Y-m-d H') >= date('2018-09-01 08') && date('Y-m-d H:i:s') <= date('2018-09-02 07:59:59');
    }

    /**
     * Признак акция Fix Price2
     * @return bool
     */
    public static function isFixPriceSale2()
    {
        return date('Y-m-d H') >= date('2018-09-02 08') && date('Y-m-d H:i:s') <= date('2018-09-03 07:59:59');
    }

    /**
     * Признак акция Stock Sale
     * @return bool
     */
    public static function isStockSale()
    {
        return date('Y-m-d H') >= date('2018-09-03 08') && date('Y-m-d H:i:s') <= date('2018-09-04 07:59:59');
    }

    /**
     * Признак акция Stock Sale
     * @return bool
     */
    public static function isStockSale2()
    {
        return date('Y-m-d H') >= date('2018-09-04 08') && date('Y-m-d H:i:s') <= date('2018-09-05 07:59:59');
    }

    /**
     * Признак акция Final Sale
     * @return bool
     */
    public static function isFinalSale()
    {
        return date('Y-m-d H') >= date('2019-02-14 08') && date('Y-m-d H:i:s') <= date('2019-02-18 07:59:59');
    }

    /**
     * Признак акция Fasion Sale
     * @return bool
     */
    public static function isFashionSale()
    {
        return date('Y-m-d H') >= date('2018-10-26 08') && date('Y-m-d H:i:s') <= date('2018-10-27 07:59:59');
    }

    /**
     * Признак акция Weekend Sale
     * @return bool
     */
    public static function isWeekendSale()
    {
        return date('Y-m-d H') >= date('2018-11-10 00') && date('Y-m-d H:i:s') <= date('2018-11-13 07:59:59');
    }

    /**
     * Признак акция Autumn Sale
     * @return bool
     */
    public static function isGiftsWeekSale()
    {
        return date('Y-m-d H') >= date('2019-01-02 08') && date('Y-m-d H:i:s') <= date('2019-01-08 07:59:59');
    }

    /**
     * Признак акция Mega Sale
     * @return bool
     */
    public static function isMegaSale()
    {
        return date('Y-m-d H') >= date('2018-08-27 08') && date('Y-m-d H:i:s') <= date('2018-08-29 07:59:59');
    }

    /**
     * Признак акция NonStop распродажа
     * @return bool
     */
    public static function isNonstopSale()
    {
        return date('Y-m-d H') >= date('2018-10-12 08') && date('Y-m-d H:i:s') <= date('2018-10-15 07:59:59');
    }

    /**
     * Признак акция Доствка за 199 при заказе менее 4990 (ShopDiscount::PROMO_SHIPPING_ORDER_PRICE)
     * @return bool
     */
    public static function isPromoShippingPeriod()
    {
        return date('Y-m-d H') >= date('2018-09-03 08') && date('Y-m-d H:i:s') <= date('2020-09-30 07:59:59');
    }

    public static function isPromoDr7()
    {
        return date('Y-m-d H') >= date('2019-10-04 08') && date('Y-m-d H:i:s') <= date('2019-10-13 23:59:59');
    }

    /**
     * Признак акции черной пятницы 2018
     * @return bool
     */
    public static function isBlackFriday()
    {
        return date('Y-m-d H') >= date('2018-11-18 00') && date('Y-m-d H:i:s') <= date('2018-11-25 06:59:59');
    }

    /**
     * Признак акции когда надо показывать калькулятор подбора подарков
     * @return bool
     */
    public static function isPromoGiftCalc()
    {
        return date('Y-m-d H') >= date('2018-11-18 00') && date('Y-m-d H:i:s') <= date('2018-12-31 23:59:59');
    }

    /**
     * Возвращает id лотов, участвующие в АБ тесте старых и молодых моделей
     * @return array
     */
    public static function getStarieVsMolodieProducts()
    {
        static $products = [
            //220933, //тест
            //221576, //тест

            924788,
            925484,
            927044,
            927189,
            930620,
            931477,
            931910,
            931913,
            932150,

            929398,
            930142,
            930443,
            931463,
            931810,
            931911,
            931914,


            /*            237180, // 150304
                        920482, // 4236174
                        924788, // 4261169
                        925484, // 4268268
                        927044, // 4277359
                        927189, // 4283188
                        929398, // 4307352
                        930142, // 4315072
                        930443, // 4315431
                        930620, // 4316020
                        931463, // 4324886
                        931477, // 4325848
                        931810, // 4329249
                        931910, // 4329381
                        931913, // 4329511
                        931914, // 4329512
                        931911, // 4329518
                        932150, // 4330177*/

        ];
        return $products;
    }

    /**
     * Проверяет, является ли продукт тестовым для АБ-теста старые/молодые модели
     * @param $productId
     * @return bool
     */
    public static function isProductStarieVsMolodie($productId)
    {
        return false;
        //return in_array($productId, self::getStarieVsMolodieProducts());
    }

    /**
     * Вычисление признака "низкого сезона" для изменения цены бесплатной доставки
     * @return bool
     */
    public static function isLowSeasonPeriod()
    {
        return date('Y-m-d') >= date('2018-05-12') && date('Y-m-d H:i:s') <= date('2019-12-08 06:59:59');
    }

    /**
     * Получить ссылку на распродажу
     * @return string
     */
    public static function getSaleUrl()
    {
        return '/promo/sales/';
    }

    /**
     * Получить ссылку на акции
     * @return string
     */
    public static function getActionsUrl()
    {
        return '/promo';
    }

    public static function isSpringSale()
    {
        return false;
    }

    /**
     * Признак зимней распродажи
     * @return bool
     */

    public static function isWinterSale()
    {
        return date('Y-m-d') >= date('2018-12-10') && date('Y-m-d H:i:s') <= date('2018-12-17 07:59:59');
    }

    public static function isMarchSale()
    {
        return date('Y-m-d H') >= date('2019-03-16 07') && date('Y-m-d H:i:s') <= date('2019-03-18 06:59:59');
    }

    public static function getWinterSaleLink()
    {
        $date = date('H:i:s') > date('07:59:59') ? date('Y-m-d') : date('Y-m-d', strtotime("-1 days"));
        $link = '/promo/sale/';
        switch ($date) {
            case '2018-12-10' :
                $link = '/promo/prazdnichnaya-rasprodaja-moda/';
                break;
            case '2018-12-11' :
                $link = '/promo/prazdnichnaya-rasprodaja-dom/';
                break;
            case '2018-12-12' :
                $link = '/promo/prazdnichnaya-rasprodaja-ukrasheniya/';
                break;
            case '2018-12-13' :
                $link = '/promo/prazdnichnaya-rasprodaja-ukrasheniya/';
                break;
            case '2018-12-14' :
                $link = '/promo/prazdnichnaya-rasprodaja-obuv/';
                break;
            case '2018-12-15' :
                $link = '/promo/prazdnichnaya-rasprodaja-elektronika/';
                break;
            case '2018-12-16' :
                $link = '/promo/prazdnichnaya-rasprodaja-elektronika/';
                break;
                break;
        }

        return $link;
    }

    public static function isJanuarySale2019()
    {
        return date('Y-m-d') >= date('2019-01-09') && date('Y-m-d H:i:s') <= date('2019-01-14 07:59:59');
    }

    /**
     * Список акций
     * @return array
     */
    public static function findAll(): array
    {
        return [
            [
                'isActive' => static::is999(),
                'url' => '/promo/999/',
                'name' => 'Распродажа 999',
                'banner' => '/v2/site/img/promo/day/20190224/button.png'
            ],
            [
                'isActive' => static::isCyberMonday(),
                'url' => '/promo/kiberponedelnik/',
                'name' => 'Кибер Понедельник',
                'banner' => '/v2/site/img/promo/day/20190128/button.png'
            ],
            [
                'isActive' => false && static::isFashionSale(),//::todo спросить почему выкл жестко через false
                'url' => '/promo/fashion-weekend/',
                'name' => 'Fashion Weekend',
                'banner' => '/v2/site/img/promo/day/20181026/button.png'
            ],
            [
                'isActive' => static::isNightSale(),
                'url' => '/promo/stock/noch-rasprodaja/',
                'name' => 'Ночная распродажа',
                'banner' => '/v2/site/img/promo/day/20181018/button.png'
            ],
            [
                'isActive' => static::isGiftsWeekSale(),
                'url' => '/promo/nedelya-podarkov/',
                'name' => 'Неделя подарков',
                'banner' => '/v2/site/img/promo/day/20190102/button.png'
            ],
            [
                'isActive' => static::isFixPriceSale(),
                'url' => '/promo/fix-price-prodolzhaetsya/',
                'name' => 'FixPrice распродажа',
                'banner' => '/v2/site/img/promo/day/20180903/button.png'
            ],
            [
                'isActive' => static::isFixPriceSale2(),
                'url' => '/promo/stock/fix-price-sale/',
                'name' => 'FixPrice распродажа',
                'banner' => '/v2/site/img/promo/day/20180902/button.png'
            ],
            [
                'isActive' => static::isStockSale(),
                'url' => static::getSaleUrl(),
                'name' => 'Жаркая распродажа',
                'banner' => '/v2/site/img/promo/day/20180903/button.png?v=1'
            ],
            [
                'isActive' => static::isStockSale2(),
                'url' => static::getSaleUrl(),
                'name' => 'Жаркая распродажа',
                'banner' => '/v2/site/img/promo/day/20180904/button.png?v=1'
            ],
            [
                'isActive' => static::isMegaSale(),
                'url' => '/promo/stock/mega-rasprodaja/',
                'name' => 'Мега распродажа',
                'banner' => '/v2/site/img/promo/day/20180827/button.png?v=1'
            ],

            [
                'isActive' => static::isNonstopSale(),
                'url' => '/promo/stock/non-stop-shopping/',
                'name' => 'Шоппинг нон-стоп',
                'banner' => '/v2/site/img/promo/day/20181012/button.png?v=1'
            ],
            [
                'isActive' => static::isBlackFriday(),
                'url' => '/promo/black-friday/',
                'name' => 'Черная пятница',
                'banner' => '/v2/site/img/promo/day/20181122/button.png?v=1'
            ],
            [
                'isActive' => static::isFinalSale(),
                'url' => '/promo/totalnaya-likvidatsiya/',
                'name' => 'Тотальная распродажа',
                'banner' => '/v2/site/img/promo/day/20190211/button.png?v=1'
            ],
            [
                'isActive' => static::isMarchSale(),
                'url' => '/promo/vesennyaya-rasprodaja/',
                'name' => 'Весенняя распродажа',
                'banner' => '/v2/site/img/promo/day/20190307/button.png?v=1'
            ],
            [
                'isActive' => static::isPromoGiftCalc(),
                'url' => '/gift2019/',
                'name' => 'Мега распродажа',
                'banner' => '/v2/site/img/promo/black-friday-2018/present.gif?v=181220'
            ],

        ];
    }

    /**
     * Список активных акций
     * @return array
     */
    public static function findAllActive(): array
    {
        return array_filter(static::findAll(), function ($promo) {
            return $promo['isActive'];
        });
    }

    /**
     * Первое активное промо из списка
     * @return array
     */
    public static function findOneActive(): array
    {
//        $model = static::findAllActive();
        $model = SsShare::find()->byDate(time())->byBannerType(SsShare::BANNER_TYPE_LABEL)->active()->one();
        $promo = false;
        if ($model) {
            $promo = [
                'isActive' => true,
                'url' => $model->url,
                'name' => $model->name,
                'banner' => $model->getImageSrc(),
            ];
        }
//        $promo = array_shift($model);
        return $promo ?: static::findDefault();
    }

    protected static function findDefault()
    {
        return [
            'isActive' => true,
            'url' => self::getActionsUrl(),
            'name' => 'Акции',
            'banner' => '',
            'class' => 'bg-cinnabar-a text-white'
        ];
//        return [
//            'isActive' => true,
//            'url' => self::getSaleUrl(),
//            'name' => '% Распродажа',
//            'banner' => ''
//        ];
    }

    //Фикс ошибочных вариантов написания купонов
    public static function fixCoupon($coupon)
    {
        if (mb_stripos($coupon, 'счастливая') !== false && mb_stripos($coupon, '7') !== false) {
            $coupon = 'Счастливая 7';
        }
        if (mb_stripos($coupon, 'shop') !== false && mb_stripos($coupon, '7') !== false) {
            $coupon = 'SHOP7';
        }

        return $coupon;
    }

    //Если есть купон который действует в данный момент, то вернем его
    public static function getCouponHint()
    {
        return self::getPromoHint('coupon');

        $coupon = '';

        if (date('Y-m-d H') >= date('2019-10-14 08') && date('Y-m-d H:i:s') <= date('2019-10-18 23:59:59')) {
            $coupon = '5 звезд';
        }
        return $coupon;
    }

    public static function getPromoHint($source = false)
    {
        switch ($source) {
            case 'product':
            case 'cart':
                return PromoSchedule::showActual($source);
                break;
            case 'coupon':
                return PromoSchedule::getMainCoupon();
                break;
        }

        return '';
    }

    public static function resetCountViewsDay()
    {
        $sql = "UPDATE " . \common\models\Promo::tableName() . " SET count_views_day = 0 
        WHERE 1=1";
        \Yii::$app->db->createCommand($sql, [])->execute();;
    }

    //todo функция для принудительногшо вывода списка определенных баннеров в дни акций
    public static function getPromosBannersBlockCustom($promoIds = [])
    {
        if (!count($promoIds)) {
            $promoIds = [
                26,
                1713,
                1714,
                1726,
                1727,
                1715,
                1716,
                1717,
                1718,
                1719,
                1720,
                1722,
                1723,
                1721,
                1724
            ];
        }

        $banners = [];
        $query = \common\models\Promo::findPromosActionQuery();
        $query->andWhere(['IN', 'id', $promoIds]);

        $query->orderBy(new \yii\db\Expression('rand()'));
        $result = $query->limit(self::$countBannersNeed)->all();
        foreach ($result as $k => $model) {
            $banners[$k]['name'] = $model->name;
            $banners[$k]['link'] = $model->getLink();
            $banners[$k]['src'] = $model->getImageBanner();
        }
        return $banners;
    }

    public static function getPromosBannersBlock($params = [], $apiFlag = false)
    {
//        $debag = true;
        $debag = false;

        $countBannersNeed = self::$countBannersNeed;

        if ($debag) {
            echo '<pre>';
            print_r($params);
            echo '</pre>';
        }

        if (!$params['case']) {
            return false;
        }

        $id_category = isset($params['id_category']) ? $params['id_category'] : null;
        $pid_category = isset($params['pid_category']) ? $params['pid_category'] : null;
        $id_promo = isset($params['id_promo']) ? $params['id_promo'] : null;
        $flag_show = false;
        $subquery = null;

        $query = \common\models\Promo::findPromosActionQuery();

        switch ($params['case']) {

            //Страница каталога первого уровня
            //промо без привязанной категории
            //три случайные сборки с баннерами
            case 'catalog_first_level':
            case 'promo':
            default:

                if ($id_promo) {
                    $query->andWhere(['!=', 'id', $id_promo]);
                }

                $count = $query->count();
                if ($count >= $countBannersNeed) {
                    $flag_show = true;
                } else {
                    //если не набираем необходимое количество, то ничего не показываем
                    $result = null;
                }
                break;

            //Страница каталога 2 уровня
            //три случайные сборки из дочерних для данной категории
            case 'catalog_second_level':

                if (!$id_category) {
                    return null;
                }

                $childrenCats = Category::getIdsCategoriesByPid($id_category);

                if ($childrenCats) {
                    $query->andWhere(['IN', 'tree_id_onair', $childrenCats]);
                    $count = $query->count();
                    if ($count >= $countBannersNeed) {
                        $flag_show = true;
                    } else {
                        //если не набираем необходимое количество, то ничего не показываем
                        $result = null;
                    }
                } else {
                    $result = null;
                }
                break;

            //Страница каталога 3 уровня
            //Три случайных сборки из этой категории
            case 'catalog_third_level':

                if (!$id_category) {
                    return null;
                }

                $query->andWhere(['tree_id_onair' => $id_category]);
                $count = $query->count();
//                if ($count >= $countBannersNeed) {
//                    $flag_show = true;
//                } else {
                //если не набираем необходимое количество, то добиваем подборками из смежных категорий(имеющих с данной ощего радителя)
                if (!$pid_category) {
                    return null;
                }

                $childrenCats = Category::getIdsCategoriesByPid($pid_category);

                if ($childrenCats) {

                    $subquery = \common\models\Promo::findPromosActionQuery();
                    $subquery->andWhere(['IN', 'tree_id_onair', $childrenCats]);
                    $count += $subquery->count();
                    if ($count >= $countBannersNeed) {
                        $flag_show = true;
                    } else {
                        $result = null;
                    }

                } else {
                    $result = null;
                }
//                }
                break;

            //Страница каталога 4 уровня
            //Три случайных сборки из категории на 1 уровень выше
            case 'catalog_fourth_level':

                if (!$id_category) {
                    return null;
                }
                if (!$pid_category) {
                    return null;
                }

                $childrenCats = Category::getIdsCategoriesByPid($pid_category);
                if ($childrenCats) {
                    $query->andWhere(['IN', 'tree_id_onair', $childrenCats]);
                } else {
                    $query->andWhere(['tree_id_onair' => 0]);
                }
                $count = $query->count();

                if ($count >= $countBannersNeed) {
                    $flag_show = true;
                } else {

                    //Если не набираем необходимое количество, то добираем сборками из категорий на 2 уровня выше(2 уровень)

                    $subquery = \common\models\Promo::findPromosActionQuery();

                    $pidCategory = Tree::findOne($pid_category);
                    if (!$pidCategory) {
                        return null;
                    }
                    $childrenCats = Category::getIdsCategoriesByPid($pidCategory->pid);
                    if (!$childrenCats) {
                        return null;
                    }

                    $subquery->andWhere(['IN', 'tree_id_onair', $childrenCats]);
                    $count += $subquery->count();
                    if ($count >= $countBannersNeed) {
                        $flag_show = true;
                    } else {
                        $result = null;
                    }
                }

                break;

            //Промо с привязанной категорией
            //Три случайные сборки из этой же категории

            case 'promo_with_category':
//                $id_category = $params['id_category'];
                if (!$id_category) {
                    $result = null;
                }
                $query->andWhere(['tree_id_onair' => $id_category]);
                if ($id_promo) {
                    $query->andWhere(['!=', 'id', $id_promo]);
                }
                $count = $query->count();
                if ($count >= $countBannersNeed) {
                    $flag_show = true;
                } else {
                    $subquery = \common\models\Promo::findPromosActionQuery();
                    if ($id_promo) {
                        $subquery->andWhere(['!=', 'id', $id_promo]);
                    }
                    $count += $subquery->count();
                    if ($count >= $countBannersNeed) {
                        $flag_show = true;
                    } else {
                        $result = null;
                    }
                }
                break;
        }

        $query->orderBy(new \yii\db\Expression('rand()'));
        if ($subquery) {
            $subquery->orderBy(new \yii\db\Expression('rand()'));
        }
        if ($debag) {

            if ($query) {
                $q = $query->createCommand()->getRawSql();
                echo '<pre>';
                print_r($q);
                echo '</pre>';
            }

            if ($subquery) {
                $qs = $subquery->createCommand()->getRawSql();
                echo '<pre>';
                print_r($qs);
                echo '</pre>';
            }
        }

        $return = [];
        $result = $query->limit($countBannersNeed)->all();
        foreach ($result as $k => $model) {
            if ($apiFlag) {
                $return[] = $model->id;
            } else {
                $return[$k]['name'] = $model->name;
                $return[$k]['link'] = $model->getLink();
                $return[$k]['src'] = $model->getImageBanner();
            }
        }
        $count_isset = count($return);
        if ($subquery) {
            $result = $subquery->limit($countBannersNeed - $count_isset)->all();
            foreach ($result as $k => $model) {
                if ($apiFlag) {
                    $return[] = $model->id;
                } else {
                    $return[$k + $count_isset]['name'] = $model->name;
                    $return[$k + $count_isset]['link'] = $model->getLink();
                    $return[$k + $count_isset]['src'] = $model->getImageBanner();
                }
            }
        }

        return $return;

    }

    public static function getPromoBannersSimpleBlock($count = 2, $rand = false)
    {
        $promoBanners = [];
        if ($rand) {
            $promoBannersData = \common\models\Promo::findPromosQuery()
                ->limit($count)
                ->orderBy(new Expression('rand()'))
                ->all();
        } else {
            $promoBannersData = \common\models\Promo::findPromosMainQuery()
                ->limit($count)
                ->all();
        }

        if ($promoBannersData) {
            foreach ($promoBannersData as $k => $promoModel) {
                $promoBanners[$k]['id'] = $promoModel->id;
                $promoBanners[$k]['link'] = $promoModel->getLink();
                $promoBanners[$k]['name'] = $promoModel->name;
                $promoBanners[$k]['img'] = $promoModel->getImageBanner();
                $promoBanners[$k]['count_views_day'] = $promoModel->count_views_day;
                $promoBanners[$k]['count_views'] = $promoModel->count_views;
                $promoBanners[$k]['priority'] = $promoModel->priority;
//                $promoBanners[$k]['rating'] = $promoModel->rating;

                //todo уточнить, где брать этот параметр
                $promoBanners[$k]['creative'] = 'Вариант 1';
            }
        }
        return $promoBanners;
    }


    public static function getPromoBannersToCatalog($paramsBannersQuery, $apiFlag = false)
    {
        //todo реализовать функционал создания специального списка для вывода особых банеров для различных акций через админку
        $dateStart = new \DateTime('2020-04-23 00:00:00');
        $dateEnd = new \DateTime('2020-04-25 00:00:00');
        $promoIds = [2043, 2044, 2045, 2047, 2049, 2050, 2175, 2048];

        $time = time();
        if ($time > $dateStart->format('U') && $time < $dateEnd->format('U')) {
            if ($apiFlag) {
                $banners = $promoIds;
            } else {
                $banners = self::getPromosBannersBlockCustom($promoIds);
            }
        } else {
            $banners = self::getPromosBannersBlock($paramsBannersQuery, $apiFlag);
        }
        return $banners;
    }

    public static function addPromoTree($promo_id, $tree_id)
    {
        $promoTree = PromoTree::find()
            ->andWhere(['=', 'promo_id', $promo_id])
            ->andWhere(['=', 'tree_id', $tree_id])
            ->one();
        if (!$promoTree) {
            try {
                $promoTree = new PromoTree();
                $promoTree->promo_id = $promo_id;
                $promoTree->tree_id = $tree_id;
                $promoTree->save();
                return true;
            } catch (\yii\db\Exception $exception) {
                return true;
            }
        }
    }

    public static function deletePromoTrees($promo_id)
    {
        $sql = "DELETE FROM " . PromoTree::tableName() . " WHERE promo_id = :promo_id";

        \Yii::$app->db->createCommand($sql, [
            ':promo_id' => $promo_id,
        ])->query();
    }


    public static function deletePromoTree($promo_id, $tree_id)
    {
        $sql = "DELETE FROM " . PromoTree::tableName() . " WHERE promo_id = :promo_id AND tree_id = :tree_id";

        \Yii::$app->db->createCommand($sql, [
            ':promo_id' => $promo_id,
            ':tree_id' => $tree_id,
        ])->query();
    }

    public static function getPromoMillionUserData($kfssUserId = 0)
    {
        $userId = User::getAuthorizeId();

        $data = [
            'userId' => $userId ?: false,
            'isInPromo' => false,
            'balanceWeek' => 0,
            'balanceMonth' => 0,
            'rateWeek' => 0,
            'rateMonth' => 0,
        ];

        if ($kfssUserId) {
            $getBalanceResponse = \Yii::$app->kfssLkApiV2->getMillionPromoUserBalance($kfssUserId);

            if ($getBalanceResponse && isset($getBalanceResponse['code'])) {
                switch ($getBalanceResponse['code']) {
                    case 0:
                        $data['isInPromo'] = true;
                        $data['balanceWeek'] = $getBalanceResponse['weekValue'];
                        $data['balanceMonth'] = $getBalanceResponse['monthValue'];
                        $data['rateWeek'] = $getBalanceResponse['weekRating'];
                        $data['rateMonth'] = $getBalanceResponse['monthRating'];
                        break;
                    case 4:
                        //Не участвует в промо
                        break;
                    default:
                        //Прочие неинтересные ошибки
                }
            } else {
                //Какой то эррор )
            }
        }

        return $data;
    }

}