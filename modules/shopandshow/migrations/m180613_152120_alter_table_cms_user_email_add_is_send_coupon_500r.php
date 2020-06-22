<?php

use yii\db\Migration;

/**
 * Class m180613_152120_alter_table_cms_user_email_add_is_send_coupon_500r
 */
class m180613_152120_alter_table_cms_user_email_add_is_send_coupon_500r extends Migration
{
    private $tableNameCmsUserEmail  = '{{%cms_user_email}}';

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        try {
            $this->addColumn($this->tableNameCmsUserEmail, 'is_send_coupon_500r', $this->string(1)->defaultValue('N'));

        } catch (Exception $exception) {
            return true;
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        try {
            $this->dropColumn($this->tableNameCmsUserEmail, 'is_send_coupon_500r');

        } catch (Exception $exception) {
            return true;
        }
    }
}
