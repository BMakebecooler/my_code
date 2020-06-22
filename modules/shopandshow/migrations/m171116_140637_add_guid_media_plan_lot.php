<?php

use yii\db\Migration;

class m171116_140637_add_guid_media_plan_lot extends Migration
{

    private $tableName = 'ss_mediaplan_air_day_product_time';

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn($this->tableName, 'lot_guid', $this->string(64));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn($this->tableName, 'lot_guid');
    }
}
