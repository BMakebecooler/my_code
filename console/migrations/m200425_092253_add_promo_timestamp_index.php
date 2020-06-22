<?php

use yii\db\Migration;

class m200425_092253_add_promo_timestamp_index extends Migration
{
    const TABLE_NAME = '{{%promo}}';
    public static $columns = ['start_timestamp', 'end_timestamp'];

    public function safeUp()
    {
        foreach (self::$columns as $column) {
            $this->createIndex($column . '_i', self::TABLE_NAME, $column);
        }
    }

    public function safeDown()
    {
//        echo "m200425_092253_add_promo_timestamp_index cannot be reverted.\n";

        return true;
    }


}
