<?php

namespace common\models\filtered\products;

use common\helpers\Promo;
use common\lists\TreeList;
use modules\shopandshow\models\shares\SsShareProduct;
use modules\shopandshow\models\statistic\ShopProductStatistic;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Query;

class Catalog extends Model
{

    /**
     * Сколько товаров на странице
     */
    const DEFAULT_PER_PAGE = 59;

    const INFINITE_PER_PAGE = 40;

    /**
     * Сколько максимум в выборке
     */
    const PER_PAGE_MAX_LIMIT = 119;

    /**
     * Виды пагинации
     */
    const PER_PAGE_TYPES = [
        39, self::DEFAULT_PER_PAGE, self::PER_PAGE_MAX_LIMIT, self::INFINITE_PER_PAGE
    ];

    public $sort = "recommend";
    public $category = null;
    public $subcategory = null;
    public $inStock = 1;

    public $perPage = self::DEFAULT_PER_PAGE;

    const  COOKIE_PAGE_SIZE_NAME = 'ss_page_size';

    public function rules()
    {
        return [
            ['sort', 'string'],
            [['inStock', 'perPage', 'category'], 'integer'],
            ['perPage', 'in', 'allowArray' => true, 'range' => self::PER_PAGE_TYPES],
            ['perPage', 'number', 'min' => self::PER_PAGE_TYPES[0], 'max' => self::PER_PAGE_MAX_LIMIT],
        ];
    }

    public function init()
    {
        if ($sort = Yii::$app->request->get('sort')) {
            $this->sort = $sort;
        }

        if ($category = Yii::$app->request->get('category')) {
            $this->category = $category;
        }

        if ($subcategory = Yii::$app->request->get('subcategory')) {
            $this->subcategory = $subcategory;
        }

        $perPage = (int)\Yii::$app->request->get('per-page', self::DEFAULT_PER_PAGE);

        $cookies = Yii::$app->request->cookies;
        $hasPerPageCookie = $cookies->has(self:: COOKIE_PAGE_SIZE_NAME);

        if (!$perPage && $hasPerPageCookie && $val = $cookies->getValue(self:: COOKIE_PAGE_SIZE_NAME)) {
            $perPage = (int)$val;
        }

        $perPage = ($perPage > self::PER_PAGE_MAX_LIMIT) ? self::DEFAULT_PER_PAGE : $perPage;

        if ($perPage) {
            if (!in_array($perPage, self::PER_PAGE_TYPES)) {
                $perPage = self::DEFAULT_PER_PAGE;
            }
            $this->perPage = $perPage;

            setcookie(self:: COOKIE_PAGE_SIZE_NAME, $this->perPage, time() + YEAR, '/');
        }

        parent::init();
    }

    public function attributeLabels()
    {
        return [];
    }

    public function search(ActiveDataProvider $activeDataProvider)
    {
        $query = $activeDataProvider->query;

        /**
         * @var $query \yii\db\ActiveQuery
         */

        /*$query->addSelect([
            '`cms_content_element`.*',
            'IF(`shop_product`.`quantity` > 0, 1, 0) as quant'
        ]);*/

//        $query->addOrderBy(['quant' => SORT_DESC]);

        if ($this->sort) {
            switch ($this->sort) {
                case ('efir'):
                case ('recommend'):

                    //Поднятие наверх спец товаров для киберпонедельника
                    if (Promo::isCyberMonday()){
                        $cyberProducts = [217752, 1013376, 1078727, 1081064, 1089872, 1114980, 1126364];
                        $query->addOrderBy(new \yii\db\Expression('IF(cms_content_element.id IN ('.(implode(', ', $cyberProducts)).'), 1, 0) DESC'));
                    }
                    // сначала выбираем товары, которые находятся в текущем часе эфира
                    $query->addOrderBy(new \yii\db\Expression('IF(unix_timestamp(now()) - air_day_product_time.begin_datetime between 0 and 3600, 0, 1) ASC'));
                    // далее сортируем по дате выхода в эфир (т.е. все эфирные лоты поднимаем наверх)
                    $query->addOrderBy(['air_day_product_time.begin_datetime' => SORT_DESC]);

                    // для 999 дополнительно сортируем по цене, 999 сверху
                    if (Promo::is999()) {
                        $query->addOrderBy(new \yii\db\Expression("if(ss_shop_product_prices.price=999,0,1) ASC"));
                    }

                    if ($this->sort == 'recommend') {
                        $query->leftJoin(ShopProductStatistic::tableName() . ' AS super_sort',
                            "super_sort.id = cms_content_element.id");
                        $query->addOrderBy(new \yii\db\Expression('super_sort.k_stock DESC'));
                        $query->addOrderBy(new \yii\db\Expression('super_sort.k_1 DESC'));
                    }

                    break;
                case ('lucky'):

                    $query->addOrderBy(['air_day_product_time.begin_datetime' => SORT_DESC]);

                    $query->leftJoin(ShopProductStatistic::tableName() . ' AS super_sort',
                        "super_sort.id = cms_content_element.id");
                    $query->addOrderBy(new \yii\db\Expression('super_sort.k_2 DESC'));


                    break;
                case ('cheap'):
                    $query->addOrderBy(['ss_shop_product_prices.price' => SORT_ASC]);
                    break;
                case ('expensive'):
                    $query->addOrderBy(['ss_shop_product_prices.price' => SORT_DESC]);
                    break;
                case ('sale'):
                    $baseTypePriceId = \Yii::$app->shop->baseTypePrice->id;
                    $query->addOrderBy(new \yii\db\Expression("if(ss_shop_product_prices.type_price_id={$baseTypePriceId},0,1) DESC"));
                    $query->addOrderBy(['ss_shop_product_prices.discount_percent' => SORT_DESC]);
                    break;
                case ('popular'):
                    //old sort
                    //$query->addOrderBy(['show_counter' => SORT_DESC]);

                    $query->leftJoin(ShopProductStatistic::tableName() . ' AS super_sort',
                        "super_sort.id = cms_content_element.id");
                    $query->addOrderBy(['super_sort.ordered' => SORT_DESC]);
                    $query->addOrderBy(['super_sort.k_1' => SORT_DESC]);
                    break;
                case ('new'):
                    $query->addOrderBy(['cms_content_element.created_at' => SORT_DESC]);
                    break;
                case ('quantity'):
                    $query->leftJoin(ShopProductStatistic::tableName() . ' AS super_sort',
                        "super_sort.id = cms_content_element.id");
                    $query->addOrderBy(new \yii\db\Expression('super_sort.k_quantity DESC'));
                    break;
                case ('stock'):
                    $query->leftJoin(ShopProductStatistic::tableName() . ' AS super_sort',
                        "super_sort.id = cms_content_element.id");
                    $query->addOrderBy(['super_sort.k_stock' => SORT_DESC]);
                    $query->addOrderBy(['super_sort.k_1' => SORT_DESC]);
                    break;
            }
        }

        $query->limit($this->perPage);

        if ($this->inStock == 1) {
            //$query->andWhere(['>=', 'shop_product.quantity', 1]);
        }

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getSortLabel()
    {
        switch ($this->sort) {
            case 'efir':
            default:
                return 'Недавно в эфире';
            case 'cheap':
                return 'Сначала дешевые';
            case 'expensive':
                return 'Сначала дорогие';
            case 'sale':
                return 'По размеру скидки';
            case 'popular':
                return 'Хиты';
            case 'recommend':
                return 'Рекомендуемые';
            case 'lucky':
                return 'Мне повезет';
            case 'quantity':
                return 'По размерам в наличии';
            case 'stock':
                return 'Сток';
            case 'new':
                return 'Новинки';
        }
    }

    /**
     * активеность фильтра
     * @param $sort
     * @return bool
     */
    public function isSort($sort)
    {
        return $this->sort === $sort;
    }

    /**
     * показывать ли фильтр пользователю
     * @param $sort
     * @return bool
     */
    public function isShowFilter($sort)
    {
        if ($sort == 'quantity') {
            if (!\common\helpers\User::isDeveloper()) {
                return false;
            }
            $tree = \Yii::$app->cms->currentTree;
            if (!$tree || !$tree->id) {
                return false;
            }

            $availTrees = array_filter([
                TreeList::getTreeByDir('promo', false),
                TreeList::getTreeByCode('moda'),
                TreeList::getTreeByCode('obuv'),
                TreeList::getTreeByCode('dom'),
                TreeList::getTreeByCode('ukrasheniya'),
            ]);

            foreach ($availTrees as $availTree) {
                if ($availTree->id == $tree->id || in_array($availTree->id, (array)$tree->pids)) {
                    return true;
                }
            }
            return false;
        }

        return true;
    }

    /**
     * Скрыть "скрытые" товары из выборки
     * @param ActiveDataProvider $activeDataProvider
     */
    public function hideProduct(ActiveDataProvider $activeDataProvider)
    {
        /**
         * @var $query \yii\db\ActiveQuery
         */
        $query = $activeDataProvider->query;

        $subQuery = new Query();
        $subQuery->distinct()->select('product_id')
            ->from(['shares_products' => SsShareProduct::tableName()])
            ->andWhere("is_hidden_catalog = 1");

        $query->andWhere(['NOT', ['cms_content_element.id' => $subQuery]]); // Если будут проблемы с производительностью, переписать на Join
    }

}
