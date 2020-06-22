<?php

use yii\db\Migration;

/**
 * Class m180110_080131_add_column_to_queue_log
 */
class m180110_080131_add_column_to_queue_log extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('queue_log', 'error', $this->text());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('queue_log', 'error');
    }
}
