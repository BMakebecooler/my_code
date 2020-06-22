<?php

use yii\db\Migration;

class m190404_125353_insert_banner_type extends Migration
{
    const TABLE_BANNER = '{{%ss_shares_type}}';

    public function safeUp()
    {


        $this->insert(self::TABLE_BANNER,['code' => 'ear', 'description' =>'Баннер ухо']);
        $this->insert(self::TABLE_BANNER,['code' => 'popup', 'description'  =>'Баннер попап']);
        $this->insert(self::TABLE_BANNER,['code' => 'label', 'description' =>'Баннер название']);
    }

    public function safeDown()
    {
        $this->delete(self::TABLE_BANNER,['code' => ['ear','popup','label']]);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190404_125353_insert_banner_type cannot be reverted.\n";

        return false;
    }
    */
}
