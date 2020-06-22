<?php

use yii\db\Migration;

class m190319_103619_alter_table_cms_content_element_add_lot_props extends Migration
{
    private $tableName = '{{%cms_content_element}}';

    public function getColumns()
    {

        return [
            'new_guid' => $this->string(),
            'new_lot_num' => $this->string(),
            'new_lot_name' => $this->string(),
            'new_characteristics' => $this->text(),
            'new_technical_details' => $this->text(),
            'new_product_kit' => $this->text(),
            'new_advantages' => $this->text(),
            'new_advantages_addons' => $this->text(),
            'new_not_public' => $this->boolean(),
            'new_quantity' => $this->integer(),
            'new_rest' => $this->boolean(),
            'new_price_active' => $this->boolean(),
            'new_price' => $this->decimal(10, 2),
            'new_price_old' => $this->decimal(10, 2),
            'new_discount_percent' => $this->decimal(10, 2),
            'new_brand_id' => $this->integer(),
            'new_season_id' => $this->integer(),
            'new_rating' => $this->decimal(10, 2),
        ];
    }

    public function up()
    {
        echo "Добавляю свойства для лота..." . PHP_EOL;

        foreach ($this->getColumns() as $column => $item) {
            try {
                $this->addColumn($this->tableName, $column, $item);
            } catch (Exception $e) {
                echo $e->getMessage() . PHP_EOL;
            }
        }
        echo "Готово" . PHP_EOL;
    }

    public function down()
    {

        echo "Удаляю свойства для лота..." . PHP_EOL;

        foreach ($this->getColumns() as $column => $column) {
            try{
            $this->dropColumn($this->tableName, $column);
            }catch (Exception $e){
                echo $e->getMessage() . PHP_EOL;
            }
        }
        echo "Готово" . PHP_EOL;
    }
}
