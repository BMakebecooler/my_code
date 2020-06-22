<?php
namespace common\widgets\checkout;

use modules\shopandshow\models\shop\ShopContentElement;
use modules\shopandshow\models\shop\ShopFuser;
use skeeks\cms\base\WidgetRenderable;

class DeliveryInfo extends WidgetRenderable
{
    public $viewFile = '@template/widgets/Delivery/product';
    /** @var ShopContentElement */
    public $model;

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->view->registerJs(<<<JS
        $(function() {
            $('#productDeliveryLink').on('click', function() {
                sx.Observer.trigger('product_delivery_widget_click');
            })
        });
JS
        );
        return $this->render($this->viewFile);
    }

    /**
     * выводит отформатированную дату доставки
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function getDeliveryDate()
    {
        return \Yii::$app->formatter->asDate(time() + $this->getDeliveryDays() * DAYS_1);
    }

    /**
     * считает кол-во дней на доставку
     * @return int
     */
    public function getDeliveryDays()
    {
        $isFastDelivery = $this->model->isFastDelivery();
        return $isFastDelivery ? ShopFuser::DELIVERY_DAYS_FAST : ShopFuser::DELIVERY_DAYS_LONG;
    }

    /**
     * Стоимость доставки. Для ускорения рассчета цифры по доставке захардкодены
     * @return string
     */
    public function getDeliveryPrice()
    {
        $freeDeliverySum = \common\helpers\Promo::isLowSeasonPeriod() ? 4990 : 5990;

        if ($this->model->price->price >= $freeDeliverySum) {
            return 'бесплатная доставка';
        }
        return 'от 126 руб, точную стоимость сообщит оператор';
    }
}