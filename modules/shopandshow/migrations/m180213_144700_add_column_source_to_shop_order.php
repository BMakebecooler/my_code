<?php

use yii\db\Migration;

/**
 * Class m180213_144700_add_column_source_to_shop_order
 */
class m180213_144700_add_column_source_to_shop_order extends Migration
{

    private $tableName = '{{%shop_order}}';

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn($this->tableName, 'source', $this->string(32)->after('last_send_queue_at'));
        $this->addColumn($this->tableName, 'source_detail', $this->string(32)->after('source'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn($this->tableName, 'source_detail');
        $this->dropColumn($this->tableName, 'source');
    }
}
