<?php

use yii\db\Migration;

class m171101_101157_newyear_tree_sections extends Migration
{
    const CONTENT_CODE_NEWYEAR = 'newyear';

    public $subtrees = [
            ['name' => 'Украшения, тренды-2018', 'code' => 'trendy-2018'],
            ['name' => 'Новогодние платья', 'code' => 'platya'],
            ['name' => 'Праздничные образы', 'code' => 'obrazi'],
            ['name' => 'Сервировка праздничного стола', 'code' => 'servirovka'],
            ['name' => 'Идеи интерьера для нового года', 'code' => 'interior'],
            ['name' => 'Подарки близким и друзьям', 'code' => 'podarki'],
            ['name' => 'Подарочные наборы', 'code' => 'nabori'],
        ];

    public function safeUp()
    {
        $parentTree = \skeeks\cms\models\Tree::findOne(['code' => 'catalog', 'pid' => 1]);

        $newyearTree = new \common\models\Tree();
        $newyearTree->name = 'Новый год';
        $newyearTree->code = self::CONTENT_CODE_NEWYEAR;
        $newyearTree->dir = self::CONTENT_CODE_NEWYEAR;
        $newyearTree->tree_type_id = CATALOG_TREE_TYPE_ID;
        $newyearTree->view_file = '@template/modules/cms/tree/promo/newyear';
        $newyearTree->pid = $parentTree->id;
        $newyearTree->pids = '1/'.$parentTree->id;
        $newyearTree->level = 2;
        $newyearTree->priority = 1300;
        $newyearTree->has_children = 1;
        $newyearTree->count_content_element = 1;
        $newyearTree->active = \skeeks\cms\components\Cms::BOOL_N;

        if (!$newyearTree->save()) {
            var_dump($newyearTree->getErrors());

            return false;
        }

        $redirectedId = 0;
        $priority = 100;
        foreach ($this->subtrees as $subtree) {
            $tree = new \common\models\Tree();
            $tree->name = $subtree['name'];
            $tree->code = $subtree['code'];
            $tree->dir = self::CONTENT_CODE_NEWYEAR.'/'.$subtree['code'];
            $tree->tree_type_id = CATALOG_TREE_TYPE_ID;
            $tree->view_file = '@template/modules/cms/tree/promo/newyear';
            $tree->pid = $newyearTree->id;
            $tree->pids = '1/'.$parentTree->id.'/'.$newyearTree->id;
            $tree->level = 3;
            $tree->priority = $priority;
            $tree->count_content_element = 1;

            if (!$tree->save()) {
                var_dump($tree->getErrors());

                return false;
            }

            if($subtree['code'] == 'trendy-2018') {
                $redirectedId = $tree->id;
            }
            $priority+=100;
        }

        // переадресация с основного меню
        $newyearTree->redirect_tree_id = $redirectedId;
        $newyearTree->redirect_code = 302;
        $newyearTree->save();

    }

    public function safeDown()
    {
        \common\models\Tree::deleteAll("dir like 'newyear/%'");
        \common\models\Tree::deleteAll("dir = 'newyear'");
    }
}
