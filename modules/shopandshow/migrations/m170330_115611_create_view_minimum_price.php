<?php

use yii\db\Migration;

class m170330_115611_create_view_minimum_price extends Migration
{
    public function up()
    {

        return true;


        $createView = <<<SQL
CREATE TEMPORARY TABLE minimum_prices 
(
INDEX minimum_prices_product_id (product_id),
INDEX minimum_prices_type_price_id (type_price_id)
)

select `pr`.`product_id` AS `product_id`,`pr`.`type_price_id` AS `type_price_id`, `pr`.`price` AS `min_price`, count(0) AS `count_prices` from (

`shop_product_price` `pr` 

left join `cms_content_element` `cce` on((`cce`.`parent_content_element_id` = `pr`.`product_id`))) where (`pr`.`price` > 0) group by `pr`.`product_id`,`pr`.`price` 
having min(`pr`.`price`) 
order by max(`pr`.`created_at`) desc
SQL;


        $this->execute($createView);

    }

    public function down()
    {

        return true;

        echo "m170330_115611_create_view_minimum_price cannot be reverted.\n";

        return false;
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
