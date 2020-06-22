<?php

use common\models\cmsContent\CmsContentElement;
use modules\shopandshow\models\shares\SsShare;
use yii\db\Migration;

/**
 * Class m180518_075235_alter_table_ss_shares_add_image_product_id
 */
class m180518_075235_alter_table_ss_shares_add_image_product_id extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(SsShare::tableName(), 'image_product_id', $this->integer(11)->after('bitrix_product_id'));

        $this->addForeignKey(
            'fk_image_product_id',
            SsShare::tableName(), 'image_product_id',
            CmsContentElement::tableName(), 'id',
            'SET NULL', 'SET NULL'
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {

        $this->dropForeignKey('fk_image_product_id', SsShare::tableName());
        $this->dropColumn(SsShare::tableName(), 'image_product_id');
    }
}
