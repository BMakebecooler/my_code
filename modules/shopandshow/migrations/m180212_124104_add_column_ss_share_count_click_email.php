<?php

use yii\db\Migration;

/**
 * Class m180212_124104_add_column_ss_share_count_click_email
 */
class m180212_124104_add_column_ss_share_count_click_email extends Migration
{

    private $tableName = 'ss_shares';

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn($this->tableName, 'count_click_email', $this->integer(11)->defaultValue(0)->unsigned()->after('count_click'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn($this->tableName, 'count_click_email');
    }
}
