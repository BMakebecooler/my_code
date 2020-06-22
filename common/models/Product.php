<?php

namespace common\models;

use common\helpers\Category;
use common\helpers\Color;
use common\helpers\Common;
use common\helpers\Product as ProductHelper;
use common\helpers\Property;
use common\helpers\Strings;
use common\models\generated\models\CmsContentElementImage;
use Yii;
use common\components\MorpherAz;
use common\thumbnails\Thumbnail;
use common\behaviors\SeoBehavior;
use common\models\query\CmsContentElementQuery;
use common\helpers\ArrayHelper;

//use common\models\generated\models\ShopProduct;
use common\models\generated\models\SsGuids;
use modules\shopandshow\models\common\StorageFile;

//use modules\shopandshow\models\shop\SsShopProductPrice;
use yii\data\ArrayDataProvider;
use yii\db\Expression;
use yii\helpers\Html;

/**
 * Class Product
 * @package common\models
 *
 * @property Brand $brand
 * @property array $cardsOnlyActive
 * @property string $url
 *
 * @property Product $lot
 *
 */
class Product extends \common\models\CmsContentElement
{
//    use \modules\shopandshow\models\traits\SsContentElement;

    const LOT = 2;
    const CARD = 5;
    const MOD = 10;

//    public $url; //Убрано что бы срабатывал мазический метод
    public $forceUpdateSeoFields = false;

    const USE_SITE_PRICES = true;

    const BADGE_BESTSELLER_LIMIT = 3;
    const BADGE_HIT_LIMIT = 3;
    const BADGE2_SUPER_DISCOUNT_LIMIT = 35; //процент скидки на товар

    const BADGE1_CTS = 80;
    const BADGE1_ON_AIR_DAY = 40;
    const BADGE1_ON_AIR_WEEK = 20;
    const BADGE1_PRIME = 73;
    const BADGE1_FLASH_PRICE = 75;

    const BADGE2_BESTSELLER = 70;
    const BADGE2_HIT = 60;
    const BADGE2_SALEDRIVER = 30;
    const BADGE2_FAVORITE_PRODUCT = 50;
    const BADGE2_SUPER_DISCOUNT = 10;

    const BADGE2_ADD2CTS = 200;
    const BADGE2_PAY_ATTENTION = 210;

    const BADGE_FAVORITE_RATIO = 0.035; //1 - это 100%

    public static $badgesInfo = [
        self::BADGE1_CTS => ['code' => 'cts', 'label' => 'Товар дня', 'type' => 1],
        self::BADGE1_ON_AIR_DAY => ['code' => 'on-air-day', 'label' => 'В эфире', 'type' => 1],
        self::BADGE1_ON_AIR_WEEK => ['code' => 'on-air-week', 'label' => 'Недавно в эфире', 'type' => 1],
        self::BADGE1_PRIME => ['code' => 'prime', 'label' => 'PRIME', 'type' => 1],
        self::BADGE1_FLASH_PRICE => ['code' => 'flash-price', 'label' => 'Выгода на час', 'type' => 1],

        self::BADGE2_BESTSELLER => ['code' => 'bestseller', 'label' => 'Бестселлер', 'type' => 2],
        self::BADGE2_HIT => ['code' => 'hit', 'label' => 'Хит', 'type' => 2],
        self::BADGE2_FAVORITE_PRODUCT => ['code' => 'favorite', 'label' => 'Любимый товар', 'type' => 2],
        self::BADGE2_SUPER_DISCOUNT => ['code' => 'super-discount', 'label' => 'Суперскидка', 'type' => 2],
        self::BADGE2_ADD2CTS => ['code' => 'add2cts', 'label' => 'Добавьте к товару дня', 'type' => 2],
        self::BADGE2_PAY_ATTENTION => ['code' => 'pay-attention', 'label' => 'Обратите внимание', 'type' => 2],
    ];

    public function behaviors()
    {
        $new = [
            'seo' => [
                'class' => SeoBehavior::class,
                'titleAttribute' => function () {
                    try {
                        if (empty($this->name)) {
                            return null;
                        }
                        $model = $this->isLot() ? $this : ProductHelper::getLot($this->id);
                        $name = empty($model->new_lot_name) ? $model->name : $model->new_lot_name;
                        return "{$name} – лот {$model->new_lot_num} – купить по низкой цене в интернет-магазине Shop&Show";
                    } catch (\Throwable $e) {
                        Yii::error($e->getTraceAsString(), __METHOD__);
                    }
                    return null;

                },
                'h1Attribute' => function () {
                    $product = ProductHelper::getLot($this->id);
                    return empty($product->new_lot_name) ? $product->name : $product->new_lot_name;
                },
                'descriptionAttribute' => function () {

                    try {
                        if (empty($this->name)) {
                            return $this->meta_description;
                        }

                        $model = $this->isLot() ? $this : ProductHelper::getLot($this->id);
                        $name = empty($model->new_lot_name) ? $model->name : $model->new_lot_name;


                        $declension = Yii::$app->morpherAz
                            ->declension($name)
                            ->data;

                        $plural = ArrayHelper::getValue($declension, MorpherAz::PLURAL);
                        $genitive = ArrayHelper::getValue($plural ?: $declension, MorpherAz::GENITIVE);

                        $profitable = ['выгодные', 'низкие', 'дешевые'];
                        $delivery = ['Оперативная', 'Быстрая'];
                        $range = ['Широкий', 'Огромный'];
                        $profitableIndex = array_rand($profitable);
                        $deliveryIndex = array_rand($delivery);
                        $rangeIndex = array_rand($range);


                        $categoryGenitive = '';
                        if ($model->tree->name) {
                            $categoryDeclension = Yii::$app->morpherAz
                                ->declension($model->tree->name)
                                ->data;
                            $plural = ArrayHelper::getValue($categoryDeclension, MorpherAz::PLURAL);
                            $categoryGenitive = ArrayHelper::getValue($plural ?: $categoryDeclension, MorpherAz::GENITIVE);
                        }
                        return "Продажа {$genitive} в Москве – {$profitable[$profitableIndex]} цены в официальном интернет-магазине Shop&Show.
                         ✔{$range[$rangeIndex]} ассортимент {$categoryGenitive} ✔{$delivery[$deliveryIndex]} доставка по всей России
                          ✔Регулярные акции и скидки ✔Гарантия на продукцию. 
                          Вежливые менеджеры круглосуточно окажут консультацию по телефону ☎ 8 (800) 301-60-10.";

                    } catch (\Throwable $e) {
                        Yii::error($e->getTraceAsString(), __METHOD__);
                    }
                    return null;

                },
                'slugAttribute' => 'code',
                'forceAttribute' => function () {
                    return $this->forceUpdateSeoFields;
                }
            ],
            'history' => [
                'class' => \nhkey\arh\ActiveRecordHistoryBehavior::className(),
                'ignoreFields' => [
                    'id',
                    'created_by',
                    'updated_by',
                    'created_at',
                    'updated_at',
                    'published_at',
                    'published_to',
                    'priority',
                    'active',
                    'name',
                    'image_id',
                    'image_full_id',
                    'code',
                    'description_short',
                    'description_full',
                    'content_id',
                    'tree_id',
                    'show_counter',
                    'show_counter_start',
                    'meta_title',
                    'meta_description',
                    'meta_keywords',
                    'description_short_type',
                    'description_full_type',
                    'parent_content_element_id',
                    'bitrix_id',
                    'is_base',
                    'count_children',
                    'guid_id',
                    'kfss_id',
                    'count_images',
                    'new_guid',
                    'new_lot_num',
                    'new_lot_name',
                    'new_characteristics',
                    'new_technical_details',
                    'new_product_kit',
                    'new_advantages',
                    'new_advantages_addons',
                    'new_not_public',
                    'new_quantity',
                    'new_rest',
//                    'new_price_active',
//                    'new_price',
//                    'new_price_old',
//                    'new_discount_percent',
                    'new_brand_id',
                    'new_season_id',
                    'new_rating',
                ]
            ],
        ];

        return ArrayHelper::merge($new, parent::behaviors());
    }


    static $propertyesGuids = [
        '72D67C218019C11AE0538201090AB50A' => 'new_characteristics',
        '72D67C21801CC11AE0538201090AB50A' => 'new_advantages',
        '72D67C218026C11AE0538201090AB50A' => 'new_advantages_addons',
        '72D67C21801AC11AE0538201090AB50A' => 'new_product_kit',
        '72D67C218021C11AE0538201090AB50A' => 'new_technical_details',
        '62E18FAAAE9F1E5FE0538201090A587C' => 'new_not_public',
    ];

    /**
     * Причина такого костыля в контструктое \common\models\generated\models\CmsContentElement
     * отсутстувет возможность передачи аттрибутов в конструктор
     * @param $config
     *
     * @return Product
     */
    public static function create($config)
    {
        $model = new static();
        $model->setAttributes($config);
        $model->id = $config['id'];
        return $model;

    }

    /**
     * @inheritdoc
     * @return CmsContentElementQuery
     */
    public static function find()
    {
        return new CmsContentElementQuery(get_called_class());
    }

    public function getSeo()
    {
        return $this->hasOne(Seo::class, ['owner_id' => 'id'])->andWhere([
            'owner' => static::class
        ]);
    }

    /**
     * @param $id
     *
     * @return mixed
     * @throws \Throwable
     */
    public static function getFromCache($id)
    {
        $product = self::getDb()->cache(function ($db) use ($id) {
            return self::findOne($id);
        }, HOUR_1);

        if (!$product) {
            \Yii::error("Can't get product model for ID='" . var_export($id, true) . "'", __METHOD__);
        }

        return $product;
    }

    public static function getType($value = false)
    {
        $array = [
            self::LOT => 'Lot',
            self::CARD => 'Card',
            self::MOD => 'Mod',
        ];

        return $value ? $array[$value] : $array;
    }

    public function beforeValidate()
    {
        if ($this->new_not_public == 'Y') {
            $this->new_not_public = 1;
        } else {
            $this->new_not_public = 0;
        }
        return parent::beforeValidate();
    }

    public function getColorData()
    {
        $color = $this->getColorParam();
        $colorBx = $this->getColorParamBx();

        if (!$color && !$colorBx) {
            return false;
        }

        $colorName = (!empty($color['name']) && $color['name'] != 'UNDEFINED') ? $color['name'] : '';
        $colorBxName = (!empty($colorBx['name']) && $colorBx['name'] != 'UNDEFINED') ? $colorBx['name'] : '';

        return [
            'name' => $colorBxName ?: $colorName,
            'id' => $colorBxName ? $colorBx['id'] : $color['id'],
            'hex' => $color['hex'] ?? '',
            'hexs' => $color['hexs'] ?? [],
            'color' => $color,
            'colorBx' => $colorBx,
        ];
    }

    /** Получить массив данных о цвете элемента (из связанного свойства KFSS_COLOR) //актуально только дя карточек
     *
     * @return array|generated\models\CmsContentElement|mixed|string|null
     */
    public function getColorParam()
    {
        //Отключим кеш в этом месте так как похоже из-за него застревают неверные цвета
        //$return = \Yii::$app->cache->get('color-param-' . $this->id);
        $return = false;

        if ($return === false) {
            $color = CmsContentElement::find()
                ->select([
                    'cms_content_element.id',
                    'cms_content_element_property.element_id AS product_id',
                    'cms_content_element.name',
                    'cms_content_element.content_id',
                    'cms_content_element.guid_id'
                ])
                ->leftJoin('cms_content_element_property', CmsContentElementProperty::tableName() . '.value = ' . CmsContentElement::tableName() . '.id')
                ->where(['element_id' => $this->id])
                ->andWhere(['property_id' => 231])
                ->asArray()
                ->one();

            if ($color) {
                $colorHex = [];
                foreach (explode(',', $color['name']) as $colorName) {
                    if ($hex = Color::getHexFromName($colorName)) {
                        $colorHex[] = $hex;
                    }
                }

//                $color['hex'] = Color::getHexFromName($color['name']);
                $color['hex'] = current($colorHex); //Первый цвет как основной
                $color['hexs'] = $colorHex;
                $return = $color;
            } else {
                return '';
            }
            //\Yii::$app->cache->set('color-param-' . $this->id, $return, MIN_15);
        }
        return $return;
    }

    /** Получить массив данных о цвете картинки элемента (из связанного свойства KFSS_COLOR_BX) //актуально только дя карточек
     *
     * @return array|generated\models\CmsContentElement|mixed|string|null
     */
    public function getColorParamBx()
    {
        $return = \Yii::$app->cache->get('color-param-bx-' . $this->id);
        if ($return === false) {
            $color = CmsContentElement::find()
                ->select([
                    'cms_content_element.id',
                    'cms_content_element_property.element_id AS product_id',
                    'cms_content_element.name',
                    'cms_content_element.content_id',
                    'cms_content_element.guid_id'
                ])
                ->leftJoin('cms_content_element_property', CmsContentElementProperty::tableName() . '.value = ' . CmsContentElement::tableName() . '.id')
                ->where(['element_id' => $this->id])
                ->andWhere(['property_id' => 284])
                ->asArray()
                ->one();

            if ($color) {
                $return = $color;
            } else {
                return '';
            }
            \Yii::$app->cache->set('color-param-bx-' . $this->id, $return, MIN_15);
        }
        return $return;
    }

    /** Получить массив цветов из карточек товара
     *
     * @param $productId - идентификатор любой сущности связанной с товаром
     * @param bool $onlyCanSale - только карточки доступные для продажи
     * @return array
     */
    public static function getLotColors($productId, $onlyCanSale = true)
    {
        $result = [];
        $product = ProductHelper::getLot($productId);

        if ($product) {
            $cardsQuery = $onlyCanSale ? Product::getProductCardsCanSaleQuery($product->id) : Product::getProductCardsQuery($product->id)->onlyActive()->notHiddenFromCatalogImage();

            if ($cards = $cardsQuery->all()) {
                /** @var Product $card */
                foreach ($cards as $card) {
                    //* Учет базовой карточки *//
                    //[DEPRECATED??? С выводом всех карточек типа уже не актуально]Если карточек больше 1, то кроме базовой точно что то есть, значит ее (базовую) пропускаем
                    if (count($cards) > 1 && $card->is_base == Common::BOOL_Y) {
                        //continue;
                    }
                    //* /Учет базовой карточки *//

                    //* Пропускаем только карточки без фото *//

                    //Так как проверки на базовость/разные цвета не дают гарантий
                    if (!$card->image_id) {
                        continue;
                    }

                    //* /Пропускаем только карточки без фото *//

                    $colorData = $card->getColorData();

                    //Карточка может быть пригодня для продажи, но не иметь цвета (базовая карточка)
                    //Цвет такой карточки не есть цвет и в список добавлять смысла нет
                    if ($colorData && $colorData['name']) {
                        $result[$card->id] = $colorData;
                    }
                }
            }
        }

        return $result;
    }

    //Получение всех размерных свойств (шкал) для модификаций товара у которых есть значения
    public static function getLotSizeScales($productId, $onlyCanSale = true)
    {
        $result = [];
        $product = ProductHelper::getLot($productId);

        if ($product) {
            $offers = $onlyCanSale ? self::getProductOffersCanSale($product->id) : self::getProductOffersQuery($product->id)->onlyActive()->all();
            if ($offers) {
                $offersIds = ArrayHelper::getColumn($offers, 'id');

                $propsNonEmpty = ProductProperty::getNonEmptyGrouped($offersIds);

                //Фильтруем свойства что бы получить только размерные свойства шкалы)
                $propsNonEmptySizes = array_filter($propsNonEmpty, function ($prop) {
                    return in_array($prop['code'], Property::$sizePropsForProductCard);
                });

//                $propsNonEmptyIds = $propsNonEmpty ? ArrayHelper::getColumn($propsNonEmpty, 'id') : [];

                $result = $propsNonEmptySizes;
            }
        }

        return $result;
    }

    /** Получить все размеры связанные с элементом //актуально для модификаций
     *
     * @param $offerId
     * @return array|generated\models\CmsContentElement[]
     */
    public static function getSizesFromProps($offerId)
    {
        $sizesValues = CmsContentElement::find()
            ->alias('size')
            ->select([
                'size.id',
                'size.name',
                'productProperty.property_id',
            ])
            ->innerJoin(ProductProperty::tableName() . ' AS productProperty', "productProperty.value=size.id")
            ->where([
                'productProperty.element_id' => $offerId,
                'productProperty.property_id' => CmsContentProperty::find()->select(['id'])->where(['code' => Property::$sizePropsForProductCard])->column()
            ])
            ->asArray()
            ->all();

        return $sizesValues;
    }

    public function getPropertyByGuid(string $guid)
    {
        if (isset(self::$propertyesGuids[$guid])) {
            return self::$propertyesGuids[$guid];
        } else {
            return null;
        }
    }

    public function isLot()
    {
        return self::isProduct();
    }

    public function isProduct()
    {
        return $this->content_id == self::LOT;
    }

    public function isCard()
    {
        return $this->content_id == self::CARD;
    }

    public function isOffer()
    {
        return $this->content_id == self::MOD;
    }

    public static function getAllEntityTypeContentId()
    {
        return [self::LOT, self::CARD, self::MOD];
    }

    public function getLot()
    {
        return ProductHelper::getLot($this->id);
    }

    public function getLotName()
    {
        return ProductHelper::getLotName($this);
    }

    /** Получение модификация лота которые пригодны для продажи (остатки, цена, активность)
     *
     * @param $productId - идентификатор товары любого уровня (лот/карта/модификация)
     * @return $this|array|bool|Product[]
     */
    public static function getProductOffersCanSale($productId)
    {
        return self::getProductOffers($productId, true);
    }

    public static function getProductOffersCanSaleQuery($productId)
    {
        return self::getProductOffersQuery($productId, true);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    //TODO Переделать, выпилить скекс!
    public function getCmsTree()
    {
        return $this->hasOne(Tree::className(), ['id' => 'tree_id']);
    }

    public function getSsGuid()
    {
        return $this->hasOne(SsGuids::class, ['id' => 'guid_id']);
    }

    public function getBrand()
    {
        if ($this->isLot()) {
            return $this->hasOne(Brand::class, ['id' => 'new_brand_id']);
        } else {
            if ($product = $this->lot) {
                return $product->hasOne(Brand::class, ['id' => 'new_brand_id']);
            }
        }

        return false;
    }


    public function getNotPublic()
    {
        return $this->hasOne(CmsContentElementProperty::class, ['element_id' => 'id'])->onCondition(['property_id' => 83]); // 83 NOT_PUBLIC
    }

    /** Получить все модификации связанные с лотом
     *
     * @param $productId - идентификатор любой сущности связанной с товаром
     * @param bool $onlyCanSale
     * @return array|bool|generated\models\CmsContentElement[]
     */
    public static function getProductOffers($productId, $onlyCanSale = false)
    {
        $offersQuery = self::getProductOffersQuery($productId, $onlyCanSale);
        return $offersQuery ? $offersQuery->all() : false;
    }

    /** Получить query для получения всех модификаций связанных с лотом
     *
     * @param $productId - идентификатор любой сущности связанной с товаром
     * @param bool $onlyCanSale - только элементы пригодные дя продажи
     * @return bool|CmsContentElementQuery
     */
    public static function getProductOffersQuery($productId, $onlyCanSale = false)
    {
        $product = ProductHelper::getLot($productId);

        if ($product) {
            //product is lot
            $offers = self::find()
                ->leftJoin(self::tableName() . " AS card", "cms_content_element.parent_content_element_id=card.id")
                ->leftJoin(self::tableName() . " AS product", "card.parent_content_element_id=product.id")
                //TODO Не нужна в новой схеме!
//                ->leftJoin(ShopProduct::tableName() . " AS offer_shop_product", "offer_shop_product.id=offer.id")
                //TODO Не нужна в новой схеме!
//                ->leftJoin(SsShopProductPrice::tableName() . " AS prices", "prices.product_id=offer.id")
                ->where([
                    'product.id' => $product->id,
                    'product.content_id' => 2,
                    'card.active' => 'Y',
                    'cms_content_element.active' => 'Y',
                ]);

            if ($onlyCanSale) {
                $offers
                    //* Проверки для новой схемы таблиц *//

                    ->andWhere(['product.active' => Common::BOOL_Y])
                    //->andWhere(['>', 'card.new_quantity', 0])
                    ->andWhere(['>', 'cms_content_element.new_quantity', 0])
                    ->andWhere(['>', 'cms_content_element.new_price', 1])
                    //TODO Добавить проверку на Не показывать на сайте для лота

                    //* /Проверки для новой схемы таблиц *//

                    //* Проверки для старой схемы таблиц *//
//                    ->andWhere(['>', 'cms_content_element.new_quantity', 0])
//                    ->andWhere(['>', 'cms_content_element.price', 1])//* /Проверки для старой схемы таблиц *//
                ;
            }

            return $offers;
        } else {
            //error - can't find lot
        }

        return false;
    }

    public static function getProductCardsQuery($productId, $onlyCanSale = false)
    {
        $product = ProductHelper::getLot($productId);

        if ($product) {
            $cards = self::find()->where(['parent_content_element_id' => $product->id]);

            if ($onlyCanSale) {
                $cards->canSale();
            }
        }

        return $cards ?? false;
    }

    public static function getProductCardsCanSaleQuery($productId)
    {
        return self::getProductCardsQuery($productId, true);
    }

    //TODO ДОБАВИТЬ КЕШИРОВАНИЕ!

    /**
     * Посчитать количество карточек для одного бренда или всех
     *
     * @param boolean $onlyCanSale
     * @param mixed $brandId
     * @return mixed
     */
    public static function getCardsCountByBrand($onlyCanSale = true, $brandId = null)
    {
        if (!Setting::countBrandsProducts()) {
            return 0;
        }

        $cacheKey = "get_brands_count_cards_" . ($brandId ? $brandId : 'all_brands') . '_' . ($onlyCanSale ? 'can_sale' : 'all');

        $countData = \Yii::$app->cache->getOrSet(
            $cacheKey,
            function () use ($onlyCanSale, $brandId) {
                $countDataQuery = $onlyCanSale ? self::getCardsCountCanSaleWithBrandQuery($brandId) : self::getCardsCountWithBrandQuery($brandId);
                return $countDataQuery->column();
            },
            HOUR_6
        );

        if ($brandId) {
            return !empty($countData[$brandId]) ? $countData[$brandId] : 0;
        } else {
            return $countData;
        }
    }

    public static function getCardsCountCanSaleWithBrandQuery($brandId = null)
    {
        $query = self::getCardsCountWithBrandQuery($brandId);

        return $query
            ->hasQuantityNew()
            ->priceMoreThanZeroNew()
            ->imageIdNotNull()
            ->notHiddenFromCatalogImage()
            ->onlyPublicForCardsNew(); //От этого джойна возможно лучше избавиться
    }

    public static function getCardsCountWithBrandQuery($brandId = null)
    {
        return self::find()
            ->onlyCard()
            ->onlyActive()
//            ->onlyActiveParent() //Из-за этого получаем лишний JOIN для получения родиетеля, хотя его же можно получить в brandNotEmptyForCard()
            ->brandNotEmptyForCard()
            ->select([new Expression("COUNT(*) AS num"), 'lot_brand.new_brand_id']) //lot_brand из brandNotEmptyForCard()
            ->andWhere(['lot_brand.active' => Common::BOOL_Y])
            ->andFilterWhere(['lot_brand.new_brand_id' => $brandId])
            ->groupBy(['lot_brand.new_brand_id'])
            ->indexBy('new_brand_id');
    }

    public static function getCardsWithPrimePriceQuery()
    {
        $productsPricePrimeQuery = self::find()
            ->select('card.id')
            ->onlyModification()
            ->onlyActive()
            ->leftJoin(Product::tableName() . ' AS card', "card.id=cms_content_element.parent_content_element_id")
            ->leftJoin(\common\models\ShopProductPrice::tableName() . ' AS prices', "prices.product_id=cms_content_element.id")
            ->andWhere([
                'prices.type_price_id' => \common\models\ShopTypePrice::PRICE_TYPE_SITE3_ID
            ])
            ->groupBy('cms_content_element.parent_content_element_id');

        return Product::find()
            ->onlyCard()
            ->andWhere(['id' => $productsPricePrimeQuery]);
    }

    public function getPrices()
    {
        return $this->hasMany(ShopProductPrice::className(), ['product_id' => 'id'])->indexBy('type_price_id');

        //Непонятно где и зачем такое используется, возможно какая то аналитика
        $subQuery = SsShopProductPrices::find()->select('product_id');
        return self::find()->andWhere(['content_id' => self::getAllEntityTypeContentId()])
            ->andWhere(['not in', 'id', $subQuery])->count();
    }

    public function hasDiscount()
    {
        return $this->new_price < $this->new_price_old;
    }

    public function getChildrenContentElements()
    {
        return $this->hasMany(self::class, ['parent_content_element_id' => 'id']);
    }

    public static function getByLotNumProp($lotNum)
    {
        //TODO Перевести на ProductQuery когда смержат и он появится

        $lotNumProp = CmsContentProperty::findOne(['code' => 'LOT_NUM']);

        return self::find()->alias('product')
            ->innerJoin(CmsContentElementProperty::tableName() . ' AS prop_lot_num', "product.id=prop_lot_num.element_id")
            ->where([
                'prop_lot_num.property_id' => $lotNumProp->id,
                'prop_lot_num.value' => $lotNum
            ])
            ->one();
    }

    public function getDatailedReportDataProvider()
    {
        $dataProvider = new \yii\data\SqlDataProvider([
            'sql' => self::getDetailedReportSql(),
            'params' => [
                ':productId' => $this->id,
            ],
//            'totalCount' => $totalCount,
            'pagination' => false,
            'sort' => false,
        ]);

        return $dataProvider;
    }

    public function getQueueReportDataProvider($queue)
    {
        $dataProvider = new \yii\data\SqlDataProvider([
            'sql' => self::getQueueReportSql(),
            'params' => [
                ':productId' => $this->id,
                ':queue' => $queue,
            ],
//            'totalCount' => $totalCount,
            'pagination' => false,
            'sort' => false,
        ]);

        return $dataProvider;
    }

    public function getPropsReportDataProvider($id)
    {
        $models = CmsContentElementProperty::find()
            ->alias('el_props')
            ->select([
                'el_props.property_id',
                'props.name',
                'props.code',
                'el_props.value'
            ])
            ->leftJoin(CmsContentProperty::tableName() . ' AS props', "el_props.property_id = props.id")
            ->andWhere(['el_props.element_id' => $id])
            ->andWhere(['not', ['el_props.value' => null]])
            ->andWhere(['!=', 'el_props.value', ''])
            ->asArray()
            ->all();

        return new ArrayDataProvider([
            'models' => $models,
            'pagination' => false,
            'sort' => false,
        ]);
    }

    public function badgeDiscount()
    {
        if ($this->new_price < $this->new_price_old && !empty($this->new_discount_percent))
            return ceil($this->new_discount_percent);
        return null;
    }

    /**
     * Признак значка "New"
     * @return boolean
     */
    public function isBadgeNew()
    {
        return $this->isBadgeHit() ? false : $this->created_at > time() - DAYS_30;
    }

    /**
     * @return bool
     */
    public function isBadgeHit()
    {
        //TODO Перевести логику в новую модель
        return false;
    }

    public function getPrice()
    {
        return $this->new_price;
    }

    public function getPriceOld()
    {
        return $this->new_price_old;
    }

    public function getPricePrime()
    {
        $priceTypePrimeId = 20;
        //Так как данный тип цены не пересчетный, отдельного поля в карточке/лоте не имеет, то приходится вычислять на лету (во всяком случае пока)
        //Учитывая наличия кеша должно быть норм

        if ($this->isOffer()) {
            $primePrice = $this->hasOne(ShopProductPrice::className(), ['product_id' => 'id'])
                ->onCondition(['type_price_id' => $priceTypePrimeId])
                ->one();

            if ($primePrice && $primePrice->price > 1) {
                $price = $primePrice->price;
            }
        } elseif ($this->isCard()) {
            //Для карточки это цена модифкации с наименьшим значением
            $subQuery = Product::find()->select(['id'])->onlyModification()->byParent($this->id);
            $price = ShopProductPrice::find()
                ->andWhere(['product_id' => $subQuery])
                ->andWhere(['type_price_id' => $priceTypePrimeId])
                ->andWhere(['>', 'price', 0])
                ->innerJoin(Product::tableName() . ' AS offer', "offer.id=shop_product_price.product_id")
                ->andWhere(['offer.active' => Common::BOOL_Y])
                ->andWhere(['>', 'offer.new_quantity', 0])
                ->andWhere(['>', 'offer.new_price', 1])
                ->min('price');
        } else {
            //lot
        }

        return $price ?? '';
    }

    public function isRedBadge()
    {
        return false;
    }


    /**
     * @param $guid
     *
     * @return array|mixed|\yii\db\ActiveRecord|null
     * @throws \Throwable
     */
    public static function getModelByGuid($guid)
    {


//        $product = Product::find()->byOldGuid($guid)->one();
        $product = Product::getDb()->cache(function ($db) use ($guid) {
            return Product::find()->byOldGuid($guid)->one();
        }, HOUR_1);

        if (empty($product)) {
            $product = Product::find()->byNewGuid($guid)->one();
//            $product = Product::getDb()->cache(function ($db) use ($guid) {
//                return Product::find()->byNewGuid($guid)->one();
//            });
        }
        return $product;
    }

    /** Для товара получает самую дешевую карточку из доступных к продаже
     *
     * @param $id - идентификатор любой сущности связанной с товаром
     * @return array|bool|Product|null
     */
    public static function getCardCanSaleWithMinPrice($id)
    {
        $card = self::getProductCardsCanSaleQuery($id)->orderBy(['new_price' => SORT_ASC])->one();
        return $card ?? false;
    }

    public function getCards()
    {
        return $this->hasMany(static::class, ['parent_content_element_id' => 'id']);
    }

    public function getCardsOnlyActive()
    {
        return $this->hasMany(static::class, ['parent_content_element_id' => 'id'])->onlyActive();
    }

    /**
     * @return array
     */
    public function cardsToImpressions()
    {
        $impressions = [];

        if ($this->cardsOnlyActive) {
            foreach ($this->cardsOnlyActive as $index => $card) {
                $impressions[$card->id] = [
                    'id' => $card->id, // Идентификатор/артикул товара
                    'name' => $this->new_lot_name, // название товара
                    'price' => (float)ArrayHelper::getValue($card, 'price.price'), // Стоимость за единицу товара
                    'list' => $this->new_lot_name,
                    'position' => ++$index,
                ];
            }
        }
        return $impressions;
    }

    public static function getQueryForFeed()
    {

        $query = self::find()
            ->innerJoin('cms_tree', 'cms_tree.id=cms_content_element.tree_id');
//            ->innerJoin('shop_product', 'shop_product.id=cms_content_element.id')
//            ->innerJoin('ss_shop_product_prices', 'ss_shop_product_prices.product_id=cms_content_element.id');
//            ->innerJoin('cms_storage_file', 'cms_storage_file.id=cms_content_element.image_id');

        return $query;
    }

    public function getThumbnail()
    {
        /** Квадратные картинки */
        $w = 191;
        $h = 191;
        /** В Моде, кромеме Обуви картинки вытянутые */
        if (preg_match('/moda(?!\/obuv).*/xi', $this->url)) {
            $w = 169;
            $h = 284;
        }
        $image = null;

        if ($this->image) {
            $image = \Yii::$app->imaging->thumbnailUrlSS($this->image->src,
                new Thumbnail([
                    'w' => $w,
                    'h' => $h,
                ]), $this->code
            );

        }

        return $image;
    }

    /** Возвращает характеристики (описание свойств) товара учитывая приритет и формат свойств
     *
     * @return mixed массив свойств или готова хтмл-таблица строкой
     */
    public function getCharacteristics($asString = true)
    {
        //В приоритете Технические детали (ака Коллекция свойств), для старых лотов сразу хтмл-таблица в значении, для новых товаров - JSON с массивом
        if ($techDetails = ProductProperty::getValueByCode($this->isLot() ? $this->id : $this->parent_content_element_id, 'TECHNICAL_DETAILS')) {

            if (($props = json_decode((string)$techDetails, true)) && json_last_error() == JSON_ERROR_NONE) {
                $characteristics = $props;
            } else {
                //err - кривой жсон или не жсон вовсе. Бывает.
                $characteristics = str_replace(['<br />', "\n"], '', Html::decode($techDetails));
            }
        } else {
            $harakteristiki = ProductProperty::getValueByCode($this->isLot() ? $this->id : $this->parent_content_element_id, 'HARAKTERISTIKI');
            $characteristics = $harakteristiki ? Strings::bxHtml2br($harakteristiki) : '';
        }

        if ($asString && is_array($characteristics)) {
            $characteristics = \common\helpers\ArrayHelper::arrayPropsToTable($characteristics);
        }

        return $characteristics;
    }

    public function getPropertyValueByCode($code)
    {
        return ProductProperty::getValueByCode($this->id, $code);
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return \common\helpers\Url::getProductUrl($this);
    }

    public function getVideoCode()
    {
        //Видео принадлежат лоту
        return ProductProperty::getValueByCode($this->lot->id, 'VIDEO');
    }

    public function getBadge1Label()
    {
        return ($this->badge_1 && !empty(self::$badgesInfo[$this->badge_1]['label'])) ? self::$badgesInfo[$this->badge_1]['label'] : '';
    }

    public function getBadge2Label()
    {
        return ($this->badge_2 && !empty(self::$badgesInfo[$this->badge_2]['label'])) ? self::$badgesInfo[$this->badge_2]['label'] : '';
    }

    public function getBadge1Code()
    {
        return ($this->badge_1 && !empty(self::$badgesInfo[$this->badge_1]['code'])) ? self::$badgesInfo[$this->badge_1]['code'] : '';
    }

    public function getBadge2Code()
    {
        return ($this->badge_2 && !empty(self::$badgesInfo[$this->badge_2]['code'])) ? self::$badgesInfo[$this->badge_2]['code'] : '';
    }

    //Комплексная проверка что товар может быть продан в указанном кол-ве
    public static function canSale(self $offer, $quantity)
    {
        if ($offer->isOffer()) {
            //Проверки на:
            //- кол-во модификации хватает
            //- цена больше 0 (именно 0, подарки стоят по 1р, их тоже можно добавлять)
            //- активны: модификация, карточка, лот
            //- лот не имеет статуса "не показывать на сайте"
            /** @var self $lot */
            $lot = $offer->lot;

            //Доступные для продажи модификации товара, наш должен быть в их списке
            $offersCanSale = self::getProductOffersCanSale($lot->id);

            if ($offersCanSale) {
                $offersCanSaleIds = ArrayHelper::getColumn($offersCanSale, 'id');

                //Если модификация есть в списке доступных дял продажи то остается лишь проверить необходимое кол-во остатков
                if (in_array($offer->id, $offersCanSaleIds) && $quantity <= $offer->new_quantity) {
                    return true;
                }
            }

        } else {
            //Карточка и лот, для них можно тоже сообщать можем ли мы их продавать
        }

        return false;
    }

    public function getBrandName()
    {
        $subQuery = CmsContentElementProperty::find()->select('value')->andWhere(['element_id' => $this->id, 'property_id' => 218])->andWhere('value IS NOT NULL');
        $brand = CmsContentElement::findOne($subQuery);
        if ($brand) {
            return $brand->name;
        }

        return 'ShopAndShow';
    }

    public function getGoogleCategoryName()
    {
        $subQuery = CmsTreeTypeProperty::find()->select('id')->andWhere(['code' => 'googleCategoryName']);
        $model = CmsTreeProperty::find()->andWhere(['property_id' => $subQuery, 'element_id' => $this->tree_id])->one();

        if ($model) {
            return $model->value;
        }
        return '';
    }

    public function isModa()
    {
        $tree = null;

        //todo у карточек и модификаций категория может привязаться неправильно, а у лота всегда правильно
        if ($this->isCard() || $this->isOffer()) {
            $tree = Tree::findOne($this->lot->tree_id);
        } elseif ($this->isLot()) {
            $tree = Tree::findOne($this->tree_id);
        }

        if (!$tree) {
            return false;
        }
        $pids = $tree->pids;
        $pids[] = $tree->id;

        $flag = false;
        foreach ($pids as $id) {
            if (in_array($id, Category::$modaCategoryIds)) {
                $flag = true;
                break;
            }
        }
        return $flag;
    }

    public static function getBadges($type = 1)
    {
        $return = [];
        foreach (self::$badgesInfo as $badgeName => $data) {
            if ($data['type'] == $type) {
                $return[$badgeName] = $data['label'];
            }
        }
        return $return;
    }

    //Вспомогательная функция возвращающая дерево для любой сущности товара
    public function getTree()
    {
        return $this->hasOne(CmsTree::className(), ['id' => 'tree_id']);
    }
}
