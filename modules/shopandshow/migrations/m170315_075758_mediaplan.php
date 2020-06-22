<?php
/**
 * php ./yii migrate/up --migrationPath=@modules/shopandshow/migrations
 */

use yii\db\Migration;

class m170315_075758_mediaplan extends Migration
{
    public function safeUp()
    {

        $tableExist = $this->db->getTableSchema("{{%ss_mediaplan_schedule_items}}", true);

        if ($tableExist) {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable("{{%ss_mediaplan_schedule_items}}", [
            'id' => $this->primaryKey(),
            'created_at' => 'timestamp NOT NULL', //->defaultValue(['expression'=>'CURRENT_TIMESTAMP']),
            'begin_datetime' => $this->integer()->notNull(),
            'end_datetime' => $this->integer()->notNull(),
            'lot_id' => $this->integer()->notNull(),
            'bitrix_section_id' => $this->integer()->notNull(),
            'section_id' => $this->integer()->notNull(),
            'section_name' => $this->string(100),
            'type' => $this->string(50),
        ], $tableOptions);

        $this->createIndex('section_id', '{{%ss_mediaplan_schedule_items}}', 'section_id');
        $this->createIndex('bitrix_section_id', '{{%ss_mediaplan_schedule_items}}', 'bitrix_section_id');
        $this->createIndex('type', '{{%ss_mediaplan_schedule_items}}', 'type');
        $this->createIndex('lot_id', '{{%ss_mediaplan_schedule_items}}', 'lot_id');
        $this->createIndex('begin_datetime', '{{%ss_mediaplan_schedule_items}}', 'begin_datetime');
        $this->createIndex('end_datetime', '{{%ss_mediaplan_schedule_items}}', 'end_datetime');

        $this->execute("ALTER TABLE `ss_mediaplan_schedule_items` CHANGE `created_at` `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP");

        $this->execute("ALTER TABLE {{%ss_mediaplan_schedule_items}} COMMENT = 'Сегодня в эфире';");

        return true;
    }


    public function safeDown()
    {
        $this->dropTable("{{%ss_mediaplan_schedule_items}}");
    }
}
