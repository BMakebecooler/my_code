<?php

use common\models\Tree;
use yii\db\Migration;

class m171004_153741_alter_table_cms_content_guid_id extends Migration
{

    public function safeUp()
    {

        $this->addColumn('{{cms_content}}', 'guid_id', $this->integer()->unsigned());
        $this->createIndex('I_cms_content_guid_id', '{{cms_content}}', 'guid_id');

        $this->insert('cms_content_type', [
            'name' => 'KFFS общие справочники',
            'code' => 'kffs-info',
        ]);

        $this->insert('cms_content_type', [
            'name' => 'KFFS справочник валют',
            'code' => 'kffs-info-currency',
        ]);

        $this->insert('cms_content_type', [
            'name' => 'KFFS справочник цветов',
            'code' => 'kffs-info-colors',
        ]);

        $this->addColumn('{{cms_tree}}', 'guid_id', $this->integer()->unsigned());
        $this->createIndex('I_tree_guid_id', '{{cms_tree}}', 'guid_id');

        /*$newTree = new Tree();
        $newTree->name = 'Разделы (классификатор)';
        $newTree->code = 'section';
        $newTree->dir = 'section';
        $newTree->tree_type_id = 2;
        $newTree->pid = 1;
        $newTree->pids = '1';
        $newTree->level = 1;

       if (!$newTree->save()) {
            var_dump($newTree->getErrors());
            die();
        }*/

        $this->addColumn('{{shop_type_price}}', 'guid_id', $this->integer()->unsigned());
        $this->createIndex('I_shop_type_price_guid_id', '{{shop_type_price}}', 'guid_id');
    }

    public function safeDown()
    {
        $this->dropIndex('I_cms_content_guid_id', '{{cms_content}}');
        $this->dropColumn('{{cms_content}}', 'guid_id');

        $this->delete('cms_content_type', "code = 'kffs-info'");
        $this->delete('cms_content_type', "code = 'kffs-info-currency'");
        $this->delete('cms_content_type', "code = 'kffs-info-colors'");

        $this->dropIndex('I_tree_guid_id', '{{cms_tree}}');
        $this->dropColumn('{{cms_tree}}', 'guid_id');

        $this->dropIndex('I_shop_type_price_guid_id', '{{shop_type_price}}');
        $this->dropColumn('{{shop_type_price}}', 'guid_id');

        Tree::deleteAll("dir = 'section' AND level = 1");
    }
}
