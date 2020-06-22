<?php

use yii\db\Migration;

/**
 * Handles the creation of table `expert_users`.
 */
class m190118_105452_create_expert_users_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('expert_users', [
            'id' => $this->primaryKey(),
            'phone' => $this->bigInteger(),
            'order_count' => $this->integer(3),
            'is_processed' => $this->boolean()
        ]);

        $filePath =  APP_DIR . '/migrations/m190118_105452_create_expert_users_table.csv';
        if(file_exists($filePath)){
            $r = 0;
            $file = fopen($filePath, 'r');
            if($file){
                while (($row = fgetcsv($file, 100, ",")) != false){
                    $r++;
                    $order_count = (int)$row[0];
                    $phone = (int)$row[1];
                    $this->insert('expert_users', ['phone' => $phone, 'order_count' => $order_count, 'is_processed' => 0]);
                }
                fclose($file);
            } else {
                \Yii::error('File can`t be open.', 'migration');
            }
        } else {
            \Yii::error('File das not exist.', 'migration');
        }

    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('expert_users');
    }
}
