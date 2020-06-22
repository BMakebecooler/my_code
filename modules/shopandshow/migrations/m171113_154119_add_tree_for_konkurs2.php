<?php

use yii\db\Migration;

/**
 * Class m171113_154119_add_tree_for_konkurs2
 */
class m171113_154119_add_tree_for_konkurs2 extends Migration
{
    const CONTENT_CODE_LOOKBOOK = 'lookbook';
    const LOOKBOOK_NAME = 'Страница конкурса "Икона Стиля"';

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $tree = \skeeks\cms\models\Tree::findOne(['code' => self::CONTENT_CODE_LOOKBOOK, 'pid' => 1]);

        $lookbookSection = new \common\models\Tree();
        $lookbookSection->name = self::LOOKBOOK_NAME;
        $lookbookSection->code = 'iconastyle';
        $lookbookSection->dir = 'konkurs/iconastyle';
        $lookbookSection->tree_type_id = 2;
        $lookbookSection->view_file = '@template/modules/cms/content-element/lookbook/konkurs-2';
        $lookbookSection->pid = $tree->id;
        $lookbookSection->pids = '1/'.$tree->id;
        $lookbookSection->level = 2;

        if (!$lookbookSection->save()) {
            var_dump($lookbookSection->getErrors());
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        \common\models\Tree::deleteAll("dir = 'konkurs/iconastyle'");
    }

}
