<?php
/**
 * Created by PhpStorm.
 * User: andrei
 * Date: 2019-03-16
 * Time: 14:06
 */

namespace common\models;


use common\components\MorpherAz;
use common\helpers\Common;
use common\helpers\Filter;
use common\lists\TreeList;
use common\models\generated\models\ShopProductPrice;
use common\models\OnAir\ProductCategory;
use common\models\query\CmsTreeQuery;
use common\seo\SeoFields;
use common\behaviors\SeoBehavior;
use frontend\models\search\ProductSearch;
use modules\shopandshow\models\common\GuidBehavior;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class CmsTree
 * @package common\models
 * @property        $minCost
 * @property        $saleMinCost
 * @property        $seo Seo
 * @property string $seoH1
 * @property integer $popularity Popularity
 *
 */
class CmsTree extends \skeeks\cms\models\CmsTree implements SeoFields
{
    const ID_ROOT = 1;
    const ID_CATALOG = 9;
    const ID_PROMO = 1811;
    const ID_LAST_SIZE = 2126;
    public $forceUpdateSeoFields = false;

    //Кол-во символов дополнительных пунктов меню не больше которого должна получаться менюха
    const NAV_ADDS_LETTERS_COUNT_LIMIT = 125;

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        return array_merge([
            GuidBehavior::className() => GuidBehavior::className(),
            'seo' => [
                'class' => SeoBehavior::class,
                'titleAttribute' => function () {
                    try {
                        if (empty($this->name)) {
                            return null;
                        }

                        if ($this->isCategoryFashion()) {

                            return $this->makeSeoTitleForCategoryFashion();

                        } elseif ($this->isCategoryFootwear()) {

                            return $this->makeSeoTitleForCategoryFootwear();

                        } elseif ($this->id == static::ID_LAST_SIZE) {
                            return "Последний размер – {categoryName}  {pageNumber} – купить по цене от {categoryMinPrice} руб – каталог скидок в официальном телемагазине Shop&Show";
                        } /*elseif ($this->id == static::ID_PROMO) {
                        return "Распродажа {pageNumber} – купить по цене от {categoryMinPrice} руб – каталог скидок в официальном телемагазине Shop&Show";
                    }*/ elseif ($this->isPromo()) {
                            return "Распродажа – {categoryName} {pageNumber} – купить по цене от {categoryMinPrice} руб – каталог скидок в официальном телемагазине Shop&Show";
                        } elseif ($this->isStaticPage() && $this->id != 1) {

                            return $this->makeSeoTitleForStaticPage();

                        } else {

                            $declension = Yii::$app->morpherAz
                                ->declension($this->name)
                                ->data;
                            $plural = ArrayHelper::getValue($declension, MorpherAz::PLURAL);
                            if ($plural) {
                                $accusative = ArrayHelper::getValue($plural, MorpherAz::ACCUSATIVE);
                                $genitive = mb_strtolower(ArrayHelper::getValue($plural, MorpherAz::GENITIVE));
                            } else {
                                $accusative = ArrayHelper::getValue($declension, MorpherAz::ACCUSATIVE);
                                $genitive = mb_strtolower(ArrayHelper::getValue($declension, MorpherAz::GENITIVE));
                            }
                            return "{$accusative} {pageNumber} – купить по цене от {$this->minCost} руб – каталог недорогих {$genitive} в официальном телемагазине Shop&Show";
                        }
                    } catch (\Throwable $e) {
                        Yii::error($e->getTraceAsString(), __METHOD__);
                    }
                    return null;
                },
                'h1Attribute' => function () {
                    if (empty($this->h1)) {
                        return "{$this->name} – официальный сайт телемагазина Shop&Show";
                    }
                    return $this->h1;
                },
                'descriptionAttribute' => function () {
                    try {
                        if (empty($this->name)) {
                            return null;
                        }

                        $declension = Yii::$app->morpherAz
                            ->declension($this->name)
                            ->data;

                        $plural = ArrayHelper::getValue($declension, MorpherAz::PLURAL);
                        $genitive = ArrayHelper::getValue($plural ?: $declension, MorpherAz::GENITIVE);

                        $profitable = ['выгодные', 'низкие', 'дешевые'];
                        $delivery = ['Оперативная', 'Быстрая'];
                        $range = ['Широкий', 'Огромный'];

                        $profitableIndex = array_rand($profitable);
                        $deliveryIndex = array_rand($delivery);
                        $rangeIndex = array_rand($range);

                        if ($this->isCatalog()) {
                            $genitive = mb_strtolower($genitive);
                            return "Продажа {$genitive} в Москве – {$profitable[$profitableIndex]} цены в официальном интернет-магазине Shop&Show. 
                        ✔{$range[$rangeIndex]} ассортимент товаров ✔{$delivery[$deliveryIndex]} доставка по всей России
                         ✔Регулярные акции и скидки ✔Гарантия на продукцию. 
                        Вежливые менеджеры круглосуточно окажут консультацию по телефону ☎ 8 (800) 301-60-10.";
                        } else {
                            return "{$genitive} – официальный сайт магазина на диване Shop&Show. ✔{$range[$rangeIndex]} ассортимент товаров 
                        ✔{$delivery[$deliveryIndex]} доставка по всей России ✔Регулярные акции и скидки 
                        ✔Гарантия на продукцию. Вежливые менеджеры круглосуточно окажут консультацию по телефону ☎ 8 (800) 301-60-10.";
                        }
                    } catch (\Throwable $e) {
                        Yii::error($e->getTraceAsString(), __METHOD__);
                    }
                    return null;
                },
                'slugAttribute' => 'dir',
                'forceAttribute' => function () {
                    return $this->forceUpdateSeoFields;
                },
            ],
        ], $behaviors);
    }

    public static function find()
    {
        return new CmsTreeQuery(get_called_class());
    }

    public function useInApp()
    {
        \Yii::$app->cms->setCurrentTree($this);
        \Yii::$app->breadcrumbs->setPartsByTree($this);
    }

    public function getView($viewFile)
    {
        if ($this->view_file) {
            $viewFile = $this->view_file;

        } else {
            if ($this->treeType) {
                if ($this->treeType->viewFile) {
                    $viewFile = $this->treeType->viewFile;
                } else {
                    $viewFile = $this->treeType->code;
                }
            }
        }

        return $viewFile;
    }


    /**
     * @return string
     */
    public function getSeoTitle()
    {
        return $this->getSeoValue('title');
    }

    /**
     * @return string
     */
    public function getSeoH1()
    {
        return ArrayHelper::getValue($this, 'seo.h1', $this->name);
    }

    /**
     * @return string
     */
    public function getSeoDescription()
    {
        return $this->getSeoValue('meta_description');
    }

    /**
     * @return string
     */
    public function getOpenGraphDescription()
    {
        return $this->getSeoDescription() ?: $this->description_full;
    }

    /**
     * @return string
     */
    public function getSeoKeywords()
    {
        return $this->getSeoValue('meta_keywords');
    }

    /**
     * @param $attribute
     * @param $defaultValue
     *
     * @return mixed
     */
    public function getSeoValue($attribute, $defaultValue = 'name')
    {
        return $this->seo && $this->seo->{$attribute} ? $this->seo->{$attribute} : $this->{$defaultValue};
    }

    /**
     * @return float
     */
    public function getMinCost()
    {
        $descendantsIds = TreeList::getDescendantsById($this->id);


        return (float)NewProduct::find()
            ->select(['new_price'])
            ->onlyPublic()
            ->onlyActive()
            ->andWhere(['IN', 'tree_id', $descendantsIds])
            ->andWhere(['content_id' => NewProduct::LOT])
            ->andWhere(['>', 'new_price', 2])
            ->andWhere(['>', 'new_quantity', 0])
            ->orderBy(['new_price' => SORT_ASC])
            ->limit(1)
            ->scalar();
    }

    /**
     * @return float
     */
    public function getSaleMinCost()
    {
        return (float)NewProduct::find()
            ->select(['new_price'])
            ->onlyPublic()
            ->priceType(ProductPriceType::SALE)
            ->onlyActive()
            ->andWhere(['content_id' => NewProduct::LOT])
            ->andWhere(['>', 'new_price', 2])
            ->andWhere(['>', 'new_quantity', 0])
            ->orderBy(['new_price' => SORT_ASC])
            ->limit(1)
            ->scalar();
    }

    /**
     * @return bool
     */
    public function isCatalog(): bool
    {
        return $this->compareWithPageType(static::ID_CATALOG);
    }

    /**
     * @return bool
     */
    public function isPromo(): bool
    {
        return $this->compareWithPageType(static::ID_PROMO);
    }

    /**
     * @return bool
     */
    public function isStaticPage(): bool
    {  //Главный раздел Текстовый раздел Лендинг стока
        return $this->tree_type_id == TREE_CATEGORY_ID_ROOT || $this->tree_type_id == PRODUCT_CONTENT_ID || $this->tree_type_id == 3;
    }

    /**
     * @return bool
     */
    public function isCategoryFashion(): bool
    {
        return $this->compareWithPageType(ProductCategory::ID_FASHION, [self::ID_ROOT, self::ID_CATALOG]);
    }

    /**
     * @return bool
     */
    public function isCategoryFootwear(): bool
    {
        return $this->compareWithPageType(ProductCategory::ID_FOOTWEAR, [self::ID_ROOT, self::ID_CATALOG]);
    }

    /**
     * @param int $categoryId
     * @param array $categories
     *
     * @return bool
     */
    protected function compareWithPageType(int $categoryId, array $categories = [self::ID_ROOT]): bool
    {
        $categories[] = $categoryId;
        $categories = implode('/', $categories);
        $pids = is_array($this->pids) ? implode('/', $this->pids) : $this->pids;

        return $this->id == $categoryId || (stristr($pids, $categories) !== false);
    }

    /**
     * Генерация для всех подразделов разделов «Мода», кроме страниц: https://shopandshow.ru/catalog/moda/
     * Купить [название категории <h1> мн ч, вин пад] по цене от [минимальная цена за товар] руб – каталог недорогой одежды в официальном телемагазине Shop&Show
     *
     * @return string|null
     * @throws \yii\base\Exception
     * @throws \yii\web\HttpException
     */
    protected function makeSeoTitleForCategoryFashion()
    {
        $declension = Yii::$app->morpherAz
            ->declension($this->name)
            ->data;

        $plural = ArrayHelper::getValue($declension, MorpherAz::PLURAL);
        $accusative = ArrayHelper::getValue($plural ?: $declension, MorpherAz::ACCUSATIVE);
        $accusative = mb_strtolower($accusative);
        return "Купить {$accusative} {pageNumber} – по цене от {$this->minCost} руб – каталог недорогой одежды в официальном телемагазине Shop&Show";
    }

    /**
     * Генерация для всех подразделов раздела «Обувь» кроме страниц:
     * https://shopandshow.ru/catalog/obuv/
     * https://shopandshow.ru/catalog/obuv/drugaya-obuv/
     * Купить [название категории <h1> мн ч, вин пад] по цене от [минимальная цена за товар] руб – каталог недорогой обуви в официальном телемагазине Shop&Show
     *
     * @return string|null
     * @throws \yii\base\Exception
     * @throws \yii\web\HttpException
     */
    protected function makeSeoTitleForCategoryFootwear()
    {
        $declension = Yii::$app->morpherAz
            ->declension($this->name)
            ->data;

        $plural = ArrayHelper::getValue($declension, MorpherAz::PLURAL);
        $accusative = ArrayHelper::getValue($plural ?: $declension, MorpherAz::ACCUSATIVE);
        $accusative = mb_strtolower($accusative);

        return "Купить {$accusative} {pageNumber} – по цене от {$this->minCost} руб – каталог недорогой обуви в официальном телемагазине Shop&Show";
    }

    /**
     * Для информационных разделов кроме страниц:
     * https://shopandshow.ru/
     *
     * @return string|null
     * @throws \yii\base\Exception
     * @throws \yii\web\HttpException
     */
    protected function makeSeoTitleForStaticPage()
    {
        return "{$this->name} – официальный сайт телемагазина Shop&Show";
    }

    public function getChildren()
    {
        return $this->hasMany(static::class, ['pid' => 'id']);
    }

    public function getRelatedPropertyValue(int $propertyId)
    {
        return $this
            ->hasOne(CmsTreeProperty::class, [
                'element_id' => 'id',
            ])
            ->where(['property_id' => $propertyId]);
    }

    public function getColumn()
    {
        return $this->getRelatedPropertyValue(CmsTreeTypeProperty::findIdByCode(CmsTreeTypePropertyCode::COLUMN));
    }

    public function getRightBannerImage()
    {
        return $this->getRelatedPropertyValue(CmsTreeTypeProperty::findIdByCode(CmsTreeTypePropertyCode::RIGHT_BANNER_IMAGE));
    }

    public function getRightBannerLink()
    {
        return $this->getRelatedPropertyValue(CmsTreeTypeProperty::findIdByCode(CmsTreeTypePropertyCode::RIGHT_BANNER_LINK));
    }

    public function getRightBannerName()
    {
        return $this->getRelatedPropertyValue(CmsTreeTypeProperty::findIdByCode(CmsTreeTypePropertyCode::RIGHT_BANNER_NAME));
    }

    public function getRightBannerSubTitleFirst()
    {
        return $this->getRelatedPropertyValue(CmsTreeTypeProperty::findIdByCode(CmsTreeTypePropertyCode::RIGHT_BANNER_SUB_TITLE_FIRST));
    }

    public function getRightBannerSubTitleSecond()
    {
        return $this->getRelatedPropertyValue(CmsTreeTypeProperty::findIdByCode(CmsTreeTypePropertyCode::RIGHT_BANNER_SUB_TITLE_SECOND));
    }

    public static function getChildrenIds($idRoot)
    {
        return self::find()
//            ->andWhere(['pid' => $idRoot])
            ->select(['id'])
            ->byParent($idRoot)
            ->column();
    }

    //Возвращает список товаров для раскрывашки с доп пунктов основного меню
    public static function getRelatedProductsForTopNav($treeId, $count = 4, $onlyCanSale = true)
    {
        //Необходимо получить товары Выгоды часа для указанного разедала, если нет или недостаточно - побуем получить для уровней выше
        //Если до верхнего раздела так и не набралось товаров - пробуем по той же схеме получить список Хитов

        $badgeFlashPriceId = Product::BADGE1_FLASH_PRICE;
        $badgeHitId = Product::BADGE2_HIT;

        //Будем искать сначала лоты! Так как только у лотов проставлен раздел
        $productsQueryBase = Product::find()
            ->onlyLot()
            ->select(['id']);

        if ($onlyCanSale) {
            $productsQueryBase->canSale();
        }

        $productsQueryBase->limit($count);
        $productsQueryBase->indexBy('id');

        //Что бы не было проблем с изменением раздела в запросе при использовании одного объекта
        $productsQuery = clone $productsQueryBase;
        $productsQuery->andWhere(['tree_id' => $treeId]);
        $productsQuery->andWhere(['badge_1' => $badgeFlashPriceId]);

        $products = $productsQuery->all();

        $tree = CmsTree::findOne($treeId);

        if (count($products) < $count) {
            //Берем ГУИД раздела, ищем в BUF_ECommN1234 и находим n1 и n4
            //По n1/n4 в BUF_ECommABC находим раздел и искомые товары
            //Связсь с БД аналитики только на бою, учитываем
            if ($tree && YII_ENV == YII_ENV_PROD) {
                if ($treeGuid = $tree->guid->getGuid()) {

                    if ($analTreeData = BUFECommN1234::findOne(['g4' => $treeGuid])) {
                        $analProductsQuery = BUFECommABC::find()
                            ->where([
                                    'n1' => $analTreeData->n1,
                                    'n4' => $analTreeData->n4]
                            )
                            ->andWhere(['>', 'OFFCNT_ID', 0])
                            ->limit(6);
                        $analProducts = $analProductsQuery->all();

                        if ($analProducts) {
                            /** @var BUFECommABC $analProduct */
                            foreach ($analProducts as $analProduct) {
                                if ($product = Product::findOne(['code' => $analProduct->LotCode])) {
                                    $products[$product->id] = $product;
                                }
                            }
                        }
                    }
                }
            }
        }

        //ОТКЛЮЧЕНО так как идея с товарами из вешестоящих раздело в выглядела по факту не очень (
        if (false && count($products) < $count) {
            $treeParentsIds = $tree ? $tree->getParentsIds() : [];
            if ($treeParentsIds) {
                $treeParentsIds = array_reverse($treeParentsIds, true);
            }

            $badgesTypes = [
//                $badgeFlashPriceId => 1, //По выгоде искали изначально, еще раз искать смысла нет
                $badgeHitId => 2,
            ];

            //перебираем плашки (типы товаров) которые подходят
            foreach ($badgesTypes as $badgeId => $badgeTypeId) {
                //Перебираем разделы вверх по дереву проверяя нашлось ли достаточное кол-во товаров
                foreach ($treeParentsIds AS $searchTreeId) {
                    if (count($products) < $count && $searchTreeId != TREE_CATEGORY_ID_CATALOG) {
                        $productsQuery = clone $productsQueryBase;

                        $searchTreeIds = TreeList::getDescendantsById($searchTreeId);
                        $searchTreeIds[] = $searchTreeId;

                        if ($searchTreeIds) {
                            $productsQuery->andWhere([
                                'tree_id' => $searchTreeIds,
                                "badge_{$badgeTypeId}" => $badgeId
                            ]);
                            $productsAdd = $productsQuery->all();
                            if ($productsAdd) {
                                foreach ($productsAdd as $product) {
                                    if (!isset($products[$product->id])) {
                                        $products[$product->id] = $product;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        //Если что то нашлось, то нашлись лоты. Теперь необходимо перейти к карточкам
        if ($products) {
            $result = [];
            /** @var Product $product */
            foreach ($products as $product) {
                if ($card = Product::getCardCanSaleWithMinPrice($product->id)) {
                    $result[] = $card;
                }
            }

            $result = array_slice($result, 0, $count);
        }

        return $result ?? [];
    }

    public static function getFilterBrandsForTopNav($treeId)
    {
        $brands = [];
        $params = [];
        $params['tree_id'] = $treeId;

        $searchModel = new ProductSearch();
        $dataProvider = $searchModel->search($params);
        $onlyPrice = false;
        $filterGroups = Filter::getFilterGroups($searchModel, [], $onlyPrice);
        $filterGroupsIndexed = \common\helpers\ArrayHelper::index($filterGroups, 'code');
        $filterBrands = ArrayHelper::getValue($filterGroupsIndexed, 'KFSS_BRAND.filters');

        if ($filterBrands) {
            //Для составления ссылки на фильтр понадобится класс поисковой модели
            $modelClass = Common::getObjectClassShortName($searchModel);
            $filterParamsKey = 'productParams';
            $filterParamId = 8;
            foreach ($filterBrands as $filterBrand) {
                $brands[] = [
                    'name' => $filterBrand['name'],
                    'value' => $filterBrand['value'],
                    'filterUrl' => "{$modelClass}[{$filterParamsKey}][{$filterParamId}][]={$filterBrand['value']}",
                ];
            }
        }

        return $brands ?? [];
    }

    //Возвращает топ брендов для категории верхнего уровня относительно текущего раздела
    public function getHighLevelTreeTopBrands ($params = [])
    {
        if ($this->pid == TREE_CATEGORY_ID_CATALOG) {
            $treeTopLevel = $this;
        }else{
            $treeParentsIds = $this->getParentsIds();

            if ($treeParentsIds) {
                //Какой то из родителей должен быть с родителем-корнем каталога
                $treeTopLevel = self::find()
                    ->andWhere([
                        'id' => $treeParentsIds,
                        'pid' => TREE_CATEGORY_ID_CATALOG,
                    ])
                    ->one();
            }
        }

        if ($treeTopLevel) {
            $onlyCanSale = isset($params['only_can_sale']) ? (bool)$params['only_can_sale'] : false;
            $brands = Brand::getPopularByTree(['tree_id' => $treeTopLevel->id, 'only_can_sale' => $onlyCanSale, 'limit' => $params['limit'] ?? false]);
        }

        return $brands ?? [];
    }
}