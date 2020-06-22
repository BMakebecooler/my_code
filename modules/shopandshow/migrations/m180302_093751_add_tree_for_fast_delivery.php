<?php

use yii\db\Migration;

class m180302_093751_add_tree_for_fast_delivery extends Migration
{
    const PARENT_CODE_PROMO = 'promo';
    const CODE = 'fast_delivery';

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $parentTree = \skeeks\cms\models\Tree::findOne(['code' => self::PARENT_CODE_PROMO, 'pid' => 1]);

        $tree = new \common\models\Tree();
        $tree->name = 'Быстрая доставка';
        $tree->code = self::CODE;
        $tree->dir = 'promo/'.self::CODE;
        $tree->tree_type_id = CATALOG_TREE_TYPE_ID;
        $tree->view_file = '@template/modules/cms/tree/promo/'.self::CODE;
        $tree->pid = $parentTree->id;
        $tree->pids = array_merge((array)$parentTree->pids, [$parentTree->id]);
        $tree->level = $parentTree->level + 1;
        $tree->priority = 500;

        if (!$tree->save()) {
            var_dump($tree->getErrors());
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        \common\models\Tree::deleteAll(['code' => self::CODE]);
    }
}
