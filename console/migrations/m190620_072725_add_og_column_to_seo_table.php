<?php

use yii\db\Migration;

/**
 * Handles adding og to table `seo`.
 */
class m190620_072725_add_og_column_to_seo_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('seo', 'og_title', $this->string(512));
        $this->addColumn('seo', 'og_description', $this->text());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('seo', 'og_title');
        $this->dropColumn('seo', 'og_description');
    }
}
