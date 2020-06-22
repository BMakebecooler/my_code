<?php
/**
 * Created by PhpStorm.
 * User: Soskov_da
 * Date: 13.07.2017
 * Time: 10:56
 */

namespace modules\shopandshow\components\shop;

use modules\shopandshow\models\shop\ShopDiscount;
use modules\shopandshow\models\shop\shopdiscount\Entity;
use modules\shopandshow\models\shop\ShopDiscountCoupon;
use modules\shopandshow\models\shop\ShopFuser;
use modules\shopandshow\models\shop\ShopBasket;
use modules\shopandshow\models\shop\ShopProduct;
use modules\shopandshow\models\shop\SsShopDiscountLogic;
use modules\shopandshow\models\shop\SsShopFuserDiscount;
use skeeks\cms\components\Cms;
use skeeks\cms\shop\models\ShopBasketProps;
use yii\base\Component;

class ShopDiscountCalculateFuser extends Component
{
    /** @var ShopFuser */
    public $shopFuser;

    /** @var SsShopFuserDiscount */
    public $shopFuserDiscount;

    /**
     * Пересчитывает скидку в целом на всю корзину
     * @return bool
     */
    public function recalculate()
    {

        //* Пересчет механизмами сайта не требуется, берем инфу из кфсс *//

        $orderKfss = \Yii::$app->kfssApiV2->getOrder(__METHOD__);

        $this->shopFuserDiscount = $this->shopFuser->ssShopFuserDiscount;

        if(!$this->shopFuserDiscount) {
            $this->shopFuserDiscount = new SsShopFuserDiscount();
            $this->shopFuserDiscount->link('shopFuser', $this->shopFuser);
        }

        $this->shopFuserDiscount->discount_price = 0;
        $this->shopFuserDiscount->discount_name = "";
        $this->shopFuserDiscount->free_delivery_discount_id = null;

//        $this->shopFuserDiscount->discount_price = 300;
//        $this->shopFuserDiscount->discount_name = "Скидка за общую сумму корзины";
//        $this->shopFuserDiscount->free_delivery_discount_id = null;

        if (!empty($orderKfss)) {
            $orderOriginalSum = $orderKfss['originalSum'] ?? 0;
            $orderSum = $orderKfss['sum'] ?? 0;

            if (!empty($orderKfss['discounts'])){
                $discountNames = array_column($orderKfss['discounts'], 'name');
                $this->shopFuserDiscount->discount_name = implode(' + ', $discountNames);
            }

        }

        //Если  из кфсс пришлаи скидки - то запишутся они, если ничего не пришло, запишется пустота (или сбросится до пустоты)
        //TODO Если приходит пустота то возможно все таки лучше удалять пустую запись, а не просто обнулять
        $this->shopFuserDiscount->save();

        return true;

        //* /Пересчет механизмами сайта не требуется, берем инфу из кфсс *//

        //$this->recalcCoupons();
        $this->recalcGifts();

        $this->shopFuserDiscount = $this->shopFuser->ssShopFuserDiscount;

        if(!$this->shopFuserDiscount) {
            $this->shopFuserDiscount = new SsShopFuserDiscount();
            $this->shopFuserDiscount->link('shopFuser', $this->shopFuser);
        }

        $this->shopFuserDiscount->discount_price = 0;
        $this->shopFuserDiscount->discount_name = "";
        $this->shopFuserDiscount->free_delivery_discount_id = null;

        $shopDiscounts = ShopDiscount::getDiscountsToRecalc([
            ShopDiscount::VALUE_TYPE_LADDER,
            ShopDiscount::VALUE_TYPE_F,
            ShopDiscount::VALUE_TYPE_DELIVERY,
            ShopDiscount::VALUE_TYPE_BASKET_QTY,
        ]);
        if ($shopDiscounts) {
            $discountNames = [];

            foreach ($shopDiscounts as $key => $shopDiscount) {
                $discountPrice = 0;
                $baskets = $shopDiscount->canApplyFuser($this->shopFuser);
                if(empty($baskets)) continue;

                $basketsPrice = $this->calcBasketsBasePrice($baskets);

                // фиксированные скидки
                if ($shopDiscount->value_type == ShopDiscount::VALUE_TYPE_F) {
                    $discountPrice = $this->calcFixedDiscount($shopDiscount, $basketsPrice);
                }
                // лестница скидок
                elseif ($shopDiscount->value_type == ShopDiscount::VALUE_TYPE_LADDER) {
                    $discountPrice = $this->calcLadderDiscount($shopDiscount, $basketsPrice);
                }
                // Бесплатная доствка
                elseif ($shopDiscount->value_type == ShopDiscount::VALUE_TYPE_DELIVERY) {
                    $this->calcFreeDelivery($shopDiscount, $baskets);
                }
                // Скидка на наименьшую сумму позиции корзины
                elseif ($shopDiscount->value_type == ShopDiscount::VALUE_TYPE_BASKET_QTY) {
                    $this->calcBasketQtyDiscount($shopDiscount, $baskets);
                }

                if($discountPrice) $discountNames[] = $shopDiscount->name;

                //Нужно остановится и не применять другие скидки
                if ($shopDiscount->last_discount === Cms::BOOL_Y) {
                    break;
                }
            }

            $this->shopFuserDiscount->discount_name = implode(" + ", $discountNames);
        }

        $this->shopFuserDiscount->save();

        return true;
    }

    public function recalcCoupons()
    {
        // пересчет купонов
        $discountCoupons = [];
        /** @var ShopDiscountCoupon $shopDiscountCoupon */
        foreach ($this->shopFuser->discountCoupons as $shopDiscountCoupon) {
            // Проверяем, можно ли еще использовать этот купон
            if ($shopDiscountCoupon->canApply($this->shopFuser)) {
                $discountCoupons[] = $shopDiscountCoupon->id;
            }
        }
        $this->shopFuser->discount_coupons = array_unique($discountCoupons);
        $this->shopFuser->save();
    }

    public function recalcGifts()
    {
        $currentGifts = [];
        // список разрешенных пождарков в акции
        $allowedGifts = [];

        $giftDiscounts = ShopDiscount::getGiftDiscountsToRecalc();

        /** @var ShopBasket $shopBasket */
        foreach ($this->shopFuser->shopBaskets as $shopBasket) {
            if($shopBasket->isGift) {
                $currentGifts[$shopBasket->product_id] = $shopBasket;
            }
            else {
                foreach ($giftDiscounts as $discount) {
                    if ($discount->canApply($shopBasket)) {
                        $allowedGifts[$discount->gift] = $shopBasket;
                    }
                }
            }
        }

        foreach ($giftDiscounts as $discount) {
            // 1. если подарок есть, а акционный товар удален (или нарушены иные условия акции)
            /** @var ShopBasket[] $giftsToDelete */
            $giftsToDelete = array_diff_key($currentGifts, $allowedGifts);

            foreach($giftsToDelete as $gift => $shopBasketToDelete) {
                $shopBasketToDelete->has_removed = ShopBasket::HAS_REMOVED_TRUE;
                $shopBasketToDelete->save();
                unset($currentGifts[$gift]);
            }

            // 2. если подарка в корзине еще нет - добавляем
            $basketWithGiftsToAdd = array_diff_key($allowedGifts, $currentGifts);

            foreach($basketWithGiftsToAdd as $gift => $basketWithGift) {
                // проверяем, не добавляли ли мы уже этот подарок на предыдущей итерации
                // такое может быть, когда активно сразу 2 акции с подарками
                if(!array_key_exists($gift, $allowedGifts) || array_key_exists($gift, $currentGifts)) continue;
                // сохраняем добавленный подарок
                $currentGifts[$gift] = $basketWithGift;

                /** @var ShopProduct $product */
                $product = ShopProduct::findOne($gift);

                // такого продукта нет, кому-то подарка не достанется :(
                if (!$product) continue;

                $shopBasket = new ShopBasket([
                    'fuser_id' => $this->shopFuser->id,
                    'product_id' => $product->id,
                    'has_removed' => ShopBasket::HAS_REMOVED_FALSE,
                    'discount_name' => $discount->name,
                    'isGift' => true,
                ]);

                if(!$shopBasket->recalculate()->save()) throw new \yii\db\Exception('Ошибка при создании подарка: '.print_r($shopBasket->getErrors(), true));

                $basketProperty = new ShopBasketProps();
                $basketProperty->shop_basket_id = $shopBasket->id;
                $basketProperty->code = 'gift';
                $basketProperty->value = (string)$basketWithGift->id;
                $basketProperty->name = 'gift';

                if(!$basketProperty->save()) throw new \yii\db\Exception('Ошибка при сохранении параметров подарка: '.print_r($basketProperty->getErrors(), true));
            }
        }
    }

    /**
     * @param ShopBasket[] $baskets
     *
     * @return number $basketsPrice
     */
    public function calcBasketsBasePrice(array $baskets)
    {
        $basketsPrice = 0;
        foreach ($baskets as $basket) {
            $cmsContentElement = \common\lists\Contents::getContentElementById($basket->product->id);
            $shopProduct = ShopProduct::getInstanceByContentElement($cmsContentElement);
            $basketsPrice += $shopProduct->basePrice() * $basket->quantity;
        }

        return $basketsPrice;
    }

    /**
     * Считает скидку товара по алгоритму процентной акции
     * @param ShopDiscount $shopDiscount
     * @param number      $basketsPrice
     *
     * @return number $discountPrice
     */
    protected function calcFixedDiscount(ShopDiscount $shopDiscount, $basketsPrice)
    {
        $discountPrice = (int)$shopDiscount->value;

        // купон на 500 рублей применяется на сумму не более 10% от суммы заказа
        if ($shopDiscount->code == ShopDiscount::DISCOUNT_CODE_500RUB) {
            $discountPricePercent = round($basketsPrice * 0.10);
            $discountPrice = min($discountPricePercent, $discountPrice);
        }

        // ограничение на макс. скидку
        if ($shopDiscount->max_discount > 0 && $discountPrice > $shopDiscount->max_discount) $discountPrice = $shopDiscount->max_discount;

        // скидка не может превышать сумму корзины с учетом уже посчитанных скидок
        if ($discountPrice > $basketsPrice + (int)$this->shopFuserDiscount->discount_price) $discountPrice = $basketsPrice + (int)$this->shopFuserDiscount->discount_price;

        $this->shopFuserDiscount->discount_price += $discountPrice;

        return $discountPrice;
    }

    /**
     * Считает скидку корзины по алгоритму лестница скидок
     * @param ShopDiscount $shopDiscount
     * @param number       $basketsPrice
     *
     * @return number $discountPrice
     */
    protected function calcLadderDiscount(ShopDiscount $shopDiscount, $basketsPrice)
    {
        $ladderLogic = $this->getDiscountLogic($shopDiscount, $basketsPrice);
        if(!$ladderLogic) return 0;

        $discountPrice = 0;
        if ($ladderLogic->discount_type == SsShopDiscountLogic::DISCOUNT_TYPE_PERCENT) {
            $percent = $ladderLogic->discount_value / 100;
            $discountPrice = intval($basketsPrice * $percent);
        }
        elseif ($ladderLogic->discount_type == SsShopDiscountLogic::DISCOUNT_TYPE_FIXED) {
            $discountPrice = $ladderLogic->discount_value;
        }

        // ограничение на макс. скидку
        if($shopDiscount->max_discount > 0 && $discountPrice > $shopDiscount->max_discount) $discountPrice = $shopDiscount->max_discount;

        $this->shopFuserDiscount->discount_price += $discountPrice;

        return $discountPrice;
    }

    /**
     * Устанавливает признак бесплатной доставки
     *
     * @param ShopDiscount $shopDiscount
     * @param ShopBasket[] $shopBaskets
     *
     * @return number $discountPrice
     */
    protected function calcFreeDelivery(ShopDiscount $shopDiscount, array $shopBaskets)
    {
        // Если хотя бы 1 товар удовлетворяет условию
        if (sizeof($shopBaskets) > 0) {

            $reqSum = array_reduce($shopDiscount->configurations, function ($carry, $configuration) {
                if ($configuration->entity->class != Entity::SUM_ENTITY) {
                    return $carry;
                }
                return $carry + $configuration->getValues()->one()->value;
            }, 0);

            $money = \Yii::$app->money->newMoney();
            foreach ($shopBaskets as $shopBasket)
            {
                $money = $money->add($shopBasket->moneyOriginal->multiply($shopBasket->quantity));
            }
            $basketSum = $money->getValue();

            if ($basketSum >= $reqSum) {
                $this->shopFuserDiscount->link('freeDeliveryDiscount', $shopDiscount);
            }
        }

        return 0;
    }

    /**
     * Считает скидку корзины по алгоритму "Скидка на наименьшую сумму позиции корзины"
     *
     * @param ShopDiscount $shopDiscount
     * @param ShopBasket[] $shopBaskets
     *
     * @return number $discountPrice
     */
    protected function calcBasketQtyDiscount(ShopDiscount $shopDiscount, array $shopBaskets)
    {
        $basketItemsQuantity = array_sum(\common\helpers\ArrayHelper::getColumn($shopBaskets, 'quantity'));

        /** @var ShopBasket $minBasket */
        $minBasket = reset($shopBaskets);
        foreach ($shopBaskets as $shopBasket) {
            if ($shopBasket->price < $minBasket->price) {
                $minBasket = $shopBasket;
            }
        }

        // сумма скидки = наименьшая сумма позиции корзины
        $discountPrice = $minBasket->price;

        // размазываем эту скидку по всем позициям
        $itemDiscount = $discountPrice / $basketItemsQuantity;

        foreach ($shopBaskets as $i => $shopBasket) {
            $discountPrice -= $itemDiscount * $shopBasket->quantity;

            // на последний товар накручиваем остаток (из-за погрешности округлений при делении)
            if (floor($discountPrice) < floor($itemDiscount)) {
                $itemDiscount += $discountPrice;
                $itemDiscount = floor($itemDiscount);
            }

            $shopBasket->discount_price = round($itemDiscount);
            $shopBasket->discount_value = (string)(round($itemDiscount * $shopBasket->quantity));
            $shopBasket->discount_name = $shopDiscount->name;
            $shopBasket->price -= round($itemDiscount);

            $shopBasket->save();
        }

        return 0;
    }

    /**
     * Получает нужную логику для обсчета лестницы скидок
     *
     * @param ShopDiscount $shopDiscount
     * @param number       $logicValue
     *
     * @return SsShopDiscountLogic|null
     */
    protected function getDiscountLogic(ShopDiscount $shopDiscount, $logicValue)
    {
        /** @var SsShopDiscountLogic[] $discountLogics */
        $discountLogics = $shopDiscount->getShopDiscountLogics()->all();
        foreach ($discountLogics as $discountLogic) {
            // цена корзины или кол-во элементов в корзине
            if ($discountLogic->logic_type == SsShopDiscountLogic::LOGIC_TYPE_BASKET || $discountLogic->logic_type == SsShopDiscountLogic::LOGIC_TYPE_QUANTITY) {
                if ($logicValue < $discountLogic->value) {
                    continue;
                }
            }
            // остальные пропускаем
            else {
                continue;
            }

            return $discountLogic;
        }
        return null;
    }
}