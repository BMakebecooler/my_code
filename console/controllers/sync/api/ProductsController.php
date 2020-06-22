<?php

/**
 * php ./yii sync/api/products
 * php ./yii sync/api/products/variations
 * php ./yii sync/api/products/express-update
 */

namespace console\controllers\sync\api;

use common\helpers\Developers;
use console\controllers\sync\SyncController;
use modules\api\models\mongodb\product\Product;
use modules\api\models\mongodb\product\Variation;
use common\models\cmsContent\CmsContentElement;
use modules\shopandshow\lists\Products;
use modules\shopandshow\models\shop\ShopContentElement;
use modules\shopandshow\models\shop\SsShopProductPrice;
use skeeks\cms\components\Cms;
use yii\helpers\Console;
use yii\mongodb\Exception;

/**
 * Class ProductsController
 * @package console\controllers
 */
class ProductsController extends SyncController
{

    /**
     * Полный список товаров
     * @return array
     */
    public function actionIndex()
    {
        $sql = <<<SQL
            SELECT cce.* 
            FROM cms_content_element AS cce 
            INNER JOIN shop_product AS sp ON sp.id = cce.id
            INNER JOIN ss_shop_product_prices AS spp ON spp.product_id = cce.id
            INNER JOIN cms_content_element_image AS images ON images.content_element_id = cce.id
            WHERE cce.active = 'Y' AND sp.quantity >=1 AND cce.image_id IS NOT NULL
            ORDER BY cce.id DESC
            LIMIT :limit
            OFFSET :offset
SQL;

        $batchCount = 99;
        $countElements = 20000;
        $mongoDB = \Yii::$app->mongodb->createCommand();

        $collection = \Yii::$app->mongodb->getCollection(Product::collectionName());

        for ($offset = 0; $offset <= $countElements; $offset += $batchCount) {

            $cmsContentElements = CmsContentElement::findBySql($sql, [
                ':limit' => $batchCount,
                ':offset' => $offset,
            ])
                ->joinWith(['images', 'cmsTree', 'price', 'guidObject'])
                ->all();

            if (!$cmsContentElements) {
                break;
            }

            foreach ($cmsContentElements as $product) {
                if ($data = Product::getData($product)) {
                    try {
                        $collection->insert($data);
//                        $this->stdout(sprintf("insert:%s\n",  $data['id']), Console::FG_GREEN);

                    } catch (Exception $exception) {
                        $mongoDB->update(Product::collectionName(), ['id' => $data['id']], $data, ['upsert' => true]);
                    }
                }
            }

            $this->stdout(sprintf("insert:%s\n", $offset), Console::FG_GREEN);

            unset($cmsContentElements);
//
            gc_enable();
            gc_collect_cycles();
            gc_mem_caches();
//
//            sleep(2);

            echo Developers::byMb(memory_get_peak_usage(true)) . "====" . Developers::byMb(memory_get_usage()) . "\n";
        }

        return [];
    }

    /**
     * Вариации (Модификации)
     * @return array
     */
    public function actionVariations()
    {
        $productList = (new Products())->getProductList(null, null, [
            'skipNonAjaxPageItems' => false,
            'withImages' => false,
            'content_ids' => [OFFERS_CONTENT_ID],
        ]);

        $dataProvider = $productList->dataProvider;

        /*        $dependency = new \yii\caching\DbDependency([
                    'sql' => 'SELECT MAX(updated_at) FROM cms_content_element WHERE content_id = 10'
                ]);

                \Yii::$app->db->cache(function () use ($dataProvider) {
                    $dataProvider->prepare();
                }, HOUR_1, $dependency);*/

        $dataProvider->prepare();

        $mongoDB = \Yii::$app->mongodb->createCommand();

        $variations = [];

        /**
         * @var $product CmsContentElement|ShopContentElement
         */
        foreach ($dataProvider->models as $product) {
            $variations[] = Variation::getData($product);
        }

        $batchs = array_chunk($variations, 300);

        foreach ($batchs as $b) {
            foreach ($b as $variation) {
                $mongoDB->addUpdate(['id' => $variation['id']], $variation, ['upsert' => true]);
            }

            $mongoDB->executeBatch(Variation::collectionName());

            $this->stdout("insertedIds: " . (count($b)) . "\n", Console::FG_GREEN);
        }
    }


    /**
     * Экспресс обновления
     */
    public function actionExpressUpdate()
    {
        $sql = <<<SQL
SELECT cce.id AS product_id, price.price, price.max_price, not_public_value.value AS not_public, cce.active
FROM cms_content_element AS cce
LEFT JOIN `cms_content_element_property` AS `not_public_value` ON not_public_value.element_id = cce.id AND
  not_public_value.property_id = (SELECT id FROM `cms_content_property` WHERE `content_id` = '2' AND `code` = 'NOT_PUBLIC')
LEFT JOIN `shop_product` ON `cce`.`id` = `shop_product`.`id`
LEFT JOIN `ss_shop_product_prices` AS price ON `cce`.`id` = `price`.`product_id`
WHERE (NOT (`cce`.`tree_id` IS NULL)) AND (`cce`.`content_id`=2)
ORDER BY cce.updated_at DESC
LIMIT :limit
OFFSET :offset
SQL;

        $batchCount = 5000;
        $countElements = 50000;

        $mongoDB = \Yii::$app->mongodb->createCommand();

        for ($offset = 0; $offset <= $countElements; $offset += $batchCount) {

            $products = \Yii::$app->db->createCommand($sql, [
                ':limit' => $batchCount,
                ':offset' => $offset,
            ])->queryAll();

            if (!$products) {
                break;
            }

            foreach ($products as $product) {

                $active = ($product['active'] == Cms::BOOL_N || $product['not_public'] == Cms::BOOL_Y ) ? false : true;

                $data = [
                    'price' => $product['price'],
                    'regular_price' => $product['max_price'],
                    'active' => $active,
                ];

                $mongoDB->addUpdate(['id' => (int)$product['product_id']], $data);
            }

            unset($product);

            $mongoDB->executeBatch(Product::collectionName());

            $this->stdout("updated: " . $offset . "\n", Console::FG_GREEN);

            gc_enable();
            gc_collect_cycles();
            gc_mem_caches();

            echo Developers::byMb(memory_get_peak_usage(true)) . "====" . Developers::byMb(memory_get_usage()) . "\n";
        }

        $this->stdout("updated all " . "\n", Console::FG_GREEN);
    }
}