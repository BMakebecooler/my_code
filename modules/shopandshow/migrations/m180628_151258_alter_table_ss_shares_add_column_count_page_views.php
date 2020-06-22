<?php

use yii\db\Migration;

class m180628_151258_alter_table_ss_shares_add_column_count_page_views extends Migration
{
    private $table_name = '{{%ss_shares}}';

    public function safeUp()
    {
        $this->addColumn($this->table_name, 'count_page_views', $this->integer()->unsigned()->after('promo_type')->defaultValue(0));
    }

    public function safeDown()
    {
        $this->dropColumn($this->table_name, 'count_page_views');
    }
}
