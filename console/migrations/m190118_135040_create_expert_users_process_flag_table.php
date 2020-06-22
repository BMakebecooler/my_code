<?php

use yii\db\Migration;

/**
 * Handles the creation of table `expert_users_process_flag`.
 */
class m190118_135040_create_expert_users_process_flag_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('expert_users_process_flag', [
            'id' => $this->primaryKey(),
            'update_time' => $this->timestamp(),
            'check_cnt' => $this->integer()
        ]);

        $update_time = time();
        $sql = "INSERT INTO expert_users_process_flag (update_time, check_cnt) VALUES ({$update_time}, 0)";
        $this->execute($sql);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('expert_users_process_flag');
    }
}
