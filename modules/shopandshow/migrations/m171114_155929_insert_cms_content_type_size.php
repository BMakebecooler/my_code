<?php

use yii\db\Migration;

/**
 * Class m171114_155929_insert_cms_content_type_size
 */
class m171114_155929_insert_cms_content_type_size extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->insert('cms_content_type', [
            'name' => 'KFFS справочник размеров',
            'code' => 'kffs-info-sizes',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->delete('cms_content_type', "code = 'kffs-info-sizes'");
    }

}
