<?php


namespace common\models;


use common\helpers\Common;
use common\helpers\Price;
use common\models\query\ShopBasketQuery;
use modules\shopandshow\models\shop\ShopFuser;

class ShopBasket extends \common\models\generated\models\ShopBasket
{
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

    public static function find()
    {
        return new ShopBasketQuery(get_called_class());
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::className(), ['id' => 'product_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLot()
    {
        return $this->hasOne(Product::className(), ['id' => 'main_product_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFuser()
    {
        return $this->hasOne(ShopFuser::className(), ['id' => 'fuser_id'])->inverseOf('shopBaskets');
    }

    public static function addOneClick($offerId, $quantity = 1, $source = ShopOrder::SOURCE_SITE)
    {
        return self::add($offerId, $quantity, self::TYPE_ONE_CLICK, $source);
    }

    public static function add($offerId, $quantity = 1, $basketType = self::TYPE_DEFAULT, $source = ShopOrder::SOURCE_SITE)
    {
        $result = ['success' => false, 'message' => ''];

        $fuser = \Yii::$app->shop->shopFuser;
        $fuserId = $fuser->id;

        //Для подстраховки будет проверять возможность прихода любого типа сущности
        //TODO Подумать на каком этапе обыграть возможную попытку доабвить в корзину лот/карточку товара

        $offer = Product::findOne($offerId);

        if (!$offer->isOffer()) {
            $error = "Попытка добавление не модификации в корзину. fuserId='{$fuserId}' | productId='{$offerId}' | contentId='{$offer->content_id}' | [{$offer->name}]";
            var_dump($error);
            \Yii::error($error, __METHOD__);
            return [
                'success' => false,
                'message' => 'Ошибка при добавлении типа товара'
            ];
        }

        //Возможно этот товар уже в корзине или был там (имеет флаг удаленности)
        /** @var self $shopBasket */
        $shopBasket = self::find()
            ->where([
                'fuser_id' => $fuserId,
                'product_id' => $offerId,
                'order_id' => null,
                'type' => $basketType
            ])
            ->one();

        $quantityInCart = $shopBasket && !$shopBasket->hasRemoved() ? $shopBasket->quantity : 0;
        $quantityTotal = $quantityInCart + $quantity;

        //Кол-во после добавления не должно превышать доступное для продажи кол-во
        if (Product::canSale($offer, $quantityTotal)) {
            //С товаром все ок, продавать можем, добавляем в корзину

            if (!$shopBasket) {
                $shopBasket = new self();
                $shopBasket->setAttributes([
                    'fuser_id' => $fuserId,
                    'product_id' => $offerId,
                    'quantity' => 0,
                    'type' => $basketType
                ]);
            }

            if ($shopBasket->hasRemoved()) {
                $shopBasket->setAttributes([
                    'has_removed' => Common::BOOL_N_INT,
                    'quantity' => $quantity
                ]);
            } else {
                $shopBasket->quantity = $shopBasket->quantity + $quantity;
            }

            //* Тип цены по которой продается товар (связано с источником (каналом) продажи) *//
            $productPrice = Price::getPriceForSource($offer, $source);

            $shopBasket->price = $productPrice;
            $shopBasket->name = $offer->lot->name;
            $shopBasket->main_product_id = $offer->lot->id;
            $shopBasket->currency_code = 'RUB';

            if (!$shopBasket->save()) {
                $result['success'] = false;
                $result['message'] = $shopBasket->getErrors();
            } else {

                $fuser->recalculate();

                $result['success'] = true;
                $result['message'] = 'Товар успешно добавлен';
                $result['quantity'] = (int)$shopBasket->quantity;
            }
        } else {
            $result['message'] = 'Недостаточно количества';
        }

        return $result;
    }

    //Для указанной корзины выставляет запрошенной кол-во (если можно)
    //Если кол-во 0 - отмечает как удалленную позицию.
    public function updateBasket($quantity)
    {
        $fUser = \Yii::$app->shop->shopFuser;

        /** @var Product $product */
        $product = $this->product;;
        $errors = [];

        if ($quantity <= $product->new_quantity) {
            if ($quantity){
                $this->quantity = $quantity;
                if (!$this->save()) {
                    $errors[] = var_export($this->getErrors(), true);
                }
            }else{
                $result = $this->hasRemovedUpdate();
            }


            if (!$errors) {
                if (!$fUser->recalculate()->save()) {
                    $errors[] = 'Возникла ошибка пересчета.';
                }
            }

        }else{
            $errors[] = "Недостаточно количества";
        }

        return $errors ? implode(' / ', $errors) : true;
    }

    public function hasRemoved()
    {
        return $this->has_removed == Common::BOOL_Y_INT;
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
            $this->kfss_position_id = null; //Удаленный товар отлинковываем от синхры с кфсс
        }

        if (!$this->save()){
            //Какая то ошибка
            return false;
        }else{
            return true;
        }

        //Если есть номер заказа - обновляем заказ через АПИ
//        if (\Yii::$app->shop->shopFuser->external_order_id){
//            \Yii::$app->kfssApiV2->updateOrder();
//        }

//        return \Yii::$app->shop->shopFuser->recalculate()->save();
    }

    /**
     * Пересчет состояния позиции согласно текущим данным
     * @return $this
     */
    public function recalculate()
    {
        /** @var Product $product */
        if ($product = $this->product) {
            //TODO Добавить возможность использовать любой тип цены в качестве текущего
            //Корректно ли цену корзины брать всегда из товара, ведь она меняется если есть какая то акция, после обновы из КФСС она меняется
            $this->price = $product->new_price ?: false;
            //discount_price это скидка по акциям на единицу товара, а не скидка потому что цена товара в каталоге со скидкой!!!
            $this->discount_price = $product->new_price && $product->new_price_old ? $product->new_price_old - $product->new_price : 0;
            $this->name = $product->lot->name;
            $this->main_product_id = $product->lot->id;
            $this->currency_code = 'RUB';

            $this->save();
        }

        return $this;

        //* /KFSS API *//
    }

    /**
     * Итоговая стоимость скидки
     * @return int
     */
    public function getMoneyDiscount()
    {
        return $this->discount_price;
    }

    public function getMoneyTotal()
    {
        return $this->price * $this->quantity;
    }

    public function getImage ()
    {
        /** @var Product $offer */
        $offer = $this->product;
        $card = Product::findOne($offer->parent_content_element_id);

        return $card ? $card->image : false;
    }

    public function getUrl()
    {
        $offer = $this->product;
        $card = Product::findOne($offer->parent_content_element_id);
        return $card ? $card->url : false;
    }
}