<?php

use yii\db\Migration;

/**
 * Class m180322_133335_alter_table_ss_mail_dispatch
 */
class m180322_133335_alter_table_ss_mail_dispatch extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%ss_mail_dispatch}}', 'segments', $this->string(512)->null()->after('to'));
        $this->addColumn('{{%ss_mail_dispatch}}', 'message', $this->text()->null());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        //echo "m180322_133335_alter_table_ss_mail_dispatch cannot be reverted.\n";
        $this->dropColumn('{{%ss_mail_dispatch}}', 'segments');
        $this->dropColumn('{{%ss_mail_dispatch}}', 'message');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180322_133335_alter_table_ss_mail_dispatch cannot be reverted.\n";

        return false;
    }
    */
}
