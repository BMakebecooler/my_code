<?php

use yii\db\Migration;

class m180719_103459_alter_table_ss_pass_time_add_columns_fuser_id_seconds_first_symbol extends Migration
{
    private $tableName = '{{%ss_pass_time}}';

    public function safeUp()
    {
        $this->addColumn(
            $this->tableName,
            'seconds_first_symbol',
            $this->integer()->unsigned()->after('created_at')->comment('Кол-во секунд от запроса смс-пароля до ввода его первого символа')
        );
        $this->addColumn($this->tableName, 'fuser_id', $this->integer()->unsigned());

        $this->dropColumn($this->tableName, 'user_id');

        $this->createIndex('I_fuser_id', $this->tableName, 'fuser_id');
        $this->createIndex('I_seconds_first_symbol', $this->tableName, 'seconds_first_symbol');
        $this->createIndex('I_seconds', $this->tableName, 'seconds');
    }

    public function safeDown()
    {
        $this->dropIndex('I_fuser_id', $this->tableName);
        $this->dropIndex('I_seconds_first_symbol', $this->tableName);
        $this->dropIndex('I_seconds', $this->tableName);

        $this->dropColumn($this->tableName, 'seconds_first_symbol');
        $this->dropColumn($this->tableName, 'fuser_id');

        $this->addColumn($this->tableName, 'user_id', $this->integer()->unsigned()->after('created_at'));
    }
}
