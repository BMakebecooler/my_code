<?php

use yii\db\Migration;

class m170626_142542_alter_table_media_plan_time extends Migration
{
    public function safeUp()
    {
        $this->addColumn('ss_mediaplan_air_day_product_time', 'block_id', $this->integer()->unsigned());
    }

    public function safeDown()
    {
        $this->dropColumn('ss_mediaplan_air_day_product_time', 'block_id');
    }
}
