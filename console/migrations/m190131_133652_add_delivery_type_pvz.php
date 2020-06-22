<?php

use modules\shopandshow\models\shop\ShopFuser;
use modules\shopandshow\models\shop\ShopOrder;
use skeeks\cms\shop\models\ShopDelivery;
use yii\db\Migration;

class m190131_133652_add_delivery_type_pvz extends Migration
{
    private $shopDeliveryId = 9; //При вставке в админке установился такой ИД, используем его и тут
    private $columnName = 'pvz_data';

    public function safeUp()
    {
        //Добавляем тип доставки
        echo "Добавляю тип доставки." . PHP_EOL;

        echo "Проверяю наличие добавляемого типа доставки" . PHP_EOL;

        $shopDelivery = ShopDelivery::findOne($this->shopDeliveryId);

        if (!$shopDelivery){
            echo "Не найдено. Добавляю." . PHP_EOL;
            $shopDelivery = new ShopDelivery([
                'id'    => $this->shopDeliveryId,
                'name'  => 'Постоматы Boxberry',
                'site_id'   => \Yii::$app->cms->site->id
            ]);

            if (!$shopDelivery->save()){
                echo "Ошибки при сохранеии: " . PHP_EOL;
                var_dump($shopDelivery->getErrors());
            }
        }else{
            echo "Тип доставки найден. Пропускаю." . PHP_EOL;
        }

        //Добавляем данные ПВЗ в фузера
        echo "Добавляю колонку '{$this->columnName}'" . PHP_EOL;
        echo "-- в таблицу " . ShopFuser::tableName() . PHP_EOL;

        try {
            $this->addColumn(ShopFuser::tableName(), $this->columnName, $this->text()->after('delivery_id'));
        } catch (Exception $e) {
            echo $e->getMessage();
        }


        //Добавляем данные ПВЗ в заказ
        echo "-- в таблицу " . ShopOrder::tableName() . PHP_EOL;

        try {
            $this->addColumn(ShopOrder::tableName(), $this->columnName, $this->text()->after('delivery_id'));
        } catch (Exception $e) {
            echo $e->getMessage();
        }

    }

    public function safeDown()
    {

        try {
            $this->dropColumn(ShopOrder::tableName(), $this->columnName);
            $this->dropColumn(ShopFuser::tableName(), $this->columnName);
        } catch (Exception $e) {
            echo $e->getMessage();
        }


        $shopDelivery = ShopDelivery::findOne($this->shopDeliveryId);
        if ($shopDelivery){
            $shopDelivery->delete();
        }

    }
}
