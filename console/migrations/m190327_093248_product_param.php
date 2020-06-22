<?php

use yii\db\Migration;

class m190327_093248_product_param extends Migration
{
    use \ignatenkovnikita\migrationsaddons\AddCreatedUpdated;
    use \ignatenkovnikita\migrationsaddons\AddAuthorUpdater;
    use \ignatenkovnikita\migrationsaddons\ForeignKeyTrait;

    const TABLE_PRODUCT_PARAM_TYPE = '{{%product_param_type}}';
    const TABLE_PRODUCT_PARAM = '{{%product_param}}';
    const TABLE_PRODUCT_PARAM_PRODUCT = '{{%product_param_product}}';
    const TABLE_PRODUCT = '{{%cms_content_element}}';

    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(self::TABLE_PRODUCT_PARAM_TYPE, [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'guid' => $this->string()->notNull(),
            'code' => $this->string()
        ],$tableOptions);

        $this->createTable(self::TABLE_PRODUCT_PARAM, [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'type_id' => $this->integer()->notNull()
        ],$tableOptions);

        $this->addForeignKeys(self::TABLE_PRODUCT_PARAM,[
            ['type_id', self::TABLE_PRODUCT_PARAM_TYPE, 'id']
        ]);

        $this->addAllTime(self::TABLE_PRODUCT_PARAM);
        $this->addAllUser(self::TABLE_PRODUCT_PARAM);

        $this->createTable(self::TABLE_PRODUCT_PARAM_PRODUCT, [
            'id' => $this->primaryKey(),
            'product_id' => $this->integer()->notNull(),
            'product_param_id' => $this->integer()->notNull()
        ]);
        $this->createIndex('index_product_param_product_product_id_product_param_id',self::TABLE_PRODUCT_PARAM_PRODUCT,['product_id','product_param_id'],true);

        $this->addForeignKeys(self::TABLE_PRODUCT_PARAM_PRODUCT, [
            ['product_id', self::TABLE_PRODUCT, 'id'],
            ['product_param_id', self::TABLE_PRODUCT_PARAM, 'id'],
        ]);

    }

    public function safeDown()
    {
        $this->dropForeignKeys(self::TABLE_PRODUCT_PARAM,[
            ['type_id', self::TABLE_PRODUCT_PARAM_TYPE, 'id']
        ]);

        $this->dropForeignKeys(self::TABLE_PRODUCT_PARAM_PRODUCT, [
            ['product_id', self::TABLE_PRODUCT, 'id'],
            ['product_param_id', self::TABLE_PRODUCT_PARAM, 'id'],
        ]);

        $this->dropTable(self::TABLE_PRODUCT_PARAM);
        $this->dropTable(self::TABLE_PRODUCT_PARAM_PRODUCT);
        $this->dropTable(self::TABLE_PRODUCT_PARAM_TYPE);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190327_093248_product_param cannot be reverted.\n";

        return false;
    }
    */
}
