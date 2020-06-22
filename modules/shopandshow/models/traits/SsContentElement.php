<?php

namespace modules\shopandshow\models\traits;

use common\helpers\ArrayHelper;
use common\helpers\Strings;
use common\helpers\User;
use common\models\cmsContent\CmsContentElement;
use common\models\cmsContent\CmsContentProperty;
use common\models\Product;
use common\widgets\products\ModificationsWidget;
use modules\shopandshow\models\cmsContent\CmsContentElementRelation;
use modules\shopandshow\models\shop\ShopContentElement;
use modules\shopandshow\models\shop\ShopFuserFavorite;
use modules\shopandshow\models\shop\SsShopProductPrice;
use modules\shopandshow\models\shop\stock\SsProductsSegments;
use skeeks\cms\components\Cms;
use skeeks\cms\models\CmsComponentSettings;
use skeeks\cms\models\CmsContentElementImage;
use skeeks\cms\models\CmsContentElementProperty;
use skeeks\cms\models\StorageFile;
use skeeks\cms\shop\models\ShopViewedProduct;
use yii\db\ActiveQuery;
use yii\helpers\Html;

trait SsContentElement
{


    /**
     * Признак значка "ХИт"
     * @var
     */
    public $is_badge_hit;

    /**
     * Файл плашки
     * @var
     */
    public $badge_image_file;

    /**
     * Название лота в параметрах
     * @var
     */
    public $lot_name;

    /**
     * Рейтинг
     * @var int
     */
    public $rating;

    /**
     * Товар в эфире
     * @var int
     */
    public $is_efir;

    /**
     * Эфирный блок товара
     * @var int
     */
    public $block_id;

    /**
     * Час эфира
     * @var int
     */
    public $hour_efir;

    /**
     * Идентификатор сегмента товара
     * @var string
     */
    public $segment;

    /**
     * Текст плашки на товаре (верх)
     * @var string
     */
    public $badge_text_top;

    /**
     * Текст плашки на товаре (низ)
     * @var string
     */
    public $badge_text_bottom;

    /**
     * Видео
     * @var null
     */
    public $video;

    /**
     * Избранные
     * @return \yii\db\ActiveQuery
     */
    public function getFavorite()
    {
        return $this->hasOne(ShopFuserFavorite::className(), ['shop_product_id' => 'id'])
            ->andOnCondition(['shop_fuser_id' => ($fUser = \Yii::$app->shop->shopFuser) ? $fUser->id : 0])
            ->andOnCondition(['shop_fuser_favorites.active' => Cms::BOOL_Y]);
    }

    /**
     * Просмотренные товары
     * @return \yii\db\ActiveQuery
     */
    public function getViewedProduct()
    {
        return $this->hasOne(ShopViewedProduct::className(), ['shop_product_id' => 'id'])
            ->andOnCondition(['shop_viewed_product.shop_fuser_id' => ($fUser = \Yii::$app->shop->shopFuser) ? $fUser->id : 0]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParentContentElement()
    {
        return $this->hasOne(static::className(), ['id' => 'parent_content_element_id'])
            ->alias('parent_element');
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStockSegment()
    {
        return $this->hasOne(SsProductsSegments::className(), ['product_id' => 'id']);
    }

    /**
     * Проверка на наличие сегмента стока
     * @return integer
     */
    public function getIsStockSegment()
    {
        return (bool)$this->getStockSegment()->count();
    }

    /**
     * Признак наличия избранного товара
     * @return bool
     */
    public function isFavorite()
    {
        return $this->favorite ? $this->favorite->isActive() : false;
    }

    /**
     * Получение цены товара
     * @return \yii\db\ActiveQuery
     */
    public function getPrice()
    {
        return $this->hasOne(SsShopProductPrice::className(), ['product_id' => 'id']);
    }

    /**
     * Признак карточки товара (цвета)
     * @return bool
     */
    public function isCard()
    {
        return $this->content_id == CARD_CONTENT_ID;
    }

    /**
     * Признак предожения
     * @return bool
     */
    public function isOffer()
    {
        return $this->content_id == OFFERS_CONTENT_ID;
    }

    /**
     * Признак лота
     * @return bool
     */
    public function isLot()
    {
        return $this->content_id == PRODUCT_CONTENT_ID;
    }

    /**
     * Признак наличия предожений
     * @return bool
     */
    public function isOffers()
    {
        return $this->count_children > 0;
    }

    /**
     * Признак значка "Хит"
     * @return boolean
     */
    public function isBadgeHit()
    {
        return $this->is_badge_hit;
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
     * Рейтинг товара
     * @return int
     */
    public function getRating()
    {
//        return mt_rand(0, 50) / 10;

        return $this->rating ?: $this->relatedPropertiesModel->getAttribute('RATING');
    }

    /**
     * Показывать товар на сайте
     * @return bool
     */
    public function isShowProduct()
    {
        $notPublic = $this->relatedPropertiesModel->getAttribute('NOT_PUBLIC');

        return $notPublic == Cms::BOOL_Y ? false : true;
    }

    protected static $modificationSettings = null;

    /**
     * Получить модификации в товаре
     * @param string $modificationNamespace
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getModifications($modificationNamespace = '')
    {

        $keyCache = sprintf('product_modifications_%s_%s', $this->id, __FUNCTION__);
        $cache = \Yii::$app->cache;
        if (User::isDeveloper() && isset($_GET['cleargetmodscache'])) {
            $cache->delete($keyCache);
        }

        $result = $cache->get($keyCache);

        if ($result === false) {

            $modificationSettings = (self::$modificationSettings) ?: self::$modificationSettings =
                CmsComponentSettings::find()->andWhere('namespace = :namespace', [
                    ':namespace' => $modificationNamespace ?: ModificationsWidget::getNameSpace()
                ])->one();

            if (!$modificationSettings) {
                return [];
            }

            $params = $modificationSettings->value;

            if (!isset($params['realatedProperties'])) {
                return [];
            }

            $parameters = $params['realatedProperties'];

            $properties = CmsContentProperty::find()
                ->select(['cms_content_property.id',
                    'COALESCE(cms_content_property.widget_name, cms_content_property.name) AS name',
                    'cms_content_property.code',
                ])
                ->andWhere(['cms_content_property.code' => $parameters]);

            $offers = CmsContentElement::find()
                ->select(['cms_content_element.id', 'cms_content_element.parent_content_element_id'])
//                ->innerJoin(ShopProduct::tableName(), 'cms_content_element.id = shop_product.id')
                ->andWhere('new_quantity > 0')
                ->andWhere(['or',
                    ['cms_content_element.id' => $this->id],
                    ['cms_content_element.parent_content_element_id' => $this->id],
                ])->asArray()->all();

            // собираем модификации и их карточки с цветом в один массив
            $offers = ArrayHelper::merge(ArrayHelper::getColumn($offers, 'id', false), ArrayHelper::getColumn($offers, 'parent_content_element_id', false));

            $propertyValues = CmsContentElementProperty::find()
                ->select(['value', 'property_id'])
                ->andWhere(['element_id' => $offers])
                ->andWhere('value IS NOT NULL AND value > 0')
                ->innerJoin(['properties' => $properties], 'cms_content_element_property.property_id = properties.id')
                ->groupBy(['value', 'property_id']);

            $parameters = CmsContentElement::find()
                ->select([
                    'cms_content_element.id AS element_id',
                    'properties.id AS property_id',
                    'properties.name AS name',
                    'cms_content_element.name AS value',
                    'properties.code AS code',
                ])
//                ->active()
                ->innerJoin(['properties_val' => $propertyValues], 'cms_content_element.id = properties_val.value')
                ->innerJoin(['properties' => $properties], 'properties_val.property_id = properties.id')
                ->limit(15)
                ->orderBy('cms_content_element.name ASC')
                ->asArray()
                ->all();

            $result = [];

            foreach ($parameters as $parameter) {
                if (isset($result[$parameter['property_id']])) {
                    array_push($result[$parameter['property_id']]['values'], [
                        'value' => $parameter['value'],
                        'element_id' => $parameter['element_id'],
                    ]);
                } else {
                    $result[$parameter['property_id']] = [
                        'name' => $parameter['name'],
                        'code' => $parameter['code'],
                        'element_id' => $parameter['element_id'],
                        'value' => $parameter['value'],
                        'values' => [
                            [
                                'value' => $parameter['value'],
                                'element_id' => $parameter['element_id'],
                            ]
                        ],
                    ];
                }
            }

            $cache->set($keyCache, $result, DAYS_1);

            return $result;

        } else {
            return $result;
        }
    }

    /**
     * Признак есть ли сопутствующие товары для завершения образа
     * @return []
     */
    public function getFinishYouImage()
    {
        $plusBuy = $this->relatedPropertiesModel->getAttribute('PLUS_BUY');
        $plusBuy = $plusBuy ? array_filter($plusBuy) : [];

        return $plusBuy ? array_unique(array_values($plusBuy)) : [];
    }

    /**
     * @return ActiveQuery
     */
    public function similarProductsQuery($subQueryIds = false)
    {
        $plusBuy = ArrayHelper::arrayToInt($this->getFinishYouImage());

        //$products = ShopContentElement::find()
        $products = Product::find()
//            ->joinWith([
//                'price',
//                'shopProduct',
//            ])
            ->canSale()
            ->onlyLot()
//            ->andWhere('content_id = :content_id', [':content_id' => PRODUCT_CONTENT_ID])
//            ->andWhere('price > 0')
//            ->andWhere(['>=', 'new_quantity', 1])
//            ->andWhere('tree_id IS NOT NULL')
//            ->andWhere('image_id IS NOT NULL')
//            ->active()
            ->andWhere(['not', ['cms_content_element.id' => $this->id]]);

        if ($subQueryIds) {
            $products = $products->andWhere(['and',
                ['cms_content_element.id' => $subQueryIds],
            ]);
        } elseif ($plusBuy) {
            $products = $products->andWhere(['and',
                ['cms_content_element.id' => $plusBuy],
            ]);
        } else {

            $products = $products->andWhere('1 = 0');

            /*                $parentTree = $this->getParentId(); @todo - довести до ума! ветка similar_products
                            $descendantsIds = TreeList::getDescendantsById($parentTree);

                            $products = $products->andWhere(['and',
                                ['cms_content_element.tree_id' => $descendantsIds]
                            ]);*/


        }

        $products->leftJoin(CmsContentElementProperty::tableName() . ' AS not_public_value',
            "not_public_value.element_id = cms_content_element.id AND not_public_value.property_id = 
                (SELECT id FROM `cms_content_property` WHERE `content_id` = '2' AND `code` = 'NOT_PUBLIC')
                ");

        $products->andWhere("not_public_value.value IS NULL OR not_public_value.value = ''");

        return $products;
    }

    /**
     * С этим товаром покупают
     * @return array|ShopContentElement[]|\yii\db\ActiveRecord[]
     */
    public function getSimilarProducts($subQueryId = false)
    {
        $plusBuy = ArrayHelper::arrayToInt($this->getFinishYouImage());

        $products = $this->similarProductsQuery($subQueryId)->limit(20);

        if ($plusBuy && !$subQueryId) {
            $products->orderBy([new \yii\db\Expression('FIELD (cms_content_element.id, ' . join(',', $plusBuy) . ') DESC')]);
            $products->addOrderBy('show_counter DESC');
        }


        return $products->all();
    }

    /**
     * Получить ИД предыдущего элемента в дереве
     * @return mixed
     */
    public function getParentId()
    {
        if ($this->tree_id) {
            $pids = $this->cmsTree->pids;
            $countPids = count($pids);

            return $pids[$countPids - 1];
        }

        return $this->tree_id;
    }

    /**
     * Получить ИД главного элемента в дереве
     * @return mixed
     */
    public function getMainParentId()
    {
        if ($this->tree_id) {
            $tree = $this->cmsTree;

            $pids = $tree->pids;
            $countPids = count($pids);

            $index = $countPids - ($tree->level - 2);

            return isset($pids[$index]) ? $pids[$index] : $this->tree_id;
        }

        return $this->tree_id;
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getImage()
    {
        return $this->hasOne(StorageFile::className(), ['id' => 'image_id'])
            ->alias('image');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getImages()
    {
        return $this->hasMany(StorageFile::className(), ['id' => 'storage_file_id'])
            ->via('cmsContentElementImages')
            ->alias('images');
    }

    /**
     * @return mixed
     */
    public function getCountImages()
    {
        return $this->hasMany(CmsContentElementImage::className(), ['content_element_id' => 'id'])
            ->alias('has_images')->count();
    }

    /**
     * Получить название товара (Лота)
     * @return mixed
     */
    public function getLotName()
    {
        if ($lotName = $this->lot_name) {
            return $lotName;
        } elseif ($lotName = $this->relatedPropertiesModel->getAttribute('LOT_NAME')) {
            return $lotName;
        } else {
            return $this->name;
        }
    }

    /**
     * @param ActiveQuery $query
     * @param bool $withImages
     */
    public static function catalogFilterQuery($query, $withImages = true)
    {
        $query->joinWith([
//            'shopProduct',
            'price',
        ]);

        /**
         * @todo При добавлении новых условий не забывать добавлять их в sphinx
         * @todo актуальный конфиг тут configs/sphinx/sphinx_devnew.conf
         * @todo а так же в логику по параметрам
         */

        $query->andWhere(['>=', 'new_quantity', 1]);
        $query->andWhere(['>', 'ss_shop_product_prices.min_price', 2]);
//        $query->andWhere(['not', ['ss_shop_product_prices.min_price' => null]]);
//        $query->andWhere(['not', ['cms_content_element.tree_id' => null]]);
//        $query->andWhere(['not', ['cms_content_element.image_id' => null]]);
    }

    /**
     * Есть ли видео о товаре
     * @return bool
     */
    public function isVideo()
    {
        if ($this->video !== null || $this->video === 0) {
            return $this->video;
        }

        $priceCode = '';
        try {
            $priceCode = $this->price->typePrice->code;

            if ($priceCode == 'DISCOUNT') {
                $priceCode = 'DISCOUNTED';
            } elseif ($priceCode == 'SHOPANDSHOW') {
                $priceCode = 'BASE';
            }
        } catch (\Exception $e) {
        }

        $paramName = 'VIDEO_PRICE_' . $priceCode;

        $sql = <<<SQL
SELECT param.value  AS video
FROM cms_content_element_property AS param 
INNER JOIN cms_content_property AS property ON property.id = param.property_id
WHERE param.element_id = :element_id AND property.code = :code
SQL;

        $data = \Yii::$app->db->createCommand($sql, [
            ':element_id' => $this->id,
            ':code' => $paramName,
        ])->cache(HOUR_2)->queryOne();

        return $this->video = ($data) ? $data['video'] : 0;
    }

    /**
     * Получить техническое описание товара
     * @return mixed
     */
    public function getTechDetails()
    {
        return str_replace(['<br />', "\n"], '', Html::decode($this->relatedPropertiesModel->getAttribute('TECHNICAL_DETAILS')));
    }

    /** Возвращает характеристики (описание свойств) товара учитывая приритет и формат свойств
     *
     * @return mixed массив свойств или готова хтмл-таблица строкой
     */
    public function getCharacteristics($asString = true)
    {
        //В приоритете Технические детали (ака Коллекция свойств), для старых лотов сразу хтмл-таблица в значении, для новых товаров - JSON с массивом
        if ($techDetails = $this->relatedPropertiesModel->getAttribute('TECHNICAL_DETAILS')) {

            if (($props = json_decode((string)$techDetails, true)) && json_last_error() == JSON_ERROR_NONE) {
                $characteristics = $props;
            } else {
                //err - кривой жсон или не жсон вовсе. Бывает.
                $characteristics = $this->getTechDetails();
            }
        } else {
            $characteristics = Strings::bxHtml2br($this->relatedPropertiesModel->getAttribute('HARAKTERISTIKI'));
        }

        if ($asString && is_array($characteristics)) {
            $characteristics = \common\helpers\ArrayHelper::arrayPropsToTable($characteristics);
        }

        return $characteristics;
    }

    /**
     * Признак товара в эфире
     * @return bool
     */
    public function isProductInEfir()
    {
        return (bool)$this->is_efir;
    }


    /**
     * Признак есть ли сопутствующие товары для завершения образа
     * @return []
     */
    public function getRecoItems($via = '')
    {
        $plusBuy = \Yii::$app->recoApi->itemToItem($this->bitrix_id, $via);
        $plusBuy = $plusBuy ? array_filter($plusBuy) : [];

        return $plusBuy ? ArrayHelper::arrayToInt(ArrayHelper::getColumn($plusBuy, 'id')) : [];
    }

    /**
     * @return ActiveQuery
     */
    public function recomendedItemsQuery($via = '')
    {
        $plusBuy = ArrayHelper::arrayToInt($this->getRecoItems($via));

        if (count($plusBuy) < 1)
            return null;

        $products = ShopContentElement::find()
            ->joinWith([
                'price',
//                'shopProduct',
            ])
            ->andWhere('content_id = :content_id', [':content_id' => PRODUCT_CONTENT_ID])
            ->andWhere('price > 0')
            ->andWhere(['>=', 'new_quantity', 1])
            ->andWhere('tree_id IS NOT NULL')
            ->andWhere('image_id IS NOT NULL')
            ->andWhere(['not', ['cms_content_element.id' => $this->id]])
            ->active()
            ->andWhere(['cms_content_element.bitrix_id' => $plusBuy]);

//        $products->orderBy([new \yii\db\Expression('FIELD (cms_content_element.bitrix_id, ' . join(',', $plusBuy) . ') DESC')]);

        $products->leftJoin(CmsContentElementProperty::tableName() . ' AS not_public_value',
            "not_public_value.element_id = cms_content_element.id AND not_public_value.property_id = 
                (SELECT id FROM `cms_content_property` WHERE `content_id` = '2' AND `code` = 'NOT_PUBLIC')
                ");

        $products->andWhere("not_public_value.value IS NULL OR not_public_value.value = ''");

        return $products;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getElementRelations()
    {
        return $this->hasMany(CmsContentElementRelation::className(), ['content_element_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRelatedElements()
    {
        return $this->hasMany(CmsContentElement::className(), ['id' => 'related_content_element_id'])->via('elementRelations');
    }

    /**
     * Остатки на складе
     * @return int
     */
    public function getRest()
    {
        return $this->relatedPropertiesModel->getAttribute('REST');
    }

    /**
     * Признак наличия остатков на складе
     * @return bool
     */
    public function isRest()
    {
        return $this->getRest() > 0;
    }

    /**
     * Можем ли быстро доставить товар
     * @return bool
     */
    public function isFastDelivery()
    {
        if ($this->rest <= 0) {
            return false;
        }

        if ($this->isOffer()) {
            return $this->rest > 0;
        }

        /** @var static $childrenElement */
        foreach ($this->childrenContentElements as $childrenElement) {
            if ($childrenElement->rest <= 0) {
                return false;
            }
        }

        return true;
    }

    /**
     * Текст плашки товара (верх)
     * @return mixed
     */
    public function getBadgeTextTop()
    {
        return false; //$this->badge_text_top ?: $this->relatedPropertiesModel->getAttribute('badge_text_top');
    }

    /**
     * Текст плашки товара (низ)
     * @return mixed
     */
    public function getBadgeTextBottom()
    {
        return false; //$this->badge_text_bottom ?: $this->relatedPropertiesModel->getAttribute('badge_text_bottom');
    }
}