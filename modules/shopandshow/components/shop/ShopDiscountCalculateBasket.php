<?php
/**
 * Created by PhpStorm.
 * User: Soskov_da
 * Date: 13.07.2017
 * Time: 10:56
 */

namespace modules\shopandshow\components\shop;

use function get_class;
use modules\shopandshow\models\shop\ShopDiscount;
use modules\shopandshow\models\shop\ShopBasket;
use modules\shopandshow\models\shop\shopdiscount\Entity;
use modules\shopandshow\models\shop\SsShopDiscountLogic;
use skeeks\cms\components\Cms;
use skeeks\modules\cms\money\Money;
use yii\base\Component;
use yii\base\ErrorException;

class ShopDiscountCalculateBasket extends Component
{
    /** @var ShopBasket */
    public $shopBasket;

    /**
     * Пересчитывает элемент корзины
     * @throws ErrorException
     * @return bool
     */
    public function recalculate()
    {
        if(!$this->shopBasket instanceof ShopBasket) {
            throw new ErrorException('Не найден элемент корзины при пересчете скидки');
        }

        //* Пересчет механизмами сайта не требуется, берем инфу из кфсс *//

        $orderKfss = \Yii::$app->kfssApiV2->getOrder(__METHOD__);
        $kfssOffercntId = $this->shopBasket->cmsContentElement->kfss_id;

        if ($kfssOffercntId && !empty($orderKfss['positions']) && !empty($orderKfss['positions'][$kfssOffercntId])){
            $kfssElement = $orderKfss['positions'][$kfssOffercntId];
            //Скидки для товара
            $this->shopBasket->discount_price = $kfssElement['originalPrice'] - $kfssElement['price'];
            $this->shopBasket->discount_value = "";
//            $this->shopBasket->discount_name = "Тестовая скидка 1 + тестовая скидка 2";
            $this->shopBasket->price = $kfssElement['price'];

            //Запишем все скидки связанные с данным товаром
            if (!empty($kfssElement['discounts'])){
                $discountNames = array_column($kfssElement['discounts'], 'name');
                $this->shopBasket->discount_name = implode(' + ', $discountNames);
            }
        }

        return true;

        //* //Пересчет механизмами сайта не требуется, берем инфу из кфсс *//

        if($this->shopBasket->isGift) {
            return $this->calcGiftDiscount();
        }

        $this->shopBasket->discount_price = 0;
        $this->shopBasket->discount_value = "";
        $this->shopBasket->discount_name = "";

        $shopDiscounts = ShopDiscount::getDiscountsToRecalc([ShopDiscount::VALUE_TYPE_P, ShopDiscount::VALUE_TYPE_LADDER]);

        if ($shopDiscounts) {
            foreach ($shopDiscounts as $key => $shopDiscount) {
                if (!$shopDiscount->canApply($this->shopBasket)) {
                    unset($shopDiscounts[$key]);
                }
            }
        }

        if ($shopDiscounts) {
            $price = (int)$this->shopBasket->price;
            $discountNames = [];
            $discountValue = 0;

            foreach ($shopDiscounts as $shopDiscount) {
                $discountPrice = 0;
                // процентные скидки
                if ($shopDiscount->value_type == ShopDiscount::VALUE_TYPE_P) {
                    $this->calcLookBookDiscount($shopDiscount);
                    if ((int)$shopDiscount->value > 0 ) {
                        $discountPrice = $this->calcPercentDiscount($shopDiscount, $price, $discountValue);
                    }
                }
                // лестница скидок
                elseif ($shopDiscount->value_type == ShopDiscount::VALUE_TYPE_LADDER) {
                    $discountPrice = $this->calcLadderDiscount($shopDiscount, $price, $discountValue);
                }

                if($discountPrice) $discountNames[] = $shopDiscount->name;

                //Нужно остановится и не применять другие скидки
                if ($shopDiscount->last_discount === Cms::BOOL_Y) {
                    break;
                }

            }

            $this->shopBasket->discount_name = implode(" + ", $discountNames);
        }
        return true;
    }

    /**
     * Установка стоимости подарка
     */
    protected function calcGiftDiscount()
    {
        $this->shopBasket->discount_price = 0;
        $this->shopBasket->discount_value = "";
        $this->shopBasket->price = 0;
        $this->shopBasket->quantity = 1;

        return true;
    }

    /**
     * Считает скидку товара по алгоритму процентной акции
     * @param ShopDiscount $shopDiscount
     * @param number       $price
     * @param number       $discountValue
     *
     * @return number $discountPrice
     */
    protected function calcPercentDiscount(ShopDiscount $shopDiscount, $price, &$discountValue)
    {
        $percent = $shopDiscount->value / 100;
        $discountValue += $percent;
        $discountPrice = intval($price * $percent);
        // ограничение на макс. скидку
        if($shopDiscount->max_discount > 0 && $discountPrice > $shopDiscount->max_discount) $discountPrice = $shopDiscount->max_discount;

        $this->shopBasket->price -= $discountPrice;
        $this->shopBasket->discount_price += $discountPrice;
        $this->shopBasket->discount_value = \Yii::$app->formatter->asPercent($discountValue);

        return $discountPrice;
    }

    /**
     * Считает скидку товара по алгоритму лестница скидок
     * @param ShopDiscount $shopDiscount
     * @param number       $price
     * @param number       $discountValue
     *
     * @return number $discountPrice
     */
    protected function calcLadderDiscount(ShopDiscount $shopDiscount, $price, &$discountValue)
    {
        $ladderLogic = $this->getLadderLogic($shopDiscount, $price);
        if(!$ladderLogic) return 0;

        $discountPrice = 0;
        if ($ladderLogic->discount_type == SsShopDiscountLogic::DISCOUNT_TYPE_PERCENT) {
            $percent = $ladderLogic->discount_value / 100;
            $discountValue += $percent;
            $discountPrice = intval($price * $percent);

            $this->shopBasket->discount_value = \Yii::$app->formatter->asPercent($discountValue);
        }
        elseif ($ladderLogic->discount_type == SsShopDiscountLogic::DISCOUNT_TYPE_FIXED) {
            $discountPrice = $ladderLogic->discount_value;
            $discountValue += (int)$discountPrice;

            $money = Money::fromString((string) $discountPrice, $shopDiscount->currency_code);
            $this->shopBasket->discount_value = \Yii::$app->money->intlFormatter()->format($money);
        }

        // ограничение на макс. скидку
        if($shopDiscount->max_discount > 0 && $discountPrice > $shopDiscount->max_discount) $discountPrice = $shopDiscount->max_discount;

        $this->shopBasket->price -= $discountPrice;
        $this->shopBasket->discount_price += $discountPrice;

        return $discountPrice;
    }

    /**
     * Получает нужную логику для обсчета лестницы скидок
     *
     * @param ShopDiscount $shopDiscount
     * @param              $price
     *
     * @return SsShopDiscountLogic|null
     */
    protected function getLadderLogic(ShopDiscount $shopDiscount, $price)
    {
        /** @var SsShopDiscountLogic[] $ladderLogics */
        $ladderLogics = $shopDiscount->getShopDiscountLogics()->all();

        foreach ($ladderLogics as $ladderLogic) {
            // фиксированная цена товара
            if ($ladderLogic->logic_type == SsShopDiscountLogic::LOGIC_TYPE_FIXED) {
                // TODO: играет ли роль кол-во позиций товара? Если да, то надо учесть тут
                if ($price < $ladderLogic->value) {
                    continue;
                }
            } // кол-во товара
            elseif ($ladderLogic->logic_type == SsShopDiscountLogic::LOGIC_TYPE_QUANTITY) {
                if ($this->shopBasket->quantity < $ladderLogic->value) {
                    continue;
                }
            }
            // остальные пропускаем
            else {
                continue;
            }

            return $ladderLogic;
        }
        return null;
    }

    /**
     * Вычисляет динамический процент скидки для акций с лукбуком
     * @param ShopDiscount $shopDiscount
     */
    protected function calcLookBookDiscount(ShopDiscount $shopDiscount)
    {
        // смотрим конфигурации акции, ищем лукбук
        $isLookBookDiscount = array_filter($shopDiscount->configurations, function ($item) {
            return $item->entity->class == Entity::LOOKBOOK_ENTITY;
        });

        if (!$isLookBookDiscount) {
            return;
        }

        // смотрим, был ли добавлен продукт в корзину из лукбука
        $lookbookProperty = $this->shopBasket->getShopBasketProps()->andWhere(['code' => ShopBasket::LOOKBOOK_CODE])->one();
        if (!$lookbookProperty) {
            return;
        }

        // проверяем id lookbook'a
        $lookbookId = $lookbookProperty->value;
        if (empty($lookbookId) || !is_numeric($lookbookId)) {
            return;
        }

        // ищем лукбук
        $lookbook = \common\lists\Contents::getContentElementById($lookbookId);
        if (!$lookbook) {
            return;
        }

        // ищем скидку
        $lookbookDiscountValue = (int)$lookbook->relatedPropertiesModel->getSmartAttribute('discount');
        $shopDiscount->value = $lookbookDiscountValue;
    }
}