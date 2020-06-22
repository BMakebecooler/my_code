<?php
namespace modules\shopandshow\models\shop\delivery;

use common\helpers\ArrayHelper;
use modules\shopandshow\models\shop\ShopBasket;
use modules\shopandshow\models\shop\ShopDiscount;
use skeeks\cms\models\CmsContentElement;
use skeeks\cms\models\Tree;
use yii\base\Model;
use yii\helpers\Html;

class FreeDelivery extends Model
{
    /** @var ShopDiscount */
    protected $shopDiscount;

    protected $sum = null;
    protected $categories = [];
    protected $products = [];

    public function __construct(ShopDiscount $shopDiscount, array $config = [])
    {
        parent::__construct($config);

        $this->shopDiscount = $shopDiscount;

        //$this->sum = $shopDiscount->getSum();
        $this->sum = ShopDiscount::getFreeDeliveryPrice();
        $this->categories = $shopDiscount->getCategoryIds();
        $this->products = $shopDiscount->getProductsIds();
    }

    /**
     * Общая акция на БД без условий
     * @return bool
     */
    public function isCommon()
    {
        return empty($this->products) && empty($this->categories) && $this->sum > 0;
    }

    /**
     * Акция цтс + лот
     * @return bool
     */
    public function isCtsPlusLot()
    {
        return !empty($this->products) && $this->sum == 0;
    }

    /**
     * Сумма для условия выполнения акции
     * @return int|null
     */
    public function getSum()
    {
        return $this->sum;
    }

    /**
     * Лоты, участвующие в акции
     * @return array
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * Разделы, удовлетворяющие акции
     * @return array
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Генерирует текст для tooltip подсказки бесплатной доставки
     * @return string
     */
    public function getConditionsForTooltip()
    {
        $text = '';
        if ($this->isCtsPlusLot()) {
            $ctsProduct = \common\lists\Contents::getCtsProduct();
            if (!in_array($ctsProduct->id, \Yii::$app->shop->shopFuser->getShopBasketsWithoutGifts()->select('main_product_id')->asArray()->column())) {
                $text .= 'Основной лот: <br>';
                $text .= Html::a($ctsProduct->name, $ctsProduct->url, ['target' => '_blank']).Html::tag('br');
                $text .= '+ любой лот из списка: <br>';
            }
        }

        if ($this->products) {
            /** @var CmsContentElement[] $products */
            $products = CmsContentElement::find()->where(['id' => $this->products])->all();

            $text .= 'Лоты: <br>';

            foreach ($products as $product) {
                $text .= Html::a($product->name, $product->url, ['target' => '_blank']).Html::tag('br');
            }
        }


        if ($this->categories) {
            /** @var Tree[] $trees */
            $trees = Tree::find()->where(['id' => $this->categories])->all();

            $text .= 'Категории: <br>';

            foreach ($trees as $tree) {
                $text .= Html::a($tree->name, $tree->url, ['target' => '_blank']).Html::tag('br');
            }
        }

        return $text;
    }

    /**
     * Вычисляет оставшуюся сумму до бесплатной доставки
     * @return float|int|null
     */
    public function getRemainSum()
    {
        $currentSum = 0;
        $baskets = \Yii::$app->shop->shopFuser->shopBasketsWithoutGifts;
        foreach ($baskets as $basket) {
            if ($this->canApply($basket)) {
                $currentSum += (int)$basket->price * $basket->quantity;
            }
        }

        return $this->sum - $currentSum;
    }

    /**
     * Проверяет, удовлетворяет ли товар из корзины условиям акции
     * @param ShopBasket $shopBasket
     * @return bool
     */
    protected function canApply(ShopBasket $shopBasket)
    {
        if ($this->isCommon()) {
            return true;
        }

        $product = $shopBasket->cmsContentElement;

        // если есть в списке лотов
        if ($this->products) {
            if ($this->canApplyProduct($product)) {
                return true;
            }
        }

        // если есть в списке категорий
        if ($this->categories) {
            if ($this->canApplyTree($product->cmsTree)) {
                return true;
            }
        }

        return false;
    }

    /**
     * есть ли товар в списке товаров акции
     * @param CmsContentElement $product
     * @return bool
     */
    protected function canApplyProduct(CmsContentElement $product)
    {
        if (in_array($product->id, $this->products)) {
            return true;
        }

        return $product->parent_content_element_id && in_array($product->parent_content_element_id, $this->products);
    }

    /**
     * есть ли категория в списке категорий акции (рекурсивно)
     * @param Tree $tree
     * @return bool
     */
    protected function canApplyTree(Tree $tree)
    {
        if (in_array($tree->id, $this->categories)) {
            return true;
        }

        return $tree->pid && $this->canApplyTree($tree->parent);
    }
}