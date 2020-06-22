<?php

use yii\db\Migration;

class m190405_115338_restore_db extends Migration
{
    public function safeUp()
    {


        $file = Yii::getAlias('@console/../data/backup.sql');


        $this->execute(file_get_contents($file));

    }

    public function safeDown()
    {
        echo "m190405_115338_restore_db cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190405_115338_restore_db cannot be reverted.\n";

        return false;
    }
    */
}
