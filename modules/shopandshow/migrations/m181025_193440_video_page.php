<?php

use yii\db\Migration;

class m181025_193440_video_page extends Migration
{
    const TREE_DIR = 'video';

    public function safeUp()
    {
        $videoPage = new \common\models\Tree();
        $videoPage->name = 'Видео раздел';
        $videoPage->code = 'video';
        $videoPage->dir = self::TREE_DIR;
        $videoPage->tree_type_id = 5;
        $videoPage->view_file = '@template/modules/cms/tree/video_products.php';
        $videoPage->pid = 1;
        $videoPage->pids = 1;
        $videoPage->level = 1;

        if (!$videoPage->save()) {
            var_dump($videoPage->getErrors());
            return false;
        }
    }

    public function safeDown()
    {
        \common\models\Tree::deleteAll("dir = :dir", [':dir' => self::TREE_DIR]);

        return true;
    }

}
