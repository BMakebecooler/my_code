<?php

use yii\db\Migration;

class m170807_155233_alter_table_cms_content_element_count_children extends Migration
{
    public function safeUp()
    {
        $this->addColumn('cms_content_element', 'count_children', $this->integer(3)->defaultValue(0));

        $this->execute(
            <<<SQL
UPDATE cms_content_element AS cce, (
    SELECT parent_content_element_id, COUNT(*) AS cnt 
    FROM cms_content_element
    GROUP BY parent_content_element_id
) AS child 
  SET cce.count_children = child.cnt 
WHERE cce.id = child.parent_content_element_id AND cce.content_id = 2;
SQL
);
    }

    public function safeDown()
    {
        $this->dropColumn('cms_content_element', 'count_children');
    }
}
