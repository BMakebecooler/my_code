<?php

    use ignatenkovnikita\migrationsaddons\{AddAuthorUpdater, AddCreatedUpdated};
    use yii\db\Migration;


    /**
     * Handles the creation of table `callback`.
     */
    class m190408_083013_create_callback_table extends Migration
    {
        use AddAuthorUpdater;
        use AddCreatedUpdated;

        const TABLE_CALLBACK = '{{%callback}}';

        /**
         * @inheritdoc
         */
        public function up()
        {
            $tableOptions = null;
            if ($this->db->driverName === 'mysql') {
                $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
            }

            $this->createTable(static::TABLE_CALLBACK, [
                'id' => $this->primaryKey(),
                'name' => $this->string(512)->notNull(),
                'phone' => $this->bigInteger()->notNull(),
                'time' => $this->string(),
                'theme' => $this->text(),
            ], $tableOptions);

            $this->addAllUser(self::TABLE_CALLBACK);
            $this->addAllTime(static::TABLE_CALLBACK);
        }

        /**
         * @inheritdoc
         */
        public function down()
        {
            $this->dropTable(static::TABLE_CALLBACK);
        }
    }
