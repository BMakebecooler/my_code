<?php

use yii\db\Migration;

/**
 * Class m180115_090835_insert_kfss_content_card
 */
class m180115_090835_insert_kfss_content_card extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $catalog = \common\lists\TreeList::getTreeByCode('catalog');

        $this->insert('cms_content', [
            'id' => CARD_CONTENT_ID,
            'name' => 'Карточка товара',
            'code' => 'card',
            'active' => 'Y',
            'priority' => 500,
            'name_meny' => 'Карточки',
            'name_one' => 'Карточка',
            'content_type' => 'products',
            'default_tree_id' => $catalog->id,
            'root_tree_id' => $catalog->id,
            'is_allow_change_tree' => 'Y',
            'visible' => 'N',
            'parent_content_on_delete' => 'CASCADE',
            'parent_content_is_required' => 'Y',
            'index_for_search' => '',
            'parent_content_id' => PRODUCT_CONTENT_ID
        ]);

        $this->update('cms_content', ['parent_content_id' => CARD_CONTENT_ID], ['id' => OFFERS_CONTENT_ID]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->update('cms_content', ['parent_content_id' => PRODUCT_CONTENT_ID], ['id' => OFFERS_CONTENT_ID]);
        $this->delete('cms_content', ['id' => CARD_CONTENT_ID]);
    }
}
