<?php

namespace modules\shopandshow\models\shop;

use common\helpers\Msg;
use common\helpers\User;
use \common\models\cmsContent\CmsContentElement;
use common\models\Product;
use \skeeks\cms\shop\models\ShopBasket as SXShopBasket;
use skeeks\cms\shop\models\ShopBasketProps;
use Yii;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * @property \modules\shopandshow\models\shop\ShopProduct $product
 * @property \common\models\cmsContent\CmsContentElement $cmsContentElement
 * @property ShopFuser $fuser
 * @property integer $has_removed
 * @property number $quantity
 * @property bool $isGift
 * @property integer $main_product_id
 * @property integer $kfss_position_id
 */
class ShopBasket extends SXShopBasket
{

    // признак подарка в ShopBasketProps
    const GIFT_CODE = "gift";
    const LOOKBOOK_CODE = "lookbook";
    /**
     * Признаки удаления
     */
    const HAS_REMOVED_TRUE = 1;
    const HAS_REMOVED_FALSE = 0;

    /**
     * Типы корзин
     */
    const TYPE_DEFAULT = 1;
    const TYPE_ONE_CLICK = 2;

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['main_product_id', 'kfss_position_id'], 'integer'],
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFuser()
    {
        return $this->hasOne(ShopFuser::className(), ['id' => 'fuser_id'])->inverseOf('shopBaskets');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(ShopProduct::className(), ['id' => 'product_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCmsContentElement()
    {
        return $this->hasOne(CmsContentElement::className(), ['id' => 'product_id']);
    }

    /**
     * @return integer
     */
    public function getIsGift()
    {
        return (bool)$this->getShopBasketProps()->andWhere(['code' => self::GIFT_CODE])->count();
    }

    public function setIsGift($value)
    {
        $this->isGift = $value;
    }

    /**
     * Пересчет состояния позиции согласно текущим данным
     * @return $this
     */
    public function recalculate()
    {
        if (!$this->product) {
            return $this;
        }

        $product = $this->product;
        $productModel = Product::findOne(['id' => $this->product_id]);

        $cmsContentElement = \common\lists\Contents::getContentElementById($product->id);
        $shopProduct = ShopProduct::getInstanceByContentElement($cmsContentElement); //$parentElement Записываем либо цену предложения либо главного товара
        $parentElement = $cmsContentElement->product;

        $this->measure_name = $product->measure ? $product->measure->symbol_rus : 'шт';
        $this->measure_code = $product->measure ? $product->measure->code : 796; // TODO: это что за число? алло!

        //$this->weight = $product->weight;
        // TODO: убрать, когда переедем на кфсс
        $this->weight = $this->getWeightPredvarit($parentElement->id) ?: $product->weight;
        $this->site_id = \Yii::$app->cms->site->id; //TODO: неправильно

        $this->dimensions = Json::encode([
            'height' => $product->height,
            'width' => $product->width,
            'length' => $product->length,
        ]);

        $this->price = !empty($productModel->new_price) ? $productModel->new_price : $shopProduct->basePrice();

        $this->name = $parentElement->getLotName();
        $this->main_product_id = $parentElement->id;

        $this->currency_code = 'RUB';

        //* KFSS API *//

        $this->save();

        //Обновляем заказ в КФСС
        $orderKfss = \Yii::$app->kfssApiV2->updateOrder(); //Если после добавления в корзну, то до инициализации заказа может и не быть

        \Yii::$app->kfssApiV2->recalculateOrder($orderKfss);
        //зменяется строкой выше, там так же будет пересчет возможных подарков
//        \Yii::$app->kfssApi->recalculateBasket($this, $orderKfss);
//        \Yii::$app->kfssApi->recalculateFuser();

        return $this;

        //* /KFSS API *//

        $product = $this->product;

        $cmsContentElement = \common\lists\Contents::getContentElementById($product->id);
        $shopProduct = ShopProduct::getInstanceByContentElement($cmsContentElement); //$parentElement Записываем либо цену предложения либо главного товара
        $parentElement = $cmsContentElement->product;

        $this->measure_name = $product->measure ? $product->measure->symbol_rus : 'шт';
        $this->measure_code = $product->measure ? $product->measure->code : 796; // TODO: это что за число? алло!

        //$this->weight = $product->weight;
        // TODO: убрать, когда переедем на кфсс
        $this->weight = $this->getWeightPredvarit($parentElement->id) ?: $product->weight;
        $this->site_id = \Yii::$app->cms->site->id; //TODO: неправильно

        $this->dimensions = Json::encode([
            'height' => $product->height,
            'width' => $product->width,
            'length' => $product->length,
        ]);

        $this->price = $shopProduct->basePrice();

        $this->name = $parentElement->getLotName();
        $this->main_product_id = $parentElement->id;

        $this->currency_code = 'RUB';
        \Yii::$app->request->

        /**
         * Пересчет скидок
         */
        ShopDiscount::basketRecalculate($this);

        if (false) {

            //Если это предложение, нужно добавить свойства
            if ($parentElement && !$this->isNewRecord) {
                if ($properties = $product->cmsContentElement->relatedPropertiesModel->toArray()) {
                    foreach ($properties as $code => $value) {
                        if (!$this->getShopBasketProps()->andWhere(['code' => $code])->count() && $value) {
                            $property = $product->cmsContentElement->relatedPropertiesModel->getRelatedProperty($code);

                            $basketProperty = new ShopBasketProps();
                            $basketProperty->shop_basket_id = $this->id;
                            $basketProperty->code = $code;
                            $basketProperty->value = $product->cmsContentElement->relatedPropertiesModel->getSmartAttribute($code);
                            $basketProperty->name = $property->name;

                            $basketProperty->save();
                        }
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Включить или отключить позицию в корзине
     *
     * @return bool
     * @throws \Exception
     */
    public function hasRemovedUpdate()
    {
        if ($this->hasRemoved()) {
            $this->has_removed = self::HAS_REMOVED_FALSE;
        } else {
            $this->has_removed = self::HAS_REMOVED_TRUE;
        }

        $this->save();

        //Если есть номер заказа - обновляем заказ через АПИ
        if (\Yii::$app->shop->shopFuser->external_order_id){
            \Yii::$app->kfssApiV2->updateOrder();
        }

        return \Yii::$app->shop->shopFuser->recalculate()->save();
    }

    /**
     * @return bool
     */
    public function hasRemoved()
    {
        return $this->has_removed === self::HAS_REMOVED_TRUE;
    }

    /**
     * Добавить товар в корзину
     * @param $productId
     * @param $quantity
     * @param $basketParams
     * @return bool
     */
    public static function addProduct($productId, $quantity = 1, $basketParams = [])
    {

        /**
         * @var ShopProduct $product
         */
        $product = ShopProduct::find()->where(['id' => $productId])->one();

        if (!$product) {
            return false;
        }

        if ($product->measure_ratio > 1) {
            if ($quantity % $product->measure_ratio != 0) {
                $quantity = $product->measure_ratio;
            }
        }

        /**
         * @var self $shopBasket
         */
        $shopBasket = self::find()->where([
            'fuser_id' => \Yii::$app->shop->shopFuser->id,
            'product_id' => $productId,
            'order_id' => null,
        ])->one();

        if (!$shopBasket) {
            $shopBasket = new self([
                'fuser_id' => \Yii::$app->shop->shopFuser->id,
                'product_id' => $product->id,
                'quantity' => 0,
            ]);
        }

        /**
         * Если товар был раннее удален то сбрасываем количество
         */
        if ($shopBasket->hasRemoved()) {
            $shopBasket->quantity = $quantity;
        } else {
            $shopBasket->quantity = $shopBasket->quantity + $quantity;
        }

        $shopBasket->has_removed = self::HAS_REMOVED_FALSE; //При добавлении в корзину удаленного товара ставим его не удаленным


        \Yii::$app->shop->shopFuser->link('site', \Yii::$app->cms->site);

        //            $rr->data = \Yii::$app->shop->shopFuser->jsonSerialize();


        $shopBasket->recalculate()->save();

        if (!empty($basketParams)) {
            foreach ($basketParams as $paramName => $paramValue) {
                $basketProperty = new ShopBasketProps();
                $basketProperty->shop_basket_id = $shopBasket->id;
                $basketProperty->code = $paramName;
                $basketProperty->value = (string)$paramValue;
                $basketProperty->name = $paramName;

                $basketProperty->save();
            }
        }


        if (!isset(\Yii::$app->request->cookies['nocache'])) {
            \Yii::$app->response->cookies->add(new \yii\web\Cookie([
                'name' => 'nocache',
                'value' => 'nocache'
            ]));
        }


        return \Yii::$app->shop->shopFuser->recalculate()->save();
    }


    /**
     * Добавить товар в корзину
     * @param $productId
     * @param $quantity
     * @param $basketParams
     * @return bool
     */
    public static function addProductOneClick($productId, $quantity = 1, $basketParams = [])
    {

        /**
         * @var ShopProduct $product
         */
        $product = ShopProduct::find()->where(['id' => $productId])->one();

        if (!$product) {
            return false;
        }

        if ($product->measure_ratio > 1) {
            if ($quantity % $product->measure_ratio != 0) {
                $quantity = $product->measure_ratio;
            }
        }

        /**
         * @var self $shopBasket
         */
        $shopBasket = self::find()->where([
            'fuser_id' => \Yii::$app->shop->shopFuser->id,
            'product_id' => $productId,
            'order_id' => null,
            'type' => self::TYPE_ONE_CLICK,
        ])->one();

        if (!$shopBasket) {
            $shopBasket = new self([
                'fuser_id' => \Yii::$app->shop->shopFuser->id,
                'product_id' => $product->id,
                'quantity' => 0,
                'type' => self::TYPE_ONE_CLICK,
            ]);
        }

        /**
         * Если товар был раннее удален то сбрасываем количество
         */
        if ($shopBasket->hasRemoved()) {
            $shopBasket->quantity = $quantity;
        } else {
            $shopBasket->quantity = $shopBasket->quantity + $quantity;
        }

        $shopBasket->has_removed = self::HAS_REMOVED_FALSE; //При добавлении в корзину удаленного товара ставим его не удаленным


        \Yii::$app->shop->shopFuser->link('site', \Yii::$app->cms->site);

        //            $rr->data = \Yii::$app->shop->shopFuser->jsonSerialize();


        $shopBasket->recalculate()->save();

        if (!empty($basketParams)) {
            foreach ($basketParams as $paramName => $paramValue) {
                $basketProperty = new ShopBasketProps();
                $basketProperty->shop_basket_id = $shopBasket->id;
                $basketProperty->code = $paramName;
                $basketProperty->value = (string)$paramValue;
                $basketProperty->name = $paramName;

                $basketProperty->save();
            }
        }


        return \Yii::$app->shop->shopFuser->recalculate(ShopBasket::TYPE_ONE_CLICK)->save();
    }

    /**
     * поднимаемся 2 раза вверх из-за новой структуры 2-5-10
     * @return string
     */
    public function getUrl()
    {
        if ($this->product)
        {
            $parent = $this->product->cmsContentElement->parentContentElement;
            //Это предложение у него есть родительский элемент
            if ($parent && $parent = $parent->parentContentElement)
            {
                return $parent->url;
            } else
            {
                return $this->product->cmsContentElement->url;
            }
        }

        return $this->detail_page_url;
    }

    /**
     * поднимаемся 2 раза вверх из-за новой структуры 2-5-10
     * @return string
     */
    public function getAbsoluteUrl()
    {
        if ($this->product)
        {
            $parent = $this->product->cmsContentElement->parentContentElement;
            //Это предложение у него есть родительский элемент
            if ($parent && $parent = $parent->parentContentElement)
            {
                return $parent->absoluteUrl;
            } else
            {
                return $this->product->cmsContentElement->absoluteUrl;
            }
        }

        return Url::home() . $this->detail_page_url;
    }

    /**
     * картинки к товару лежат на втором уровне в карточке
     * @return null|\skeeks\cms\models\CmsStorageFile
     */
    public function getImage()
    {
        if ($this->product)
        {
            //Это предложение у него есть родительский элемент
            if ($parent = $this->product->cmsContentElement->parentContentElement) {
                //В карточке, если она не цветная, может и не быть изображение. Если так - берем из лота.
                if ($parent->image){
                    return $parent->image;
                }else{
                    $product = $parent->parentContentElement;
                    return $product->image;
                }
            } else {
                return $this->product->cmsContentElement->image;
            }
        }

        return null;
    }

    /**
     * вес товара (из битриксовых свойств)
     * @param $productId
     * @return int
     * @throws \yii\db\Exception
     */
    public function getWeightPredvarit($productId)
    {
        $sql = <<<SQL
    SELECT pr.value
    FROM cms_content_element_property as pr 
    WHERE pr.element_id = :id
      AND pr.property_id = (SELECT p.id FROM cms_content_property AS p WHERE p.code = 'VES_PREDVARIT')
SQL;

        $weight = \Yii::$app->db->createCommand($sql, [
            ':id' => $productId,
        ])->queryScalar();

        return (int)$weight;
    }
}