<?php

use yii\db\Migration;
use \common\models\Callback;

class m190408_200941_alter_table_callback_table extends Migration
{
    public function safeUp()
    {
        $this->alterColumn(Callback::tableName(), 'phone', $this->string()->notNull());
    }

    public function safeDown()
    {
        $this->alterColumn(Callback::tableName(), 'phone', $this->bigInteger()->notNull());
    }

}
