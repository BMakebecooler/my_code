<?php

use yii\db\Migration;

class m170629_090112_create_table_shop_discount_configuration extends Migration
{

    private $tableNameConfiguration = "{{%shop_discount_configuration}}";
    private $tableNameEntity = "{{%shop_discount_entity}}";
    private $tableNameValues = "{{%shop_discount_values}}";

    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        // типы акций и условия
        $this->createTable($this->tableNameEntity, [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'class' => $this->string()->notNull(),  // класс, реализующий логику типа акции/условия
        ], $tableOptions);

        // кокретные условия и типы акции, используемые скидкой
        $this->createTable($this->tableNameConfiguration, [
            'id' => $this->primaryKey(),
            'shop_discount_id' => $this->integer()->notNull(),
            'shop_discount_entity_id' => $this->integer()->notNull(),
        ], $tableOptions);

        // кокретные значения, выбранные в условиях
        $this->createTable($this->tableNameValues, [
            'id' => $this->primaryKey(),
            'shop_discount_configuration_id' => $this->integer()->notNull(),
            'value' => $this->string()->notNull(),
        ], $tableOptions);

        $this->createIndex('I_shop_discount_id', $this->tableNameConfiguration, 'shop_discount_id');
        $this->createIndex('I_shop_discount_entity_id', $this->tableNameConfiguration, 'shop_discount_entity_id');
        $this->createIndex('I_shop_discount_configuration_id', $this->tableNameValues, 'shop_discount_configuration_id');

        $this->addForeignKey(
            'shop_discount_configuration_shop_discount',
            $this->tableNameConfiguration, 'shop_discount_id',
            '{{%shop_discount}}', 'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'shop_discount_configuration_shop_discount_entity',
            $this->tableNameConfiguration, 'shop_discount_entity_id',
            $this->tableNameEntity, 'id'
        );

        $this->addForeignKey(
            'shop_discount_values_shop_discount_configuration',
            $this->tableNameValues, 'shop_discount_configuration_id',
            $this->tableNameConfiguration, 'id',
            'CASCADE'
        );

        $this->batchInsert(
            $this->tableNameEntity,
            ['class', 'name'],
            $this->getConditionRules()
        );
    }

    public function safeDown()
    {
        $this->dropTable($this->tableNameValues);
        $this->dropTable($this->tableNameConfiguration);
        $this->dropTable($this->tableNameEntity);
    }

    private function getConditionRules()
    {
        return [
            [
                'class' => 'EmptyCondition',
                'name' => 'Без условий',
            ],
            [
                'class' => 'ForBrands',
                'name' => 'Бренды',
            ],
            [
                'class' => 'ForCount',
                'name' => 'Количество товаров в корзине',
            ],
            [
                'class' => 'ForLots',
                'name' => 'Лоты',
            ],
            [
                'class' => 'ForPromoCode',
                'name' => 'Промо код',
            ],
            [
                'class' => 'ForQuantity',
                'name' => 'Количество лотов',
            ],
            [
                'class' => 'ForSection',
                'name' => 'По разделу',
            ],
            [
                'class' => 'ForSum',
                'name' => 'Сумма товаров',
            ],
            [
                'class' => 'ForSumRange',
                'name' => 'Вилка суммы',
            ],
            [
                'class' => 'ForUsers',
                'name' => 'Юзеры',
            ],
            [
                'class' => 'ForCTS',
                'name' => 'ЦТС',
            ],
            [
                'class' => 'ForJew',
                'name' => 'Только Ювелирка'
            ],
            [
                'class' => 'ForSales',
                'name' => 'Товары из распродажи'
            ],
        ];
    }
}
