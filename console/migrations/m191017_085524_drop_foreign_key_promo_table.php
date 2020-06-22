<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `foreign_key_promo`.
 */
class m191017_085524_drop_foreign_key_promo_table extends Migration
{
    const TABLE_PROMO = '{{%promo}}';
    const TABLE_SEGMENT = '{{%segment}}';
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->dropForeignKey(
            'segment_fk', self::TABLE_PROMO ,
            'segment_id', self::TABLE_SEGMENT, 'id', 'SET NULL', 'SET NULL'
        );
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        return true;
    }
}
