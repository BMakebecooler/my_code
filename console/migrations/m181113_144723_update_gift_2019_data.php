<?php

use yii\db\Migration;
use common\models\Tree;
use yii\db\ActiveRecord;


class m181113_144723_update_gift_2019_data extends Migration
{

  private $table = '{{%ss_gift_2019}}';

    private function generateURL($item) {
        return str_replace('catalog', '', $item['dir']).'/';
    }

    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
      $this->truncateTable($this->table);

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
      $keys = [
        'cms_tree_id',

        'is_woman',
        'is_man',
        'is_relative',
        'is_colleague',

        'age_lt_14',
        'age_lt_30',
        'age_eq_30',
        'age_lt_50',
        'age_gt_50',

        'interest_fashion',
        'interest_jewerly',
        'interest_cooking',
        'interest_tech',
        'interest_interior',
        'interest_needlework',
      ];

      // send data to db
      $sql = "INSERT INTO `ss_gift_2019` (".implode(',',$keys).") VALUES ".implode(',', $valuesToInsert);
      $db->createCommand($sql)->execute();

    }

    public function down()
    {
        echo "m181113_144720_update_gift_2019_data cannot be reverted.\n";

        return false;
    }
}
