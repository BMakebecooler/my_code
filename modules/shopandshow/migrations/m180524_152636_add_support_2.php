<?php

use yii\db\Migration;

class m180524_152636_add_support_2 extends Migration
{


    private $table_name = 'ss_support_questions';
    private $table_name_dialog = 'ss_support_questions_dialog';
    private $table_name_faq = 'cms_content_element_faq';

    public function init()
    {
        parent::init();
    }

    public function safeUp()
    {

        $this->dropTable($this->table_name_dialog);
        $this->dropTable($this->table_name);


        $this->addColumn($this->table_name_faq, 'is_sms_notification', $this->smallInteger()->unsigned()->defaultValue(0));
        $this->addColumn($this->table_name_faq, 'fuser_id', $this->integer()->unsigned());
        $this->addColumn($this->table_name_faq, 'phone', $this->string(20));
        $this->addColumn($this->table_name_faq, 'type', $this->smallInteger()->unsigned()->defaultValue(1));

        $this->addColumn($this->table_name_faq, 'parent_id', $this->integer()->unsigned());

        return true;
    }

    public function safeDown()
    {


        return true;
    }
}
