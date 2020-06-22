<?php

use yii\db\Migration;

class m200427_122215_add_promo_image_banner_column extends Migration
{
    const TABLE_NAME = '{{%promo}}';
    const COLUMN_NAME = '{{%have_image_banner}}';
    const COLUMN_OLD_NAME = '{{%have_image}}';

    public function safeUp()
    {
        $this->addColumn(self::TABLE_NAME, self::COLUMN_NAME, $this->boolean()->defaultValue(0));
        $this->update(
            self::TABLE_NAME,
            [self::COLUMN_NAME => \common\helpers\Common::BOOL_Y_INT],
            [self::COLUMN_OLD_NAME => \common\helpers\Common::BOOL_Y_INT]
        );
    }

    public function safeDown()
    {
//        echo "m200323_080316_add_segment_disable_column cannot be reverted.\n";
        $this->dropColumn(self::TABLE_NAME, self::COLUMN_NAME);

        return true;
    }
}