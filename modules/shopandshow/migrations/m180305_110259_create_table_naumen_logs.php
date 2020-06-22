<?php

use yii\db\Migration;

class m180305_110259_create_table_naumen_logs extends Migration
{

    private $preorders_table = 'ss_preorders_logs';

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable($this->preorders_table, [
            'id' => $this->primaryKey(),
            'created_at' => $this->timestamp()->notNull(),
            'phone' => $this->string(64),
            'products' => $this->text(),
        ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable($this->preorders_table);
    }
}
