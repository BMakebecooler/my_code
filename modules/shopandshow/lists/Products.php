<?php

namespace modules\shopandshow\lists;

use common\helpers\Dates;
use common\lists\Contents;
use common\models\cmsContent\CmsContentElement;
use common\models\filtered\products\Catalog as MainFilter;
use common\widgets\content\ContentElementWidget;
use common\widgets\filters\ProductFiltersWidget;
use modules\shopandshow\models\shop\ShopContentElement;
use skeeks\cms\components\Cms;

class Products
{

    /**
     * @param MainFilter $filters
     * @param null|ProductFiltersWidget $shopFilters
     * @param array $config
     * @return ContentElementWidget
     */
    public function getProductList(MainFilter $filters = null, $shopFilters = null, $config = [])
    {

        $defaultConfig = [
            'namespace' => 'ContentElementsCmsWidget-catalog-products-v1',
            'viewFile' => '@template/widgets/ContentElementsCms/products/catalog-infinite',
            'contentElementClass' => ShopContentElement::className(),
            'active' => Cms::BOOL_Y,

            'enabledRunCache' => Cms::BOOL_N,
            'runCacheDuration' => ContentElementWidget::getRunCacheDuration(),
            'groupBy' => true,
            'enabledPjaxPagination' => Cms::BOOL_N,
            'enabledActiveTime' => Cms::BOOL_N,
            'pageSize' => $filters ? (int)$filters->perPage : MainFilter::DEFAULT_PER_PAGE,
            'content_ids' => [PRODUCT_CONTENT_ID],
            'pageSizeLimitMax' => 10,
            'skipNonAjaxPageItems' => true,
            'data' => [
                'tagManager' => [
                    'list' => 'Каталог_товары'
                ]
            ],
            'dataProviderCallback' => function (\yii\data\ActiveDataProvider $activeDataProvider)
            use ($filters, $shopFilters) {

                if ($filters) {
                    $filters->search($activeDataProvider);
                    $filters->hideProduct($activeDataProvider);
                }

                if ($shopFilters && !\common\helpers\Url::isBot()) { //Для ботов не фильтруем
                    $shopFilters->search($activeDataProvider);
                }
            },
        ];

        $defaultConfig = array_merge($defaultConfig, $config);

        $productList = new ContentElementWidget($defaultConfig);

        return $productList;
    }

    /**
     * Получить товар в эфире
     * @return bool|\common\models\cmsContent\CmsContentElement|ShopContentElement|null|\yii\db\ActiveRecord
     */
    public function getOnairProduct()
    {
        $productOnair = self::getOnairProductId();

        /**
         * Логика такая:
         * если есть в редисе гуид товара то смотрим по нему,
         * если нету то если смотрим в рабочем периоде (с 7 утра до 22 часов) то показываем ЦТС,
         * если нет то ничего не показываем
         */
        if ($productOnair) {

            $entity = Guids::getEntityByGuid($productOnair);

            if ($entity && $entity instanceof CmsContentElement) {
                return $entity;
            }

        } elseif (time() >= Dates::beginEfirPeriod() && time() <= Dates::endEfirWork()) {
            return Contents::getCtsProduct();
        }

        return false;
    }

    /**
     * Получить GUID товара в эфире
     * @return string
     */
    public static function getOnairProductId()
    {
        return \Yii::$app->redis->get('onair_product_id');
    }

    /**
     * Получить статистику о товаре
     * @param $productId
     * @return []
     */
    public static function getStatistic($productId)
    {
        $sql = <<<SQL
SELECT * FROM `shop_product_statistic` WHERE `id` = :id
SQL;

        return \Yii::$app->db->createCommand($sql, [
            ':id' => $productId
        ])->queryOne();
    }

    /**
     * @param $productId
     * @param string $propertyCode
     * @return string
     */
    public static function getPropertyValue($productId, $propertyCode = 'PREIMUSHESTVA')
    {
        $sql = <<<SQL
            SELECT ccep.value FROM `cms_content_element_property` AS ccep
            INNER JOIN cms_content_property AS ccp ON ccp.id = ccep.property_id
            WHERE ccep.element_id = :id AND ccp.code = :code
SQL;
        $result = \Yii::$app->db->createCommand($sql, [
            ':id' => $productId,
            ':code' => $propertyCode,
        ])->queryOne();

        return ($result) ? $result['value'] : '';
    }

}