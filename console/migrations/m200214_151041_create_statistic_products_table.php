<?php

use yii\db\Migration;

/**
 * Handles the creation of table `statistic_products`.
 */
class m200214_151041_create_statistic_products_table extends Migration
{

    use \ignatenkovnikita\migrationsaddons\AddAuthorUpdater;
    use \ignatenkovnikita\migrationsaddons\AddCreatedUpdated;

    const TABLE_NAME = '{{%statistic_products_images}}';

    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable(self::TABLE_NAME, [
            'id' => $this->primaryKey(),
            'count_all' => $this->integer()->comment('кол-во карточке без фото всего'),
            'count_all_stock' => $this->integer()->comment('кол-во карточке без фото в наличие'),
        ]);

        $this->addAllUser(self::TABLE_NAME);
        $this->addAllTime(static::TABLE_NAME);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable(self::TABLE_NAME);
    }
}
