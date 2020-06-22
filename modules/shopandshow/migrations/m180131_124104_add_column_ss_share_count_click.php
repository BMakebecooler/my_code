<?php

use yii\db\Migration;

/**
 * Class m180131_124104_add_column_ss_share_count_click
 */
class m180131_124104_add_column_ss_share_count_click extends Migration
{

    private $tableName = 'ss_shares';

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn($this->tableName, 'count_click', $this->integer(11)->defaultValue(0)->unsigned()->after('promo_type'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn($this->tableName, 'count_click');
    }
}
