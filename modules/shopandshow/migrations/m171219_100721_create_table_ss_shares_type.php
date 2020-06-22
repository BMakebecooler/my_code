<?php

use yii\db\Migration;

/**
 * Class m171219_100721_create_table_ss_shares_type
 */
class m171219_100721_create_table_ss_shares_type extends Migration
{
    private $tableName = 'ss_shares_type';

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'code' => $this->string(255),
            'description' => $this->string()
        ], $tableOptions);

        $this->initValues();
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable($this->tableName);
    }

    private function initValues() {
        $types = [
            ['CATALOG_SECTION_ACTION', 'Сквозной акционный баннер'],
            //['MAIN', ''],
            ['MAIN_CTS', 'ЦТС'],
            ['MAIN_SITE_SALE_1', 'Большой баннер (слева)'],
            ['MAIN_SITE_SALE_2', 'Маленький баннер сверху'],
            ['MAIN_SITE_SALE_3', 'Маленький баннер снизу'],
            ['MAIN_SMALL_EFIR', 'Маленькие баннеры с плашкой эфир'],
            ['MAIN_SMALL_EFIR_2', 'Маленькие баннеры с плашкой эфир 2'],
            ['MAIN_WIDE_1', 'Главная - Основной'],
            //['MAIN_WIDE_12', ''],
            //['MAIN_WIDE_1_2', ''],
            ['MAIN_WIDE_2', 'Главная - Длинный узкий'],
            ['SANDS_PROMO_CTS', 'Дополнительный баннер в email рассылке'],
            ['SANDS_PROMO_CTS2', 'Тонкий банер для цтс рассылки'],

            ['BANNER_1_1', '1_1'],
            ['BANNER_1_2', '1_2'],
            ['BANNER_1_3', '1_3'],
            ['BANNER_1_4', '1_4'],
            ['BANNER_2_1', '2_1'],
            ['BANNER_2_2', '2_2'],
            ['BANNER_2_3', '2_3'],
            ['BANNER_3_1', '3_1'],
            ['BANNER_3_2', '3_2'],
            ['BANNER_3_3', '3_3'],
            ['BANNER_3_4', '3_4'],
            ['BANNER_4_1', '4_1'],
            ['BANNER_4_2', '4_2'],
            ['BANNER_4_3', '4_3'],
            ['BANNER_5_1', '5_1'],
            ['BANNER_5_2', '5_2'],
            ['BANNER_5_3', '5_3'],
            ['BANNER_5_4', '5_4'],
            ['BANNER_5_5', '5_5'],
            ['BANNER_6', '6'],
            ['BANNER_7_1', '7_1'],
            ['BANNER_7_2', '7_2'],
            ['BANNER_7_3', '7_3'],
            ['BANNER_9_1', '9_1'],
            ['BANNER_9_2', '9_2'],
            ['BANNER_9_3', '9_3'],
            ['BANNER_11_1', '11_1'],
            ['BANNER_11_2', '11_2'],
            ['BANNER_11_3', '11_3'],
            ['BANNER_11_4', '11_4'],
            ['BANNER_11_5', '11_5'],
        ];
        $this->batchInsert($this->tableName, ['code', 'description'], $types);
    }
}
