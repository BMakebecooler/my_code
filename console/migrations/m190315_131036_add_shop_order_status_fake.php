<?php

use modules\shopandshow\models\shop\ShopOrderStatus;
use yii\db\Migration;

class m190315_131036_add_shop_order_status_fake extends Migration
{
    private $orderStatusCode = 'Z';

    public function safeUp()
    {
        echo "Добавляю статус заказа '{$this->orderStatusCode}'" . PHP_EOL;
        $orderStatus = ShopOrderStatus::findOne(['code' => $this->orderStatusCode]);

        if (!$orderStatus){

            $orderStatus = new ShopOrderStatus([
                'code'          => $this->orderStatusCode,
                'name'          => 'FAKE',
                'description'   => 'Fake status',
                'priority'      => 9999,
                'color'         => '#d5a6bd',
            ]);

            if (!$orderStatus->save()){
                var_dump($orderStatus->getErrors());
            }else{
                echo "Статус заказа добавлен." . PHP_EOL;
            }
        }else{
            echo 'Статус уже существует.' . PHP_EOL;
        }
    }

    public function safeDown()
    {
        echo "Удаляю статус заказа '{$this->orderStatusCode}'" . PHP_EOL;
        if ($orderStatus = ShopOrderStatus::findOne(['code' => $this->orderStatusCode])){
            //Проверяем наличие заказов с этим статусом, если найдутся - удалить неполучится

            $ordersWithStatus = \modules\shopandshow\models\shop\ShopOrder::findOne(['status_code' => $this->orderStatusCode]);

            if (!$ordersWithStatus){
                echo 'Статус удален успешно.' . PHP_EOL;
                $orderStatus->delete();
            }else{
                echo "Не могу удалить статус, есть заказы с этим статусом." . PHP_EOL;
            }
        }
    }
}
