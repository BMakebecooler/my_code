<?php

use yii\db\Migration;

class m190311_122723_new_baner_type extends Migration
{
    private $tableName = '{{%ss_shares_type}}';

    public function safeUp()
    {
        $sql = "INSERT INTO ss_shares_type (code, description) VALUES ('MAIN_CTS_MOBILE', 'Главный банер ЦТС для мобильной версии')";
        $this->execute($sql);
    }

    public function down()
    {
        $sql = "DELETE FROM ss_shares_type WHERE  code = 'MAIN_CTS_MOBILE'";
        $this->execute($sql);
    }
}
