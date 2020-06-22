<?php

use yii\db\Migration;

/**
 * Class m180112_094752_insert_kfss_product_content
 */
class m180112_094752_insert_kfss_product_content extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $catalog = \common\lists\TreeList::getTreeByCode('catalog');

        $this->insert('cms_content', [
            'id' => KFSS_PRODUCT_CONTENT_ID,
            'name' => 'Товары кфсс',
            'code' => 'product_kfss',
            'active' => 'Y',
            'priority' => 500,
            'name_meny' => 'Товары',
            'name_one' => 'Товар',
            'content_type' => 'products',
            'default_tree_id' => $catalog->id,
            'root_tree_id' => $catalog->id,
            'is_allow_change_tree' => 'Y',
            'visible' => 'N',
            'parent_content_on_delete' => 'CASCADE',
            'parent_content_is_required' => 'N',
            'index_for_search' => ''
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->delete('cms_content', ['id' => KFSS_PRODUCT_CONTENT_ID]);
    }
}
