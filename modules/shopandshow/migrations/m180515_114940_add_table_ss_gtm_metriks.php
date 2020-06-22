<?php

use yii\db\Migration;

class m180515_114940_add_table_ss_gtm_metriks extends Migration
{

    private $table_name = '{{%ss_gt_metriks}}';

    public function safeUp()
    {
        $tableExist = $this->db->getTableSchema($this->table_name, true);

        if ($tableExist) {
            return true;
        }

        $tableOptions = null;

        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable($this->table_name, [
            'id' => $this->primaryKey(),

            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),

            'gt_onload_time' => $this->integer(),
            'gt_page_elements' => $this->integer(),
            'gt_dom_content_loaded_time' => $this->integer(),
            'gt_dom_interactive_time' => $this->integer(),
            'gt_page_bytes' => $this->integer(),
            'gt_page_load_time' => $this->integer(),
            'gt_fully_loaded_time' => $this->integer(),
            'gt_html_load_time' => $this->integer(),
            'gt_rum_speed_index' => $this->integer(),
            'gt_yslow_score' => $this->integer(),
            'gt_pagespeed_score' => $this->integer(),
            'gt_backend_duration' => $this->integer(),

            'gt_id' => $this->string(50),
            'gt_report_url' => $this->string(255),
            'test_url' => $this->string(255),

        ], $tableOptions);

        $this->createIndex('I_gt_id', $this->table_name, 'gt_id');
    }

    public function safeDown()
    {
        $this->dropTable($this->table_name);
    }
}
