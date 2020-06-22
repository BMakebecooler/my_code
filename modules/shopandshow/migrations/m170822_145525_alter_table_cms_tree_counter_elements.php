<?php

use yii\db\Migration;

class m170822_145525_alter_table_cms_tree_counter_elements extends Migration
{
    public function safeUp()
    {
        $this->addColumn('cms_tree', 'count_content_element', $this->integer()->defaultValue(0));

        $this->execute(
            <<<SQL
UPDATE cms_tree AS tree, (
    SELECT el.tree_id, COUNT(*) AS cnt 
    FROM cms_content_element AS el
    INNER JOIN shop_product AS shop ON shop.id = el.id
    WHERE el.content_id = 2 AND el.tree_id IS NOT NULL AND el.active = 'Y' AND shop.quantity > 0
    GROUP BY el.tree_id
) AS child 
  SET tree.count_content_element = child.cnt 
WHERE tree.id = child.tree_id
SQL
        );
    }

    public function safeDown()
    {
        $this->dropColumn('cms_tree', 'count_content_element');
    }
}
