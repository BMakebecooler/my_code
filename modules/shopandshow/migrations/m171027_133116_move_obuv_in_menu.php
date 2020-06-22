<?php

use yii\db\Migration;

class m171027_133116_move_obuv_in_menu extends Migration
{

    public function safeUp()
    {
        $cmsTree = \common\models\Tree::find()->andWhere("code = 'obuv'")->one();
        $cmsTree->level = 2;
        $cmsTree->pid = 9;
        $cmsTree->pids = '1/9';
        $cmsTree->dir = 'catalog/obuv';
        $cmsTree->save();

        $trees = \common\models\Tree::findAll(['pid' => $cmsTree->id]);

        foreach ($trees as $tree) {

            $tree->level = 3;
            $tree->pid = $cmsTree->id;
            $tree->pids = '1/9/' . $cmsTree->id;
            $tree->dir = 'catalog/obuv/' . $tree->code;

            $tree->save();
        }
    }

    public function safeDown()
    {

        $cmsTree = \common\models\Tree::find()->andWhere("code = 'obuv'")->one();
        $cmsTree->level = 3;
        $cmsTree->pid = 1626;
        $cmsTree->pids = '1/9/1626';
        $cmsTree->dir = 'catalog/moda/obuv';
        $cmsTree->save();

        $trees = \common\models\Tree::findAll(['pid' => $cmsTree->id]);

        foreach ($trees as $tree) {

            $tree->level = 4;
            $tree->pid = $cmsTree->id;
            $tree->pids = '1/9/1626/' . $cmsTree->id;
            $tree->dir = 'catalog/moda/obuv/' . $tree->code;

            $tree->save();
        }
    }
}
