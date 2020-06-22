<?php

use yii\db\Migration;

class m190514_140348_alter_table_shop_discount_coupon_null_shop_discount_id extends Migration
{
    private $tableName = "shop_discount_coupon";
    private $tableNameShopDiscount = "shop_discount";
    private $columnName = 'shop_discount_id';
    private $foreignKeyShopDiscount = 'shop_discount_coupon__shop_discount_id';

    public function safeUp()
    {
        echo "Alter column '{$this->columnName}' in table '{$this->tableName}'" . PHP_EOL;
        $this->alterColumn($this->tableName, $this->columnName, $this->integer()->null());
    }

    public function safeDown()
    {
//        echo "Can't revert alter column '{$this->columnName}' in table '{$this->tableName}' because foreign keys exists" . PHP_EOL;
//        return false;

        //Из-за внешних ключей ругается
        echo "Alter column '{$this->columnName}' in table '{$this->tableName}'. Set NOT NULL" . PHP_EOL;

        \Yii::$app->db->schema->refreshTableSchema($this->tableName);
        $tableSchema = Yii::$app->db->schema->getTableSchema($this->tableName, true);

        if (array_key_exists($this->foreignKeyShopDiscount, $tableSchema->foreignKeys)){
            echo "Drop foreignKey '{$this->foreignKeyShopDiscount}" . PHP_EOL;
            $this->dropForeignKey($this->foreignKeyShopDiscount, $this->tableName);
        }

        echo "Altering..." . PHP_EOL;

        $this->alterColumn($this->tableName, $this->columnName, $this->integer()->notNull());

        echo "Add foreignKey '{$this->foreignKeyShopDiscount}" . PHP_EOL;

        $this->addForeignKey(
            $this->foreignKeyShopDiscount,
            $this->tableName,
            $this->columnName,
            $this->tableNameShopDiscount,
            'id',
            'CASCADE',
            'CASCADE'
        );
        echo 'Done' . PHP_EOL;
    }
}
