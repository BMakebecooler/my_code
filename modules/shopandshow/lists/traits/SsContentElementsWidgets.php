<?php
/**
 * Created by PhpStorm.
 * User: ubuntu5
 * Date: 20.06.17
 * Time: 16:58
 */

namespace modules\shopandshow\lists\traits;


use common\helpers\ArrayHelper;
use common\models\cmsContent\CmsContentElement;
use common\models\ProductAbcAddition;
use common\widgets\content\ContentElementWidget;
use modules\shopandshow\models\shop\ShopContentElement;
use skeeks\cms\components\Cms;
use skeeks\cms\shop\models\ShopViewedProduct;

trait SsContentElementsWidgets
{

    /**
     * Получить виджет для блока "завершите ваш образ"
     * @param array $config
     * @return string
     */
    public function getWidgetFinishYourImage($config = [])
    {
        $basicConfig = ArrayHelper::merge([
            'contentElementClass' => ShopContentElement::className(),
            'namespace' => 'ContentElementsCmsWidget-finish-your-image-02',
            'viewFile' => '@template/widgets/ContentElementsCms/sliders/products_6',
            'label' => 'С этим товаром покупают',
            'active' => Cms::BOOL_Y,
            'limit' => 6,
            'orderBy' => false,
            'groupBy' => true, // Необходимо использовать группировку
            'data' => [
                'slider-option' => '{items: 5}',
                'item-slider' => [
                    'event' => [
                        'onclick' => "sx.Observer.trigger('rec_card'); return true;"
                    ]
                ],
                'tagManager' => [
                    'list' => 'Карточка_товара_с_этим_товаром_покупают'
                ]
            ],
            'enabledCurrentTree' => false,
            'activeQueryCallback' => function (\yii\db\ActiveQuery $query) {

                $similarIds = $this->similarProductsQuery()->select('cms_content_element.id');

                /*                $plusBuy = ArrayHelper::arrayToInt($this->getFinishYouImage());

                                if ($plusBuy) {
                                    $query->orderBy([new \yii\db\Expression('FIELD (cms_content_element.id, ' . join(',', $plusBuy) . ') DESC')]);
                                }*/

                $productAdditionalQuery = ProductAbcAddition::find()->bySourceId($this->id)->select('product_id');
                $query->andWhere(['OR', ['cms_content_element.id' => $productAdditionalQuery], ['cms_content_element.id' => $similarIds]]);

                $query->addOrderBy('show_counter  DESC');

            }
        ], $config);

        return ContentElementWidget::widget($basicConfig);
    }


    /**
     * Получить виджет для блока "С этим товаром покупают"
     * @param array $config
     * @deprecated ? нигде не используется
     * @return string
     */
    public function getWidgetAlsoBuy($config = [])
    {
        $basicConfig = ArrayHelper::merge([
            'contentElementClass' => ShopContentElement::className(),
            'namespace' => 'ContentElementsCmsWidget-finish-your-image-01',
            'viewFile' => '@template/widgets/ContentElementsCms/sliders/products_6',
            'label' => 'С этим товаром покупают',
            'active' => Cms::BOOL_Y,
            'limit' => 6,
            'orderBy' => false,
            'groupBy' => false,
            'data' => [
                'slider-option' => '{items: 5}'
            ],
            'enabledCurrentTree' => false,
            'activeQueryCallback' => function (\yii\db\ActiveQuery $query) {

                $similarIds = $this->similarProductsQuery()->select('cms_content_element.id');

                $plusBuy = ArrayHelper::arrayToInt($this->getFinishYouImage());

                if ($plusBuy) {
                    $query->orderBy([new \yii\db\Expression('FIELD (cms_content_element.id, ' . join(',', $plusBuy) . ') DESC')]);
                }

                $query->andWhere(['cms_content_element.id' => $similarIds]);

//                $query->addOrderBy('show_counter DESC');

            }
        ], $config);

        return ContentElementWidget::widget($basicConfig);
    }

    /**
     * @param array $config
     * @deprecated ? нигде не используется, заменили на РР
     * @return string
     */
    public function getWidgetSimilarProduct($config = [])
    {
        $basicConfig = ArrayHelper::merge([
            'contentElementClass' => ShopContentElement::className(),
            'namespace' => 'ContentElementsCmsWidget-similar-product-1',
            'viewFile' => '@template/widgets/ContentElementsCms/sliders/products_6',
            'label' => 'Похожие товары',
            'enabledCurrentTree' => false,
            'enabledActiveTime' => false,
            'active' => Cms::BOOL_Y,
            'content_ids' => [PRODUCT_CONTENT_ID],
            'tree_ids' => ($this->cmsTree) ? \yii\helpers\ArrayHelper::map($this->cmsTree->parent->children, 'id', 'id') : [],
            'limit' => 15,
            'activeQueryCallback' => function (\yii\db\ActiveQuery $query) {
                $query->andWhere(['!=', CmsContentElement::tableName() . ".id", $this->id]);
                $query->orderBy(['show_counter' => SORT_DESC]);
            }
        ], $config);

        return ContentElementWidget::widget($basicConfig);
    }

    /**
     * Вы смотрели
     * @param array $config
     * @return string
     */
    public function getWidgetVisitedProducts($config = [])
    {
        $basicConfig = ArrayHelper::merge([
            'contentElementClass' => ShopContentElement::className(),
            'namespace' => 'ContentElementsCmsWidget-VisitedProducts-product',
            'viewFile' => '@template/widgets/ContentElementsCms/sliders/products_6',
            'label' => 'Вы смотрели',
            'limit' => 40,
            'active' => Cms::BOOL_Y,
            'enabledRunCache' => Cms::BOOL_N, // при включенном кеше видим чужие товары !!!
            'runCacheDuration' => MIN_5,
            'enabledCurrentTree' => false,
            'enabledActiveTime' => false,
            'content_ids' => [PRODUCT_CONTENT_ID],
//            'groupBy' => false,
            'orderBy' => false,
            'dataProviderCallback' => function (\yii\data\ActiveDataProvider $activeDataProvider) {
                /**
                 * @var $query \yii\db\ActiveQuery
                 */
                $query = $activeDataProvider->query;

                if ($this->id) {
                    $query->andWhere(['!=', \skeeks\cms\models\CmsContentElement::tableName() . ".id", $this->id]);
                }

                $query->innerJoin(ShopViewedProduct::tableName() . ' AS viewed', "viewed.shop_product_id = cms_content_element.id");

                $query->andWhere('viewed.shop_fuser_id =:shop_fuser_id', [':shop_fuser_id' => \common\helpers\User::getSessionId()]);

                $query->orderBy(['viewed.created_at' => SORT_DESC]);
//        $query->groupBy('cms_content_element.id');
            }
        ], $config);

        return ContentElementWidget::widget($basicConfig);
    }

    /**
     * Получить виджет для блока "С этим товаром покупают"
     * @param array $config
     * @return string
     */
    public function getWidgetRecomendedItems($via = '', $config = [])
    {

        try {
            $similarIds = $this->getRecoItems($via);
        } catch (\Exception $exception) {
            return [];
        }

        if (count($similarIds) < 1) {
            return null;
        }

        $basicConfig = ArrayHelper::merge([
            'contentElementClass' => ShopContentElement::className(),
            'namespace' => 'ContentElementsCmsWidget-recomended-product-1',
            'viewFile' => '@template/widgets/ContentElementsCms/sliders/products_6',
            'label' => 'С этим товаром покупают (Канал: ' . $via . ')',
            'enabledCurrentTree' => false,
            'enabledActiveTime' => false,
            'active' => Cms::BOOL_Y,
            'content_ids' => [PRODUCT_CONTENT_ID],
            'tree_ids' => ($this->cmsTree) ? \yii\helpers\ArrayHelper::map($this->cmsTree->parent->children, 'id', 'id') : [],
            'limit' => 15,
            'activeQueryCallback' => function (\yii\db\ActiveQuery $query) use ($similarIds) {

                $query->andWhere(['!=', CmsContentElement::tableName() . ".id", $this->id]);

                $query->andWhere(['cms_content_element.bitrix_id' => $similarIds]);
                $query->orderBy([new \yii\db\Expression('FIELD (cms_content_element.bitrix_id, ' . join(',', $similarIds) . ') DESC')]);

//                $query->andWhere(['in', CmsContentElement::tableName() . ".bitrix_id",  $this->getRecoItems()]);
//                $query->orderBy(['show_counter' => SORT_DESC]);
            }
        ], $config);

        return ContentElementWidget::widget($basicConfig);
    }

}