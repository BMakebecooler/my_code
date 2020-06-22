<?php

use yii\db\Migration;

class m170711_101958_alter_table_shop_discount_config_rename extends Migration
{
    private $tableNameConfiguration = "{{%shop_discount_configuration}}";
    private $tableNameEntity = "{{%shop_discount_entity}}";
    private $tableNameValues = "{{%shop_discount_values}}";

    private $tableNameConfigurationNew = "{{%ss_shop_discount_configuration}}";
    private $tableNameEntityNew = "{{%ss_shop_discount_entity}}";
    private $tableNameValuesNew = "{{%ss_shop_discount_values}}";

    public function safeUp()
    {
        $this->renameTable($this->tableNameConfiguration, $this->tableNameConfigurationNew);
        $this->renameTable($this->tableNameEntity, $this->tableNameEntityNew);
        $this->renameTable($this->tableNameValues, $this->tableNameValuesNew);
    }

    public function safeDown()
    {
        $this->renameTable($this->tableNameConfigurationNew, $this->tableNameConfiguration);
        $this->renameTable($this->tableNameEntityNew, $this->tableNameEntity);
        $this->renameTable($this->tableNameValuesNew, $this->tableNameValues);
    }
}
