<?php

use common\models\generated\models\CmsUserEmail;
use yii\db\Migration;

class m190610_110337_drop_unique_for_cms_user_email extends Migration
{
    public function safeUp()
    {
        $this->dropIndex('value', CmsUserEmail::tableName());
        $this->alterColumn(\common\models\generated\models\CmsUserEmail::tableName(),'value', $this->string());

    }

    public function safeDown()
    {
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190610_110337_drop_unique_for_cms_user_email cannot be reverted.\n";

        return false;
    }
    */
}
