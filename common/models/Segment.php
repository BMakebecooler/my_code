<?php


namespace common\models;

use common\helpers\App;
use common\helpers\ArrayHelper;
use common\helpers\Common;
use common\helpers\Segment as SegmentHelper;
use common\helpers\Size;


class Segment extends \common\models\generated\models\Segment
{
    public $hideActionProducts = false;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'sort'], 'required'],
            [['description', 'products', 'first_products', 'disable_products'], 'string'],
            [['active', 'generated', 'disabled', 'created_at', 'updated_at', 'created_by', 'updated_by', 'only_discount', 'calc_price_modifications', 'hide_from_catalog', 'regenerate', 'start_timestamp', 'end_timestamp', 'modification_available_percent', 'without_sale'], 'integer'],
            [['price_from', 'price_to', 'sale_from', 'sale_to'], 'number'],
            [['name', 'generated_file', 'sort', 'name_lot'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'name' => 'Название сегмента',
            'description' => 'Описание',
            'active' => 'Активность',
            'only_discount' => 'Только товары со скидкой',
            'regenerate' => 'Постоянно перегенерировать',
            'hide_from_catalog' => 'Скрыть товары из каталога',
            'price_from' => 'Цена ОТ',
            'price_to' => 'Цена ДО',
            'sale_from' => 'Скидка ОТ',
            'sale_to' => 'Скидка ДО',
            'sort' => 'Сортировка',
            'param_types' => 'Типы свойств для фильтра',
            'color' => 'Цвет',
            'etalon_clothing_size' => 'Размер одежды',
            'etalon_shoe_size' => 'Размер обуви',
            'etalon_sock_size' => 'Размер носок',
            'etalon_cap_size' => 'Размер головного убора',
            'etalon_jewelry_size' => 'Размер ювелирный',
            'etalon_textile_size' => 'Размер текстиля',
            'etalon_pillow_size' => 'Размер подушки',
            'etalon_bed_linen_size' => 'Размер постельного белья',
            'etalon_bra_size' => 'Размер бюстгалтера',
            'brand' => 'Брэнд',
            'season' => 'Сезон',
            'tree_ids' => 'Разделы каталога',
            'price_types' => 'Типы цен',
            'disable_products' => 'Продукты исключаемые из сборки',
            'first_products' => 'Первые товары в сборке',
            'generated' => 'Использовать список товаров из файла',
            'generated_file' => 'Файл со списком товаров, в формате CSV',
            'start_timestamp' => 'Начало активности',
            'end_timestamp' => 'Конец активности',
            'modification_available_percent' => 'Процент доступных модификаций лота',
            'calc_price_modifications' => 'Использовать цену карточки при фильтрации по цене',
            'without_sale' => 'Только товары без скидки',
            'badge' => 'Бейдж 1',
            'badge_2' => 'Бейдж 2',
        ]);
    }

    // TODO: обновляем времмнную метку для сброса кеша
    public function update($runValidation = true, $attributeNames = null)
    {
        $this->updated_at = time();
        return parent::update($runValidation, $attributeNames);
    }

    public static function getSegmentsData()
    {
        $return = self::find()
            ->select(['name as value', 'name as label'])
            ->asArray()
            ->all();
        return $return;
    }


    public static function getSegments($onlyFree = false)
    {
        $return = [
            0 => 'Выберите'
        ];
        if ($onlyFree) {
            $data = self::find()
                ->leftJoin(Promo::tableName() . ' AS p', 'p.segment_id = ' . self::tableName() . '.id')
                ->andWhere(['is', 'p.segment_id', null])
                ->orderBy('name')
                ->asArray()
                ->all();
        } else {
            $data = self::find()->orderBy('name')->asArray()->all();
        }
        foreach ($data as $row) {
            $return[$row['id']] = $row['name'];
        }
        return $return;
    }

    public function buildProductsList()
    {
        //Флаг не дать создавать выборку если не установлено ни одного критерия
        $flagStart = false;
        $params = [];
        $aviliableModificationLots = [];
        $etalons = array_keys(Size::$etalons);
        $sortLots = \common\helpers\Segment::getLotsSort($this->first_products);
        $productsQuery = Product::find()
            ->select([Product::tableName() . '.id'])
            ->onlyActive()
            ->onlyLot()
            ->groupBy(Product::tableName() . '.id');

        $productsQuery->leftJoin('product_param_product pp', 'pp.lot_id = ' . Product::tableName() . '.id');

        if ($this->name_lot) {

            $words = explode("\n", $this->name_lot);
            $condition = [];
            foreach ($words as $word) {
                $word = ltrim(rtrim($word));
                if (strlen($word)) {
                    $condition[] = '(' . Product::tableName() . ".name like '%" . $word . "%' or " . Product::tableName() . ".new_lot_name like '%" . $word . "%'" . ')';
                }
            }
            if (count($condition)) {
                $condition = implode(' or ', $condition);
                $productsQuery->andWhere($condition);
                $flagStart = true;
            }
//            $productsQuery->andWhere([
//                'or',
//                ['like', Product::tableName().'.name', '%'.$this->name_lot.'%',false],
//                ['like', Product::tableName().'.new_lot_name', '%'.$this->name_lot.'%',false]
//            ]);
        }

        if ($this->modification_available_percent) {

            if (App::isConsoleApplication()) {
                echo "Выбран максимальный процент доступных модификаций : " . $this->modification_available_percent . PHP_EOL;
            }

            $lots = Product::find()->onlyLot()->onlyActive();
            foreach ($lots->each() as $lot) {

                $countEnabledMods = 0;
                $countAllMods = 0;

                $allMods = Product::getProductOffers($lot->id);
                if ($allMods) {
                    $countAllMods = count($allMods);
                }
                $enabledMods = Product::getProductOffersCanSale($lot->id);
                if ($enabledMods) {
                    $countEnabledMods = count($enabledMods);
                }

                if ($countAllMods && $countEnabledMods) {
                    $persent = 100 * $countEnabledMods / $countAllMods;
                    if ($persent <= $this->modification_available_percent) {
                        if (App::isConsoleApplication()) {
                            echo "Неликвид лот : " . $lot->id . ' процент : ' . $persent . PHP_EOL;
                        }
                        $aviliableModificationLots[] = $lot->id;
                    }
                }
            }
            $flagStart = true;
        }
//        if(!$this->calc_price_modifications){
        if ($this->price_from) {
            $productsQuery->andWhere(['>=', 'new_price', $this->price_from]);
            $flagStart = true;
        }
        if ($this->price_to) {
            $productsQuery->andWhere(['<=', 'new_price', $this->price_to]);
            $flagStart = true;
        }
//        }

        if ($this->badge) {
            $this->badge = unserialize($this->badge);
            if (is_array($this->badge) && count($this->badge)) {
                $productsQuery->andWhere(['IN', 'badge_1', $this->badge]);
                $flagStart = true;
            }
        }

        if ($this->badge_2) {
            $this->badge_2 = unserialize($this->badge_2);
            if (is_array($this->badge_2) && count($this->badge_2)) {
                $productsQuery->andWhere(['IN', 'badge_2', $this->badge_2]);
                $flagStart = true;
            }
        }


        if ($this->without_sale) {
            $productsQuery->andWhere(['=', 'new_discount_percent', 0]);
            $productsQuery->andWhere('new_price = new_price_old');
            $flagStart = true;
        } else {

            if ($this->sale_from) {
                $productsQuery->andWhere(['>=', 'new_discount_percent', $this->sale_from]);
                $flagStart = true;
            }

            if ($this->sale_to) {
                $productsQuery->andWhere(['<=', 'new_discount_percent', $this->sale_to]);
                $flagStart = true;
            }
        }

        if ($this->tree_ids) {
            $this->tree_ids = unserialize($this->tree_ids);
            if (count($this->tree_ids)) {
                $productsQuery->andWhere(['IN', 'tree_id', $this->tree_ids]);
                $flagStart = true;
            }
        }

        if ($this->color) {
            $this->color = unserialize($this->color);
            if (count($this->color)) {
                $params = array_merge($params, $this->color);
            }
        }

        foreach ($etalons as $part) {
            if ($this->$part) {
                $sizes = unserialize($this->$part);
                if (count($sizes)) {
                    $params = array_merge($params, $sizes);
                }
            }
        }

        if ($this->season) {
            $this->season = unserialize($this->season);
            if (count($this->season)) {
                $params = array_merge($params, $this->season);
            }
        }

        if ($this->brand) {
            $this->brand = unserialize($this->brand);
            if (count($this->brand)) {
                $params = array_merge($params, $this->brand);
            }
        }

        if (count($params)) {
            $productsQuery->andWhere(['IN', 'pp.product_param_id', $params]);
            $flagStart = true;
        }

        if ($this->price_types) {
            $price_types = unserialize($this->price_types);
            if (count($price_types)) {
//                $productsQuery->leftJoin(ShopProductPrice::tableName().' as spp','spp.product_id = '.Product::tableName().'.id');
//                $productsQuery->leftJoin(ShopTypePrice::tableName().' as stp','stp.id = '.Product::tableName().'.new_price_active');
                $productsQuery->andWhere(['IN', Product::tableName() . '.new_price_active', $price_types]);
                $flagStart = true;
            }
        }
        if ($this->hideActionProducts) {
            $productsQuery->andWhere(Product::tableName() . ".id not in(select ssp.product_id from ss_shares_products ssp
                left join ss_shares ss on `ss`.`id` = `ssp`.`banner_id`
                where (ss.active = 'Y' AND `ss`.is_hidden_catalog = 1)
            )");
            $flagStart = true;
        }
        if ($this->only_discount) {
            $productsQuery->andWhere(['>', Product::tableName() . '.new_discount_percent', 0]);
            $productsQuery->andWhere(Product::tableName() . '.new_price < ' . Product::tableName() . '.new_price_old');
            $flagStart = true;
        }

        if (count($aviliableModificationLots)) {
            $productsQuery->andWhere(['IN', Product::tableName() . '.id', $aviliableModificationLots]);
            $flagStart = true;
        }
        //TODO проверить запрос для дебага
//        $q = $productsQuery->createCommand()->getRawSql();
//        echo '<pre>';
//        print_r($q);
//        echo '</pre>';
//        die();

        $return = [];

        if ($flagStart) {

            if (count($sortLots)) {
                foreach ($sortLots as $lot_id => $sort) {
                    $return[] = [
                        'product_id' => $lot_id,
                        'sort' => $sort,
                        'first' => $sort
                    ];
                }
            }

            foreach ($productsQuery->each() as $product) {
                if (isset($sortLots[$product->id])) {
                    continue;
                }

                $return[] = [
                    'product_id' => $product->id,
                    'sort' => 1,
                    'first' => 0
                ];
            }
        }

        return $return;

    }

    public function getProductsCount()
    {
        if (SegmentHelper::$mode == SegmentHelper::CARD_MODE) {
            return SegmentProductCard::getCardsCount($this->id);
        } else {
            return SegmentProduct::getProductsCount($this->id);
        }
    }

    public function getProductsIds($limit = null)
    {
        return SegmentProduct::getProducts($this->id, $limit);
    }

    public static function getSegmentIdsForSchedule()
    {
        $return = [];
        $segments = self::find()
            ->andWhere([
                'or',
                ['not', ['badge' => null]],
                ['not', ['badge_2' => null]]
            ])
            ->andWhere(['=', 'active', 1])
            ->andWhere(['=', 'generated', 0]);

        foreach ($segments->each() as $segment) {
            $bage_1 = unserialize($segment->badge);
            $bage_2 = unserialize($segment->badge_2);

            if (!count($bage_1) && !count($bage_2)) {
                continue;
            }

            $return[] = $segment->id;
        }

        return $return;
    }

    public function isBadge()
    {
        $flag = false;
        if ($this->badge && count(unserialize($this->badge)) > 0) {
            $flag = true;
        }
        if ($this->badge_2 && count(unserialize($this->badge_2)) > 0) {
            $flag = true;
        }

        return $flag;
    }

    public function getTreeIds()
    {
        $treeIds = [];
        $lots = Product::find()
            ->onlyLot()
            ->canSale()
            ->leftJoin(SegmentProductCard::tableName(),
                SegmentProductCard::tableName() . '.lot_id=' . Product::tableName() . '.id')
            ->andWhere([SegmentProductCard::tableName() . '.segment_id' => $this->id])
            ->groupBy([SegmentProductCard::tableName() . '.lot_id']);

        foreach ($lots->each() as $lot) {
            if ($lot->tree->active == Common::BOOL_Y) {
                $treeIds[$lot->tree_id] = $lot->tree_id;
            }
        }
        return $treeIds;
    }

}