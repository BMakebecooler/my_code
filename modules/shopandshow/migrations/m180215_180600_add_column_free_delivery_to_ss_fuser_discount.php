<?php

use yii\db\Migration;

/**
 * Class m180213_144700_add_column_source_to_shop_order
 */
class m180215_180600_add_column_free_delivery_to_ss_fuser_discount extends Migration
{

    private $tableName = '{{%ss_shop_fuser_discount}}';

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn($this->tableName, 'free_delivery', $this->char(1)->defaultValue('N'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn($this->tableName, 'free_delivery');
    }
}
