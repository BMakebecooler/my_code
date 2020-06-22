<?php

namespace common\widgets\content;

use common\models\cmsContent\CmsContentElement;
use modules\shopandshow\models\mediaplan\AirDayProductTime;
use modules\shopandshow\models\shop\ShopContentElement;
use modules\shopandshow\models\shop\ShopTypePrice;
use modules\shopandshow\models\shop\stock\SsProductsSegments;
use modules\shopandshow\models\statistic\ShopProductStatistic;
use skeeks\cms\cmsWidgets\contentElements\ContentElementsCmsWidget;
use skeeks\cms\components\Cms;
use skeeks\cms\models\CmsContentElementProperty;
use skeeks\cms\models\Search;
use yii\db\ActiveQuery;

class ContentElementWidget extends ContentElementsCmsWidget
{

    /**
     * Включение группировки
     * @var bool
     */
    public $groupBy = true;
    public $isAdditionalConditional = true;
    public $enabledActiveTime = Cms::BOOL_N;
    public $skipNonAjaxPageItems = false;

    /**
     * Обязательное наличие изображения в запросе (метод catalogFilterQuery)
     * @var bool
     */
    public $withImages = true;

    public function initDataProvider()
    {
        $className = $this->contentElementClass;

        $this->search = new Search($className::className());
        $this->dataProvider = $this->search->getDataProvider();

        if ($this->enabledPaging == Cms::BOOL_Y) {

            // основная страница должна показывать на 1 элемент меньше ( оставляем место под баннер)
            if ($this->skipNonAjaxPageItems && !\Yii::$app->request->isAjax) {
                $this->dataProvider->getPagination()->pageSize = $this->pageSize - 1;
            }
            else {
                $this->dataProvider->getPagination()->pageSize = $this->pageSize;
            }

            $this->dataProvider->getPagination()->pageParam = $this->pageParamName;
            $this->dataProvider->getPagination()->pageSizeLimit = [(int)$this->pageSizeLimitMin, (int)$this->pageSizeLimitMax];
        } else {
            $this->dataProvider->pagination = false;
        }

        if ($this->orderBy) {
            $this->dataProvider->getSort()->defaultOrder =
                [
                    $this->orderBy => (int)$this->order
                ];
        }

        if ($this->isAdditionalConditional) {
            $this->addAdditionalConditions(); //Вынести в отд класс и включать как DI
        }

        return $this;
    }


    public function getCacheKey()
    {

        $availFields = [
            'id' => true,
            'code' => true,
            'page' => true,
            'per-page' => true,
            'Search' => true,
            'q' => true,
            'sort' => true,
            'block' => true,
            'date' => true,
            'time' => true,
            'category' => true,
            'subcategory' => true,
            'infinite' => true,
        ];
        $requestUrl = array_intersect_key($_GET, array_filter($availFields));

        $cacheKey = implode('_', [
            $this->className(),
            $this->namespace,
            md5(json_encode($requestUrl))
        ]);

        return $cacheKey;
    }

    /**
     * Добавить дополнительные фильтрации
     */
    private function addAdditionalConditions()
    {
        if ($this->contentElementClass === ShopContentElement::className()) {

            /**
             * @var $query ActiveQuery
             */
            $query = $this->dataProvider->query;

            ShopContentElement::catalogFilterQuery($query, $this->withImages);

            /**
             * Логика для значка "хит" пока тут а там посмотрим
             */
            /*$query->leftJoin(CmsContentElementProperty::tableName() . ' AS badge_hit',
                "badge_hit.element_id = cms_content_element.id AND badge_hit.property_id = 
                (SELECT id FROM `cms_content_property` WHERE `content_id` = '2' AND `code` = 'HIT_POPULAR')");*/

            $query->leftJoin(ShopTypePrice::tableName() . ' AS stp', 'stp.id = ss_shop_product_prices.type_price_id');
            $query->leftJoin(ShopProductStatistic::tableName() . ' AS badge_hit',
                "badge_hit.id = cms_content_element.id AND badge_hit.ordered >= 20");

            //Выборка файла плашки товара - закоментировано до завершения проверки запроса
            /*
            $subQuery = (new Query())
                ->select(['badges2.image_id'])
                ->from([SsBadgeProduct::tableName() . ' AS badges_products2'])
                ->leftJoin(SsBadge::tableName() . ' AS badges2',
                    "badges2.id = badges_products2.badge_id 
                    AND badges2.begin_datetime <= UNIX_TIMESTAMP(NOW())
                    AND badges2.end_datetime >= UNIX_TIMESTAMP(NOW())
                    AND badges2.active = '".Cms::BOOL_Y."'")

                ->andWhere('badges_products2.product_id = cms_content_element.id')
                ->orderBy('badges2.begin_datetime DESC')
                ->limit(1);

            $query->leftJoin(StorageFile::tableName() . ' AS badge',
                "badge.id = (".$subQuery->createCommand()->getRawSql().")"
            );
            */

            $query->leftJoin(CmsContentElementProperty::tableName() . ' AS lot_name_value',
                "lot_name_value.element_id = cms_content_element.id AND lot_name_value.property_id = 
                (SELECT id FROM `cms_content_property` WHERE `content_id` = '2' AND `code` = 'LOT_NAME')");

            $query->leftJoin(CmsContentElementProperty::tableName() . ' AS rating_value',
                "rating_value.element_id = cms_content_element.id AND rating_value.property_id = 
                (SELECT id FROM `cms_content_property` WHERE `content_id` = '2' AND `code` = 'RATING')
                ");

            $query->leftJoin(CmsContentElementProperty::tableName() . ' AS not_public_value',
                "not_public_value.element_id = cms_content_element.id AND not_public_value.property_id = 
                (SELECT id FROM `cms_content_property` WHERE `content_id` = '2' AND `code` = 'NOT_PUBLIC')
                ");


/*            $query->leftJoin(CmsContentElementProperty::tableName() . ' AS badge_text_top_value',
                "badge_text_top_value.element_id = cms_content_element.id AND badge_text_top_value.property_id = 
                (SELECT id FROM `cms_content_property` WHERE `content_id` = '2' AND `code` = 'badge_text_top')
                ");

            $query->leftJoin(CmsContentElementProperty::tableName() . ' AS badge_text_bottom_value',
                "badge_text_bottom_value.element_id = cms_content_element.id AND badge_text_bottom_value.property_id = 
                (SELECT id FROM `cms_content_property` WHERE `content_id` = '2' AND `code` = 'badge_text_bottom')
                ");*/

            /*$query->leftJoin(CmsContentElementProperty::tableName() . ' AS video_value',
                "video_value.element_id = cms_content_element.id AND video_value.property_id = 
                (SELECT id FROM `cms_content_property` WHERE `content_id` = '2' AND `code` = 
                    CONCAT('VIDEO_PRICE_',
                        (CASE WHEN stp.code = 'DISCOUNT' THEN 'DISCOUNTED' WHEN stp.code = 'SHOPANDSHOW' THEN 'BASE' ELSE stp.code COLLATE utf8_unicode_ci END)
                        )
                )
                ");*/

            // на Шике это поле у всех элементов пустое
            if (\Yii::$app->appComponent->isSiteSS()) {
                $query->andWhere("not_public_value.value IS NULL OR not_public_value.value = ''");
            }

            $airProductsHourFrom = date('H') - 1;
            $airProductsHourTo = date('H') + 1;

            $query->leftJoin(AirDayProductTime::tableName() . ' AS air_day_product_time',
                "air_day_product_time.lot_id = cms_content_element.id 
                AND air_day_product_time.begin_datetime >= UNIX_TIMESTAMP(DATE_FORMAT(NOW(), '%Y-%m-%d {$airProductsHourFrom}:00:00'))
                AND air_day_product_time.begin_datetime <= UNIX_TIMESTAMP(DATE_FORMAT(NOW(), '%Y-%m-%d {$airProductsHourTo}:00:00'))
                ");

            //Сток или не сток
//            $query->leftJoin(SsProductsSegments::tableName() . ' AS products_segments', "products_segments.product_id=cms_content_element.id");

            $query->addSelect([
                'cms_content_element.*',
                'badge_hit.ordered AS is_badge_hit',
                //'badge.cluster_file AS badge_image_file', //Выборка файла плашки товара - закоментировано до завершения проверки запроса
                'lot_name_value.value AS lot_name',
                'rating_value.value AS rating',
                'COALESCE(air_day_product_time.lot_id, 0) AS is_efir',
//                'products_segments.segment',
//                'badge_text_top_value.value AS badge_text_top',
//                'badge_text_bottom_value.value AS badge_text_bottom',
                //'COALESCE(video_value.value, 0) as video',
            ]);

            /*$query->addSelect(
                new \yii\db\Expression(
                '(SELECT COUNT(*) FROM '.CmsContentElementImage::tableName().' AS has_images WHERE has_images.content_element_id = cms_content_element.id) as count_images '
                )
            );*/

            $query->groupBy(['cms_content_element.id']);
        }
    }


    /**
     * @return $this
     */
    public function initActiveQuery()
    {
        $className = $this->contentElementClass;
        $this->initDataProvider();

        if ($this->createdBy) {
            $this->dataProvider->query->andWhere([$className::tableName() . '.created_by' => $this->createdBy]);
        }

        if ($this->active) {
            $this->dataProvider->query->andWhere([$className::tableName() . '.active' => $this->active]);
        }

        if ($this->content_ids) {
            $this->dataProvider->query->andWhere([$className::tableName() . '.content_id' => $this->content_ids]);
        }

        if ($this->limit) {
            $this->dataProvider->query->limit($this->limit);
        }

//        $this->dataProvider->query->leftJoin('`ss_shares_products` ssp','`ssp`.`product_id` = `cms_content_element`.`id`');
//        $this->dataProvider->query->leftJoin('`ss_shares` ss','`ss`.`id` = `ssp`.`banner_id`');
//        $this->dataProvider->query->andWhere('case when ss.active = "Y" then `ss`.is_hidden_catalog != 1 else true end');

        $treeIds = (array)$this->tree_ids;

        if ($this->enabledCurrentTree == Cms::BOOL_Y) {
            $tree = \Yii::$app->cms->currentTree;

            if ($tree) {
                if ($this->enabledCurrentTreeChild == Cms::BOOL_Y) {
                    if ($this->enabledCurrentTreeChildAll == Cms::BOOL_Y) {
                        $treeIds = $tree->getDescendants()->select(['id'])->indexBy('id')->asArray()->all();
                        $treeIds = array_keys($treeIds);
                    } else {
                        if ($childrens = $tree->children) {
                            foreach ($childrens as $chidren) {
                                $treeIds[] = $chidren->id;
                            }
                        }
                    }
                }

                $treeIds[] = $tree->id;
            }
        }

        if ($treeIds) {
            foreach ($treeIds as $key => $treeId) {
                if (!$treeId) {
                    unset($treeIds[$key]);
                }
            }

            if ($treeIds) {
                /**
                 * @var $query ActiveQuery
                 */
                $query = $this->dataProvider->query;

                if (false && $this->isJoinTreeMap === true) {
                    $query->joinWith('cmsContentElementTrees');
                    $query->andWhere(
                        [
                            'or',
                            [$className::tableName() . '.tree_id' => $treeIds],
                            //[CmsContentElementTree::tableName() . '.tree_id' => $treeIds]
                        ]
                    );
                } else {
                    $query->andWhere([$className::tableName() . '.tree_id' => $treeIds]);
                }

            }

        }


        if ($this->enabledActiveTime == Cms::BOOL_Y) {
            $this->dataProvider->query->andWhere(
                ["<=", $className::tableName() . '.published_at', \Yii::$app->formatter->asTimestamp(time())]
            );

            $this->dataProvider->query->andWhere(
                [
                    'or',
                    [">=", $className::tableName() . '.published_to', \Yii::$app->formatter->asTimestamp(time())],
                    [CmsContentElement::tableName() . '.published_to' => null],
                ]
            );
        }

        /**
         *
         */
        if ($this->with) {
            $this->dataProvider->query->with($this->with);
        }

        if ($this->groupBy) {
            $this->dataProvider->query->groupBy([$className::tableName() . '.id']);
        }

        if ($this->activeQueryCallback && is_callable($this->activeQueryCallback)) {
            $callback = $this->activeQueryCallback;
            $callback($this->dataProvider->query);
        }

        if ($this->dataProviderCallback && is_callable($this->dataProviderCallback)) {
            $callback = $this->dataProviderCallback;
            $callback($this->dataProvider);
        }

        return $this;
    }

    /**
     * @param bool $cache
     * @return int|mixed
     */
    public function getCountCategory($cache = true)
    {
        if (!$cache) {
            return $this->dataProvider->totalCount;
        }

        $keyCache = 'count_' . $this->getCacheKey();

        $cache = \Yii::$app->cache;

        $count = $cache->get($keyCache);

        if ($count === false) {
            $count = $this->dataProvider->totalCount;

/*            $dependency = new TagDependency([
                'tags' =>
                    [
                        $this->className() . (string)$this->namespace,
                        (new ShopContentElement())->getTableCacheTag(),
                        (new CmsContentElementTree())->getTableCacheTag(),
                    ],
            ]);*/

            $cache->set($keyCache, $count, self::getRunCacheDuration()); // $dependency
        }

        return $count;
    }

    /**
     * Вернуть значение кеша
     * @return int
     */
    public static function getRunCacheDuration()
    {
        return HOUR_2;
    }

}