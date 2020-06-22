<?php

use yii\db\Migration;

class m170420_101124_create_table_sale_banners extends Migration
{

    private $tableNameBanner = "{{%ss_shares}}";
    private $tableNameBannerProducts = "{{%ss_share_products}}";

    public function safeUp()
    {

        $tableExist = $this->db->getTableSchema($this->tableNameBanner, true);

        if ($tableExist) {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable($this->tableNameBanner, [
            'id' => $this->primaryKey(),
            'created_at' => 'timestamp NOT NULL',
            'begin_datetime' => $this->integer()->notNull(),
            'end_datetime' => $this->integer()->notNull(),
            'image_id' => $this->integer(),
            'bitrix_sands_schedule_id' => $this->integer(),
            'bitrix_banner_id' => $this->integer(),
            'promo_type' => $this->integer(),
            'banner_type' => $this->string(255),
            'promo_type_code' => $this->string(255),
            'name' => $this->string(255),
            'code' => $this->string(255),
            'promocode' => $this->string(255),
            'url' => $this->string(255),
            'active' => $this->string(1),
        ], $tableOptions);

        $this->createIndex('begin_datetime_active_type', $this->tableNameBanner, ['begin_datetime', 'active', 'banner_type']);
        $this->createIndex('begin_datetime_end_datetime_type', $this->tableNameBanner, ['begin_datetime', 'end_datetime', 'active', 'banner_type']);

       /* $this->createTable($this->tableNameBannerProducts, [
            'id' => $this->primaryKey(),
            'banner_id' => $this->integer()->notNull(),
            'bitrix_id' => $this->integer()->notNull(),
            'product_id' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->createIndex('banner_id', $this->tableNameBannerProducts, 'banner_id');

        $this->addForeignKey(
            'ss_share_products_banner_id', $this->tableNameBannerProducts,
            'banner_id', $this->tableNameBanner, 'id', 'CASCADE', 'CASCADE'
        );*/

    }

    public function safeDown()
    {
//        $this->dropTable($this->tableNameBannerProducts);
        $this->dropTable($this->tableNameBanner);
    }
}
