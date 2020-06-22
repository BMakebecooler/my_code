<?php

use yii\db\Migration;

class m170411_092047_create_table_air_block extends Migration
{

    private $tableName = "{{%ss_mediaplan_air_blocks}}";


    public function safeUp()
    {

        $this->execute("ALTER TABLE `ss_mediaplan_schedule_items` RENAME TO `ss_mediaplan_air_day_product_time`;"); //Меняем название старой таблицы

        $tableExist = $this->db->getTableSchema($this->tableName, true);

        if ($tableExist) {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'created_at' => 'timestamp NOT NULL', //->defaultValue(['expression'=>'CURRENT_TIMESTAMP']),
            'block_id' => $this->integer()->notNull(),
            'block_repeat_id' => $this->integer(),
            'begin_datetime' => $this->integer()->notNull(),
            'end_datetime' => $this->integer()->notNull(),
            'duration' => $this->integer(),
            'section_id' => $this->integer()->notNull(),
            'bitrix_section_id' => $this->integer()->notNull(),
            'type' => $this->string(50),
            'title' => $this->string(100),
            'subcategory' => $this->string(100),
            'section_name' => $this->string(100),
            'section_color' => $this->string(100),
        ], $tableOptions);

        $this->createIndex('section_id', $this->tableName, 'section_id');

        $this->createIndex('bitrix_section_id', $this->tableName, 'bitrix_section_id');
        $this->createIndex('type', $this->tableName, 'type');

        $this->createIndex('begin_datetime', $this->tableName, 'begin_datetime');
        $this->createIndex('end_datetime', $this->tableName, 'end_datetime');

        $this->execute("ALTER TABLE `ss_mediaplan_air_blocks` CHANGE `created_at` `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP");
        $this->execute("ALTER TABLE `ss_mediaplan_air_blocks` COMMENT = 'блоки прямых эфиров и повторов за указанный день';");
    }


    public function safeDown()
    {

        $this->execute("ALTER TABLE `ss_mediaplan_air_day_product_time` RENAME TO `ss_mediaplan_schedule_items`;");

        $this->dropTable($this->tableName);
    }

}
