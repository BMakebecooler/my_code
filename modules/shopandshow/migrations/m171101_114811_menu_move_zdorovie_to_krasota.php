<?php

use yii\db\Migration;

class m171101_114811_menu_move_zdorovie_to_krasota extends Migration
{
    public function safeUp()
    {
        $destTree = \common\models\Tree::find()->andWhere("code = 'krasota-i-zdorove'")->one();
        $cmsTree = \common\models\Tree::find()->andWhere("code = 'kosmetika'")->one();

        $cmsTree->level = 3;
        $cmsTree->pid = $destTree->id;
        $cmsTree->pids = join(\common\models\Tree::PIDS_DELIMETR, $destTree->pids).'/'.$destTree->id;
        $cmsTree->dir = 'catalog/krasota-i-zdorove/kosmetika';
        $cmsTree->save();
    }

    public function safeDown()
    {
        $cmsTree = \common\models\Tree::find()->andWhere("code = 'kosmetika'")->one();
        $cmsTree->level = 2;
        $cmsTree->pid = 9;
        $cmsTree->pids = '1/9';
        $cmsTree->dir = 'catalog/kosmetika';
        $cmsTree->save();
    }
}
