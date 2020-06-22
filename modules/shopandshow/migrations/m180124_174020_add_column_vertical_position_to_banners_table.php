<?php

use yii\db\Migration;

/**
 * Class m180124_174020_add_column_vertical_position_to_banners_table
 */
class m180124_174020_add_column_vertical_position_to_banners_table extends Migration
{
    private $tableName = 'ss_shares';

    private $columnName = 'vertical_position';

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(
            $this->tableName,
            $this->columnName,
            $this->integer()
                ->after('image_id')
                ->defaultValue(500)
                ->comment('Defines target block if the page is set to multiple blocks with one type')
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn($this->tableName, $this->columnName);
    }

}
