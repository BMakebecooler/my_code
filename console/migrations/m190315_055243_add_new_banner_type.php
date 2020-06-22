<?php

use yii\db\Migration;

class m190315_055243_add_new_banner_type extends Migration
{
    private $tableName = '{{%ss_shares_type}}';

    public function safeUp()
    {
        $sql = "INSERT INTO ss_shares_type (code, description) VALUES ('MAIN_WIDE_MOBILE', 'Широкий баннер для мобильной версии')";
        $this->execute($sql);
    }

    public function down()
    {
        $sql = "DELETE FROM ss_shares_type WHERE  code = 'MAIN_WIDE_MOBILE'";
        $this->execute($sql);
    }
}
