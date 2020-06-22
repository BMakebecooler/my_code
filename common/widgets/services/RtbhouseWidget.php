<?php
namespace common\widgets\services;

use yii\base\Widget;

class RtbhouseWidget extends Widget
{
    public $clientCode = 'pr_5ddJGmvQU06f3GFsdpWE';

    public static $isSet = false;

    /**
     * @var string
     */
    public $item = null;

    /**
     * @var array
     */
    public $items = null;

    /**
     * @var int
     */
    public $orderId = null;

    /**
     * @var string
     */
    public $revenue = null;

    /**
     * @var string
     */
    public $event = null;

    public function run()
    {
        $result = '';

        switch ($this->event) {
            case 'viewHome':
                $result = <<<RES
<iframe src="https://creativecdn.com/tags?id={$this->clientCode}_home"
width="1" height="1" scrolling="no" frameBorder="0" style="display: none;"></iframe>
RES;

                break;
            case 'viewCategory':
                $result = <<<RES
<iframe src="https://creativecdn.com/tags?id={$this->clientCode}_category2_{$this->item}"
width="1" height="1" scrolling="no" frameBorder="0" style="display: none;"></iframe>
RES;
                break;
            case 'viewItem':
                $result = <<<RES
<iframe src="https://creativecdn.com/tags?id={$this->clientCode}_offer_{$this->item}" 
width="1" height="1" scrolling="no" frameBorder="0" style="display: none;"></iframe>
RES;
                break;
            case 'viewSearchResults':
                //listing_{ID},{ID},{ID} - список не более 5 первых товаров выводимых в результате поиска
                $items = $this->items ? implode(',', array_slice($this->items, 0, 5)) : '';
                $result = <<<RES
<iframe
src="https://creativecdn.com/tags?id={$this->clientCode}_listing_{$items}"
width="1" height="1" scrolling="no" frameBorder="0" style="display: none;"></iframe>
RES;
                break;
            case 'viewCart':
                //basketstatus_{ID},{ID},{ID} -
                //В  место макроса {ID} следует передавать только ID товара, который в  данный момент добавили в  корзину
                //(независимо от количества).
                //ВНИМАНИЕ: Код корзины следует передавать только, когда корзина не пуста.
                $items = implode(',', $this->items);
                $result = <<<RES
<iframe src="https://creativecdn.com/tags?id={$this->clientCode}_basketstatus_{$items}"
width="1" height="1" scrolling="no" frameBorder="0" style="display: none;"></iframe>
RES;
                break;
            case 'orderCheckout': //Пока такой страницы у нас нет
                $result = <<<RES
<iframe src="https://creativecdn.com/tags?id={$this->clientCode}_startorder"
width="1" height="1" scrolling="no" frameBorder="0" style="display: none;"></iframe>
RES;
                break;
            case 'orderFinish':
                /* ... orderstatus2_{VALUE}_{ORDERID}_{ID},{ID},{ID}&amp;cd=default ...
                В  пределах макроса {VALUE} должна указываться стоимость заказа. Это значение должно быть сформатировано
                исключительно с десятичным сепаратором (точка или запятая), без сепаратора тысяч. В пределах макроса {ORDERID}
                должно быть вставлено ID заказа. В  пределах очередных макросов {ID} должны быть вставлены ID товаров
                находящихся в данный момент в корзине, в соответствии с ID этих товаров в feed'е.
                */
                $items = implode(',', $this->items);
                $result = <<<RES
<iframe src="https://creativecdn.com/tags?id={$this->clientCode}_orderstatus2_{$this->revenue}_{$this->orderId}_{$items}&amp;cd=default"
width="1" height="1" scrolling="no" frameBorder="0" style="display: none;"></iframe>
RES;
                break;
            case 'viewOthers':
            default:
                if (!self::$isSet) {
                    $result = <<<RES
<iframe src="https://creativecdn.com/tags?id={$this->clientCode}&amp;ncm=1"
width="1" height="1" scrolling="no" frameBorder="0" style="display: none;"></iframe>
RES;
                }
                break;
        }

        self::$isSet = true;

        return $result;
    }
}