<?php
/**
 * php ./yii migrate/up --migrationPath=@modules/shopandshow/migrations
 */
use yii\db\Migration;

class m170222_085126_favorites extends Migration
{
    public function safeUp()
    {

        $tableExist = $this->db->getTableSchema("{{%shop_fuser_favorites}}", true);
        if ($tableExist) {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable("{{%shop_fuser_favorites}}", [
            'id' => $this->primaryKey(),

            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),

            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),

            'shop_fuser_id' => $this->integer()->notNull(),
            'shop_product_id' => $this->integer()->notNull(),

            'active' => $this->string(1)->notNull()->defaultValue("Y"),

            'comment' => $this->text(),
        ], $tableOptions);

        $this->createIndex('updated_by', '{{%shop_fuser_favorites}}', 'updated_by');
        $this->createIndex('created_by', '{{%shop_fuser_favorites}}', 'created_by');
        $this->createIndex('created_at', '{{%shop_fuser_favorites}}', 'created_at');
        $this->createIndex('updated_at', '{{%shop_fuser_favorites}}', 'updated_at');

        $this->createIndex('shop_fuser_id', '{{%shop_fuser_favorites}}', 'shop_fuser_id');
        $this->createIndex('shop_product_id', '{{%shop_fuser_favorites}}', 'shop_product_id');

        $this->execute("ALTER TABLE {{%shop_fuser_favorites}} COMMENT = 'Для хранения избранных товаров';");

        $this->addForeignKey(
            'shop_fuser_favorites__shop_fuser_id', "{{%shop_fuser_favorites}}",
            'shop_fuser_id', '{{%shop_fuser}}', 'id', 'CASCADE', 'CASCADE'
        );

        $this->addForeignKey(
            'shop_fuser_favorites__shop_product_id', "{{%shop_fuser_favorites}}",
            'shop_product_id', '{{%shop_product}}', 'id', 'CASCADE', 'CASCADE'
        );

        $this->addForeignKey(
            'shop_fuser_favorites__shop_product_id_cce', "{{%shop_fuser_favorites}}",
            'shop_product_id', '{{%cms_content_element}}', 'id', 'CASCADE', 'CASCADE'
        );

/*        $this->addForeignKey(
            'shop_fuser_favorites__created_by', "{{%shop_fuser_favorites}}",
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'shop_fuser_favorites__updated_by', "{{%shop_fuser_favorites}}",
            'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );
*/

        return true;
    }

    public function safeDown()
    {
        $this->dropForeignKey("shop_fuser_favorites__shop_fuser_id", "{{%shop_fuser_favorites}}");
        $this->dropForeignKey("shop_fuser_favorites__shop_product_id", "{{%shop_fuser_favorites}}");
        $this->dropForeignKey("shop_fuser_favorites__shop_product_id_cce", "{{%shop_fuser_favorites}}");

//        $this->dropForeignKey("shop_fuser_favorites__created_by", "{{%shop_fuser_favorites}}");
//        $this->dropForeignKey("shop_fuser_favorites__updated_by", "{{%shop_fuser_favorites}}");

        $this->dropTable("{{%shop_fuser_favorites}}");

        return true;
    }
}
