<?php

use yii\db\Migration;

class m180328_094405_create_table_ss_shares_clicks extends Migration
{

    private $table_name = 'ss_shares_selling';

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable($this->table_name, [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'fuser_id' => $this->integer()->notNull(),
            'user_id' => $this->integer(),
            'share_id' => $this->integer()->notNull(),
            'status' => $this->smallInteger()->notNull()->defaultValue('1'),
        ], $tableOptions);

        $this->createIndex('I_ss_ss_shares_selling_fuser_id', $this->table_name, 'fuser_id');

        $this->addForeignKey('FK_share_ss_shares_selling', $this->table_name, 'share_id', 'ss_shares', 'id', 'CASCADE', 'CASCADE');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable($this->table_name);
    }
}
