<?php
namespace common\widgets\services;

use common\helpers\ArrayHelper;
use common\models\cmsContent\CmsContentElement;
use modules\shopandshow\models\mediaplan\AirDayProductTime;
use modules\shopandshow\models\shares\SsShare;
use modules\shopandshow\models\shop\ShopContentElement;
use modules\shopandshow\models\shop\ShopProduct;
use skeeks\cms\shop\models\ShopOrder;
use yii\base\Widget;

class TagManagerWidget extends Widget
{
    /**
     * @var CmsContentElement
     */
    public $model;
    public $options = [];

    public static $postition = 1;

    /**
     * @param ShopOrder $order
     * @return string
     */
    public static function getJsonOrder(ShopOrder $order)
    {
        $tagManagerOrder = $order->toArray();

        $tagManagerOrder['revenue'] = number_format($order->money->getValue(), 2, '.', '');
        $tagManagerOrder['tax'] = number_format($order->moneyVat->getValue(), 2, '.', '');
        $tagManagerOrder['coupon'] = $order->discountCoupons ? array_column($order->discountCoupons, 'coupon')[0] : '';

        return \yii\helpers\Json::encode($tagManagerOrder);
    }

    /**
     * @param SsShare $banner
     * @return string
     */
    public static function getJsonBanner(SsShare $banner)
    {
        $tagManagerBanner = $banner->toArray(['id', 'name']);

        $tagManagerBanner['creative'] = 'Вариант 1';
        $tagManagerBanner['position'] = $banner->banner_type;

        return \yii\helpers\Json::encode($tagManagerBanner);
    }

    /**
     * @param ShopContentElement $model
     * @param array $options
     * @return string
     */
    public static function getJsonModel($model, $options = [])
    {

        $tagManagerModel = [
            'id' => $model->id,
            'name' => self::getName($model),
            'price' => $model->price ? (int)$model->price->price : 0, //Если нет цены, то что бы не вызывало ошибку выставим 0
            //'brand' => '',
            'variant' => isset($options['modification_id']) ? self::getVariant($options['modification_id']) : '',
            'category' => self::getCategory($model, true), //$model->cmsTree->name,
            'position' => self::$postition++,
        ];

        if ($options) {
            $tagManagerModel = array_merge($tagManagerModel, $options);
        }

        return \yii\helpers\Json::encode($tagManagerModel);
    }

    /**
     * @param CmsContentElement $model
     * @param bool $skipLast
     * @return string
     */
    public static function getCategory($model, $skipLast = false)
    {
        $categoryName = '';
        /*if ($mainCategoryId = $model->getMainParentId()) {
            if ($mainTree = \common\lists\TreeList::getTreeById($mainCategoryId)) {
                $categoryName = $mainTree->name;
            }
        }*/

        // пробуем взять из хлебных крошек
        if ($skipLast) {
            $categories = array_slice(explode('/', \Yii::$app->breadcrumbs->getStringViewPath('/')), 3, -1);
        }
        else {
            $categories = array_slice(explode('/', \Yii::$app->breadcrumbs->getStringViewPath('/')), 3);
        }

        // в хлебных крошках пусто, лезем в базу :(
        if (!$categories && $model->cmsTree) {
            $parents = $model->cmsTree->parents;
            $parents[] = $model->cmsTree;

            $categories = array_slice(ArrayHelper::getColumn($parents, 'name'), 2);
        }

        $categoryName = join('/', $categories);

        return $categoryName;
    }

    /**
     * @param CmsContentElement $model
     * @return string
     */
    public static function getName($model)
    {
        if (!method_exists($model, 'isProductInEfir')) {
            return $model->name;
        }

        // в карточке товара и корзине эфир не рассчитывается, вычисляем сами
        if ($model->is_efir === null) {
            $model->is_efir = AirDayProductTime::find()
                ->where(['lot_id' => $model->id])
                ->andWhere('ss_mediaplan_air_day_product_time.begin_datetime >= UNIX_TIMESTAMP(DATE_FORMAT(NOW(), \'%Y-%m-%d 08:00:00\'))')
                ->count();
        }

        return $model->name . ($model->isProductInEfir() ? ' (air)' : '');
    }

    /**
     * @param int $productId
     * @return string
     */
    public static function getVariant($productId)
    {
        $cmsContentElement = \common\lists\Contents::getContentElementById($productId);
        $result = '';

        foreach ($cmsContentElement->getModifications() as $property) {
            $result .= ($result ? ', ' : '') . $property['name'] . ': ';
            $result .= join(',', array_column($property['values'], 'value'));
        }

        return $result;
    }

    /**
     * генерирует data атрибуты для html элемента
     * @param CmsContentElement $model
     * @param string $tagManagerList
     * @param ShopProduct $shopProduct
     * @return string
     */
    public static function getDataAttributes($model, $tagManagerList = null, $shopProduct = null)
    {
        if ($shopProduct == null) {
            $shopProduct = ShopProduct::getInstanceByContentElement($model);
        }

        $modelName = str_replace('"', "'", self::getName($model));
        $modelCategory = self::getCategory($model);

        return <<<HTML
    data-product-id="{$model->id}"
    data-product-name="{$modelName}"
    data-product-price="{$shopProduct->basePrice()}"
    data-product-category="{$modelCategory}"
    data-product-list="{$tagManagerList}"
HTML;
    }

    public function run()
    {
        if (!$this->model) {
            throw new \RuntimeException('Model not specified');
        }

        $tagManagerModel = self::getJsonModel($this->model, $this->options);

        $this->view->registerJs(<<<JS
        //sx.TagManager.addItem({$tagManagerModel});
JS
        );
    }
}