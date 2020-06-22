<?php

use yii\db\Migration;
use common\models\Tree;
use yii\db\ActiveRecord;

/**
 * Handles the creation of table `gift_2019`.
 */
class m181022_132816_create_gift_2019_table extends Migration
{

  private $table = '{{%ss_gift_2019}}';


  private function generateURL($item) {
    return str_replace('catalog', '', $item['dir']).'/';
  }

  /**
   * @inheritdoc
   */
  public function up()
  {
    $schema = [
      'id' => $this->primaryKey(),

      'cms_tree_id'          => $this->integer(),

      'is_woman'             => $this->boolean(),
      'is_man'               => $this->boolean(),
      'is_relative'          => $this->boolean(),
      'is_colleague'         => $this->boolean(),

      'age_lt_14'            => $this->boolean(),
      'age_lt_30'            => $this->boolean(),
      'age_eq_30'            => $this->boolean(),
      'age_lt_50'            => $this->boolean(),
      'age_gt_50'            => $this->boolean(),

      'interest_fashion'     => $this->boolean(),
      'interest_jewerly'     => $this->boolean(),
      'interest_cooking'     => $this->boolean(),
      'interest_tech'        => $this->boolean(),
      'interest_interior'    => $this->boolean(),
      'interest_needlework'  => $this->boolean(),
    ];
    $this->createTable($this->table, $schema);
    $this->alterColumn($this->table, 'id', $this->smallInteger(8).' NOT NULL AUTO_INCREMENT');
    $this->execute(<<<SQL
      ALTER TABLE ss_gift_2019 ADD CONSTRAINT `ss_gift_2019_cms_tree_id_fk` FOREIGN KEY (cms_tree_id) REFERENCES cms_tree (id) ON DELETE CASCADE;
SQL
    );


    $sql = <<<SQL
      SELECT tree.*
        FROM cms_tree AS tree
        WHERE tree.id IN (
          SELECT `cms_tree`.`id` as t
          FROM `cms_tree`
            INNER JOIN `cms_content_element` ON `cms_tree`.`id` = `cms_content_element`.`tree_id`
            INNER JOIN `ss_shop_product_prices` AS price ON `price`.`product_id` = `cms_content_element`.`id`
            INNER JOIN shop_product AS shop ON shop.id = cms_content_element.id
          WHERE (`cms_content_element`.`content_id` = 2)
          GROUP BY `cms_tree`.`id`
      ) AND NOT tree.id = 9 AND tree.level >= 3
SQL;



    $categories = Tree::findBySql($sql)->all();
    $values = [];
    // read file with data
    $source = ROOT_DIR.'/docs/gift_2019_data.csv';
    $handle = fopen($source, "r");
    while ( ($data = fgetcsv($handle, 10000, ";")) !== FALSE) {
      unset($data[1], $data[2]); // trash
      $url = array_shift($data);
      // write url as key
      $values[$url] = array_filter($data, function($val) {return $val != '';});
    };
    fclose($handle);

    // parse values and search id of record by his URL
    $valuesToInsert = [];
    foreach ($categories as $category) {
      $url = $this->generateURL($category);
      if ( !empty($values[$url]) ) {
        array_unshift($values[$url], $category['id']);
        $valuesToInsert[] = "(".implode(',', $values[$url]).")"; //
      }
    }

    // get db connection
    $db = ActiveRecord::getDb();
    unset($schema['id']); // generated automatically
    $keys = array_keys($schema);

    // send data to db
    $sql = "INSERT INTO `ss_gift_2019` (".implode(',',$keys).") VALUES ".implode(',', $valuesToInsert);
    $db->createCommand($sql)->execute();
  }

  /**
   * @inheritdoc
   */
  public function down()
  {
    $this->dropTable($this->table);
  }
}
