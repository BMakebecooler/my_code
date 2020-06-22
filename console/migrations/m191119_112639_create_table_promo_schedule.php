<?php

use yii\db\Migration;

class m191119_112639_create_table_promo_schedule extends Migration
{
    private $tableName = '{{%promo_schedule}}';
    private $indexActive = 'i_active';
    private $indexDateFrom = 'i_date_from';
    private $indexDateTo = 'i_date_to';

    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable($this->tableName, [
                'id' => $this->primaryKey(),
                'created_at' => $this->integer()->comment('Создано'),
                'updated_at' => $this->integer()->comment('Обновлено'),
                'active' => $this->boolean()->defaultValue(1)->comment('Активен'),
                'date_from' => $this->integer()->comment('Начало активности'),
                'date_to' => $this->integer()->comment('Конец активности'),
                'coupon' => $this->string(255)->comment('Код купона'),
                'discount_percent' => $this->integer(3)->comment('Процент скидки'),
                'discount_on_text' => $this->string(255)->comment('Скидка на ...'),
                'url' => $this->text()->comment('Ссылка на сборку'),
                'is_main' => $this->boolean()->defaultValue(0)->comment('Для поля купон'),
            ],
            $tableOptions
        );

        $this->createIndex($this->indexActive, $this->tableName, 'active');
        $this->createIndex($this->indexDateFrom, $this->tableName, 'date_from');
        $this->createIndex($this->indexDateTo, $this->tableName, 'date_to');

        return;
    }

    public function safeDown()
    {
        $this->dropIndex($this->indexActive, $this->tableName);
        $this->dropIndex($this->indexDateFrom, $this->tableName);
        $this->dropIndex($this->indexDateTo, $this->tableName);

        $this->dropTable($this->tableName);

        return;
    }
}
