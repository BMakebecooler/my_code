<?php

/**
 * php ./yii tools/products/clear-tn
 * php ./yii tools/products/clear-tn-product 828509 0
 * php ./yii tools/products/clear-tn-product-slider
 * php ./yii tools/products/remove-duplicate-photo
 *
 * php ./yii tools/products/clear-tn-from-file console/controllers/tools/links_to_clean.txt
 *
 * php ./yii tools/products/update-ratio-kod-s N //N - число товаров для подмеса на одну страницу (по умолчанию 3)
 * php ./yii tools/products/update-ratio-by-segments
 *
 * php ./yii tools/products/set-main-images
 *
 * php ./yii tools/products/export-products-with-guids
 *
 * php ./yii tools/products/export-products-with-brand-in-name
 * php ./yii tools/products/fix-image-extentions [N] //N - число элементов для обработки
 * php ./yii tools/products/delete-thumbs-with-uppercase-extention [N] //N - число элементов для обработки
 */

namespace console\controllers\tools;

use common\helpers\Category;
use common\models\cmsContent\CmsContentElement;
use common\models\OnAir\OnAirSchedule;
use common\models\Product;
use console\controllers\export\ExportController;
use modules\shopandshow\models\common\Guid;
use modules\shopandshow\models\mediaplan\AirDayProductTime;
use modules\shopandshow\models\shop\stock\SsProductsSegments;
use modules\shopandshow\models\statistic\ShopProductStatistic;
use skeeks\cms\components\Cms;
use skeeks\cms\models\CmsContentElementImage;
use yii\db\Expression;
use yii\helpers\Console;
use common\models\filtered\products\Catalog;
use common\models\ProductAbc;



/**
 * Class ProductsController
 * @package console\controllers
 */
class ProductsController extends ExportController
{
    /**
     * Удаление превьюшек
     */
    public function actionClearTn()
    {

        $sql = <<<SQL
            SELECT `cms_content_element`.*
            FROM `cms_content_element` 
            LEFT JOIN `shop_product` ON `cms_content_element`.`id` = `shop_product`.`id` 
            LEFT JOIN `ss_shop_product_prices` ON `cms_content_element`.`id` = `ss_shop_product_prices`.`product_id` 
            LEFT JOIN `cms_content_element_property` `badge_hit` ON badge_hit.element_id = cms_content_element.id AND badge_hit.property_id = 
            (SELECT id FROM `cms_content_property` WHERE `content_id` = '2' AND `code` = 'HIT_POPULAR') 
            LEFT JOIN `cms_content_element_property` `lot_name_value` ON lot_name_value.element_id = cms_content_element.id AND lot_name_value.property_id = 
            (SELECT id FROM `cms_content_property` WHERE `content_id` = '2' AND `code` = 'LOT_NAME') LEFT JOIN `cms_content_element_property` `rating_value` ON rating_value.element_id = cms_content_element.id AND rating_value.property_id = 
            (SELECT id FROM `cms_content_property` WHERE `content_id` = '2' AND `code` = 'RATING')
            LEFT JOIN `cms_content_element_property` `not_public_value` ON not_public_value.element_id = cms_content_element.id AND not_public_value.property_id = 
            (SELECT id FROM `cms_content_property` WHERE `content_id` = '2' AND `code` = 'NOT_PUBLIC')
            LEFT JOIN `ss_mediaplan_air_day_product_time` `air_day_product_time` ON air_day_product_time.lot_id = cms_content_element.id AND air_day_product_time.begin_datetime >=
              UNIX_TIMESTAMP(DATE_FORMAT(NOW(), '%Y-%m-%d 08:00:00'))
            LEFT JOIN `shop_product_statistic` `super_sort` ON super_sort.id = cms_content_element.id 
            WHERE (NOT (`ss_shop_product_prices`.`min_price` IS NULL)) AND (`ss_shop_product_prices`.`min_price` > 2) AND (NOT (`cms_content_element`.`tree_id` IS NULL)) 
            AND (`shop_product`.`quantity` >= 1) AND (NOT (`cms_content_element`.`image_id` IS NULL)) AND (not_public_value.value IS NULL OR not_public_value.value = '') AND (`cms_content_element`.`active`='Y') AND (`cms_content_element`.`content_id`=2) 
            AND (`cms_content_element`.`tree_id` NOT IN (1672, 1675, 1668, 1690, 1676, 1674, 1673, 1687, 1688, 1689, 1677, 1648, 1702, 1701, 1703, 1655, 1647, 1714, 1715, 1881, 1766, 1800, 1801, 1767, 1803, 1802, 1729, 1726, 1626))
            ORDER BY `air_day_product_time`.`begin_datetime` DESC, super_sort.k_1 DESC 
            LIMIT 3500 OFFSET 1400
SQL;

        $products = CmsContentElement::findBySql($sql, [])->all();

        $counter = 0;
        $count = count($products);

        $this->stdout($this->ansiFormat("Внимание!", Console::FG_RED, Console::BOLD) . " Удалятся TN из $count товарова, еще есть возможность это отменить!\n");
        $this->delay(5);

        /**
         * @var CmsContentElement $product
         */
        foreach ($products as $product) {

            ++$counter;

            $product->image->deleteTmpDir();
            $this->stdout($counter . "< lot id: " . $product->id . "\n", Console::FG_RED);

            $this->stdout("clean main image: " . $product->image->id . "\n", Console::FG_GREEN);

            foreach ($product->images as $image) {
                $image->deleteTmpDir();

                $this->stdout("clean additional image: " . $image->id . "\n", Console::FG_GREEN);
            }
        }

        $this->stdout("clean all " . $count . "\n", Console::FG_GREEN);
    }


    public function actionClearTnProductSlider()
    {
        $productsIds = [];
        $productsIdsNoModa = [];

        $sliderTypes = [
            'cts',
            'air',
            'day',
            'week'
        ];

        foreach ($sliderTypes as $type) {

            switch ($type) {
                case 'cts':
                default:
                    $productsIdsData = ProductAbc::find()
                        ->byType(ProductAbc::TYPE_CTS)
                        ->orderBy('order')
                        ->select('product_id')
                        ->all();

                    foreach ($productsIdsData as $item) {
                        $productsIds[] = $item->product_id;
                    }

                    break;
                case 'air':
                    $nowOnAir = OnAirSchedule::create(time())->make();
                    if ($nowOnAir['products']) {
                        foreach ($nowOnAir['products'] as $k => $product) {
                            $productsIds[$product['lot_id']] = $product['lot_id'];
                        }
                    }
                    break;
                case 'day':
                    $productsOfDay = ProductAbc::findDay();
                    foreach ($productsOfDay as $product) {
                        $productsIds[] = $product->id;
                    }
                    break;
                case 'week':
                    $productsOfWeek = ProductAbc::findWeek();
                    foreach ($productsOfWeek as $product) {
                        $productsIds[] = $product->id;
                    }
                    break;
            }
        }

        //Вычленить только из не моды
        if(count($productsIds)) {

            $productsIds = array_filter($productsIds, function($element) {
                return !empty($element);
            });

            $products = Product::find()
                ->onlyLot()
                ->onlyActive()
                ->andWhere(['IN', 'id', $productsIds]);

            foreach ($products->each() as $product) {
                $isModa = Category::checkIsModa($product->tree);
                if (!$isModa) {
                    $productsIdsNoModa[] = $product->id;
                }
            }
        }

        if(count($productsIdsNoModa)){

            $this->stdout($this->ansiFormat("Внимание!", Console::FG_RED, Console::BOLD) . "Всего товаров на удаление картинок ".count($productsIdsNoModa)."\n");
            $this->delay(10);

            foreach ($productsIdsNoModa as $id){
                $this->actionClearTnProduct($id,0);
            }
        }

    }


    /**
     * @param $productId
     * @param $delay
     */
    public function actionClearTnProduct($productId, $delay = 2)
    {
        $product = CmsContentElement::findOne($productId);

        if (!$product) {
            $this->stdout("lot не найден: " . $productId . "\n", Console::FG_RED);
            return;
        }

        if ($delay) {
            $this->stdout($this->ansiFormat("Внимание!", Console::FG_RED, Console::BOLD) . " Удалятся TN товарова, еще есть возможность это отменить!\n");
            $this->delay($delay);
        }

        if ($product->image) {
            $product->image->deleteTmpDir();
        }

        if ($product->images) {
            foreach ($product->images as $image) {
                $image->deleteTmpDir();
            }
        }

        $this->stdout("clean done {$productId} " . "\n", Console::FG_GREEN);
    }

    public function actionClearTnFromFile($file)
    {
        $rows = file($file);
        foreach ($rows as $row) {
            if (!trim($row)) continue;

            $link = parse_url(trim($row));
            $path = rtrim($link['path'], '/');
            $idLotStr = substr($path, strrpos($path, '/') + 1);
            $idLot = substr($idLotStr, 0, strpos($idLotStr, '-'));

            if ($idLot) {
                $this->actionClearTnProduct($idLot, 0);
            }
        }
        $this->stdout("clean from file done " . "\n", Console::FG_GREEN);
    }

    /**
     * Удаление деблей фоток (из за косяка с импортом)
     */
    public function actionRemoveDuplicatePhoto()
    {

        $sql = <<<SQL
    SELECT *
    FROM cms_content_element_image AS item
    INNER JOIN(
        SELECT image.id
        FROM cms_content_element_image AS image
        INNER JOIN cms_content_element cce ON cce.id = image.content_element_id AND cce.content_id = 2
        GROUP BY image.bitrix_id
        HAVING COUNT(image.bitrix_id) >1
    ) AS dublicate ON item.id = dublicate.id 
SQL;

        $images = CmsContentElementImage::findBySql($sql, [])->all();

        $counter = 0;
        $count = count($images);

        $this->stdout($this->ansiFormat("Внимание!", Console::FG_RED, Console::BOLD) . " Удалится $count дубликатов фоток!\n");
        $this->delay(5);

        /**
         * @var CmsContentElementImage $image
         */
        foreach ($images as $image) {

            ++$counter;

            $image->storageFile->delete();
            $image->delete();

            $this->stdout($counter . "< image id: " . $image->id . "\n", Console::FG_GREEN);
        }

        $this->stdout("clean all " . $count . "\n", Console::FG_GREEN);
    }

    /**
     * Подмес товаров Плашек на страницы каталога с использованием коэффициента
     * @PARAM $productsInjectsNumPerPage - кол-во товаров для подмеса из расчета на 1 страницу
     */
    public function actionUpdateRatioKodS($productsInjectsNumPerPage = 3)
    {

        $productsKUpdates = array();

        //Выбираем товары из акции
        $sql = "
SELECT
  val.value AS product_id, 
  content_element.tree_id, 
  super_sort.k_1
FROM ss_shop_discount_values AS val
  INNER JOIN ss_shop_discount_configuration AS conf
    ON conf.id = val.shop_discount_configuration_id
  INNER JOIN ss_shop_discount_entity AS entity
    ON entity.id = conf.shop_discount_entity_id
  INNER JOIN shop_discount AS sd
    ON sd.id = conf.shop_discount_id AND sd.code = 'codes' AND
       (sd.active_from <= UNIX_TIMESTAMP() AND sd.active_to >= UNIX_TIMESTAMP())
  INNER JOIN shop_discount_coupon AS sd_coupon
    ON sd.id = sd_coupon.shop_discount_id
  LEFT JOIN cms_content_element AS content_element
    ON content_element.id=val.value
  LEFT JOIN ss_mediaplan_air_day_product_time AS air_day_product_time
    ON air_day_product_time.lot_id = val.value AND
       air_day_product_time.begin_datetime >= UNIX_TIMESTAMP(DATE_FORMAT(NOW(), '%Y-%m-%d 08:00:00'))
  LEFT JOIN shop_product_statistic AS super_sort
    ON super_sort.id = val.value
WHERE
  entity.class = 'ForLots'
  AND sd_coupon.is_active = 1
  AND sd_coupon.coupon IS NOT NULL
  AND (sd_coupon.active_from <= UNIX_TIMESTAMP() AND sd_coupon.active_to >= UNIX_TIMESTAMP())
GROUP BY product_id
ORDER BY
  air_day_product_time.begin_datetime DESC,
  super_sort.k_1 DESC";

        $sProducts = \Yii::$app->db->createCommand($sql)->queryAll();

        $sProductsByTree = array();

        if ($sProducts) {

            $this->stdout("Акционных товаров для обновления коэффициентов - " . count($sProducts) . "\n", Console::FG_GREEN);

            //Акционные товары раскладываем по разделам
            foreach ($sProducts as $sProduct) {
                $sProductsByTree[$sProduct['tree_id']][] = $sProduct['product_id'];
            }

            $trees = array_keys($sProductsByTree);

            $this->stdout("Кол-во разделов с обновляемыми товарами - " . count($trees) . "\n", Console::FG_GREEN);

            //Перебираем разделы акционных товаров выбирая товары для постраения постранички и определения коэффициентов
            foreach ($trees as $treeId) {

                //Подмешиваемые товары постранично
                $sTreeProductsByPage = array_chunk($sProductsByTree[$treeId], $productsInjectsNumPerPage);

                //Все товары раздела нам возможно не нужны
                //Выберем то кол-во товаров, на страницы с которыми у нас есть товары для подмеса
                $limit = count($sTreeProductsByPage) * Catalog::INFINITE_PER_PAGE;

                $treeProducts = CmsContentElement::find()
                    ->alias('content_element')
                    ->leftJoin(AirDayProductTime::tableName() . ' AS air_day_product_time',
                        "air_day_product_time.lot_id = content_element.id AND air_day_product_time.begin_datetime >=
                    UNIX_TIMESTAMP(DATE_FORMAT(NOW(), '%Y-%m-%d 08:00:00'))
                ")
                    ->leftJoin(ShopProductStatistic::tableName() . ' AS super_sort',
                        "super_sort.id = content_element.id")
                    ->andWhere(['content_id' => 2, 'tree_id' => $treeId, 'active' => Cms::BOOL_Y])
                    ->select(['content_element.id', 'content_element.name', 'super_sort.k_1'])
                    ->addOrderBy(['air_day_product_time.begin_datetime' => SORT_DESC])
                    ->addOrderBy('super_sort.k_1 DESC')
                    ->asArray()
                    ->limit($limit)
                    ->all();

                //Разбиваем на страницы, записываем коэффициенты
                $treeProductsByPage = array_chunk($treeProducts, Catalog::INFINITE_PER_PAGE);

                foreach ($treeProductsByPage as $pageNum => $pageProducts) {
                    //Проверим, а есть ли товары для подмеса для данного номера страницы
                    if (empty($sTreeProductsByPage[$pageNum])) {
                        continue;
                    }

                    $k_1_max = $pageProducts[0]['k_1'];
                    $k_1_min = $pageProducts[count($pageProducts) - 1]['k_1'];

                    //Генерируем подмешиваемые коеффициенты по числу подмешиваемых товаров для данной страницы
                    for ($productNum = 0; $productNum < min($productsInjectsNumPerPage, count($sTreeProductsByPage[$pageNum])); $productNum++) {
                        $kMultiplier = 10000; // чтобы в рандом приходили целые числа
                        $k_s = round((rand($k_1_min * $kMultiplier, $k_1_max * $kMultiplier)) / $kMultiplier, 5);

                        $productId = $sTreeProductsByPage[$pageNum][$productNum];
                        //Массив с данными для обновления
                        $productsKUpdates[$productId] = $k_s;
                    }
                }
            }

            //Сбор значений для запроса обновление данных
            $queryValues = array();
            foreach ($productsKUpdates as $productId => $k) {
                $queryValues[] = "
		    ({$productId}, {$k})";
            }

            $queryUpdate = \Yii::$app->db->createCommand(
                "INSERT INTO " . ShopProductStatistic::tableName() . " (id, k_1) 
						VALUES " . implode(', ', $queryValues) . "
						ON DUPLICATE KEY UPDATE k_1 = VALUES(`k_1`)"
            );

            $queryUpdate->execute();

            $this->stdout("Коэффициенты обновлены", Console::FG_GREEN . "\n");
        } else {
            $this->stdout("Нет акционных товаров для обновления.", Console::FG_YELLOW) . "\n";
        }

    }

    /**
     * Подмес товаров стока в самый верх товаров разделов сразу за товарами эфира путем обновления коэффициентов
     */
    public function actionUpdateRatioBySegments()
    {
        $updateQueryValues = []; //массив для сборки запроса пакетного обновления

        //Общая логика:
        //Выбираем товары стока
        //Записываем самый максимальный коэффициент товаров каталога
        //Диапазон от МаксКоэф до МаксКоэф+1 делим на кол-во товаров подмеса получая шаг между товарами подмеса,
        //Обновляем коэффициенты у товаров подмеса так что бы они были больше макс коэффициента обычных товаров в разделе.

        $segmetedProducts = SsProductsSegments::find()
            ->alias('products_segments')
            ->select([
                'products_segments.product_id',
                'products_segments.segment',
                'super_sort.k_1 AS k'
            ])
            ->leftJoin(ShopProductStatistic::tableName() . ' AS super_sort', "super_sort.id=products_segments.product_id")
            ->andWhere('products_segments.begin_datetime <= UNIX_TIMESTAMP() AND products_segments.end_datetime >= UNIX_TIMESTAMP()')
            ->orderBy([
                'segment' => SORT_ASC,
                new Expression('rand()')
            ])
            ->asArray()
            ->indexBy('product_id')
            ->all();

        if ($segmetedProducts) {
            $this->stdout("Стоковых товаров для обновления коэффициентов - " . count($segmetedProducts) . "\n", Console::FG_GREEN);

            //Товар с макс коэф для каталога
            $treeProductsAc = CmsContentElement::find()
                ->alias('content_element')
                ->leftJoin(ShopProductStatistic::tableName() . ' AS super_sort',
                    "super_sort.id = content_element.id")
                ->andWhere(['content_id' => 2, 'active' => Cms::BOOL_Y])
                ->select([
                    'content_element.id',
                    'content_element.name',
                    'super_sort.k_1 AS k',
                ])
                ->addOrderBy('super_sort.k_1 DESC')
                ->asArray()
                ->limit(1);

            $catalogProductMaxKSrc = $treeProductsAc->one();
            $catalogProductMaxK = !empty($catalogProductMaxKSrc['k']) ? $catalogProductMaxKSrc['k'] : 0;

            $productsForInjectionNum = count($segmetedProducts);

            $count = $productsForInjectionNum;
            $counter = 0;
            Console::startProgress(0, $count);

            //Новые коэф должны быть в диапазоне от МаксК до МаксК+1.

            //Деля на кол-во товаров получаем ту прибавку которая равномерно распределит товар внутри части под подмес
            $productRatioAddPart = 1 / $productsForInjectionNum;

            //Перебираем товары обновляя коэф
            $curRatio = $catalogProductMaxK;
            foreach ($segmetedProducts as $productId => $product) {
                $counter++;
                Console::updateProgress($counter, $count);

                $curRatio += $productRatioAddPart;
                $newRatio = round($curRatio, 5);

                $updateQueryValues[] = "
		    ({$productId}, {$newRatio})";
            }

            $this->stdout("Сбор данных завершен, обновляю в БД\n", Console::FG_GREEN);

            //Обновляем коэффициенты в бд
            if ($updateQueryValues) {
                $queryUpdate = \Yii::$app->db->createCommand(
                    "INSERT INTO " . ShopProductStatistic::tableName() . " (id, k_stock) 
						VALUES " . implode(', ', $updateQueryValues) . "
						ON DUPLICATE KEY UPDATE k_stock = VALUES(`k_stock`)"
                );

                $productUpdatedNum = $queryUpdate->execute();

                $this->stdout("Обновлено коэффициентов у товаров: {$productUpdatedNum}\n", Console::FG_GREEN);
            }

            $this->stdout("Процесс окончен.\n", Console::FG_GREEN);

        } else {
            $this->stdout("Стоковых товаров не найдено\n", Console::FG_BLUE);
        }

        return;
    }

    /**
     * Устанавливает главным фото первое из дополнительных, если это главное фото не заполнено.
     */
    public function actionSetMainImages($updatePer = null)
    {

        $delayUpdate = 5;

        $this->stdout("Установка главных фото товаров где их нет (из доп фото)" . PHP_EOL, Console::FG_GREEN);

        $contentId = PRODUCT_CONTENT_ID;
        $boolY = Cms::BOOL_Y;

        $sqlSelect = <<<SQL
SELECT 
  content_element.id AS content_element_id, 
  content_image.storage_file_id AS image_id
FROM 
  cms_content_element AS content_element
INNER JOIN (SELECT storage_file_id, content_element_id FROM cms_content_element_image ORDER BY priority) AS content_image 
  ON content_image.content_element_id=content_element.id
WHERE 
  content_id = {$contentId}
  AND active = '{$boolY}'
  AND image_id IS NULL 
  AND name != 'Undefined!!!'
  GROUP BY content_element.id
  ORDER BY content_element.id
SQL;

        if ($products = \Yii::$app->db->createCommand($sqlSelect)->queryAll()) {

            $count = count($products);

            $this->stdout("Товаров без фото для которых фото можно установить = {$count}" . PHP_EOL, Console::FG_GREEN);

            if ($updatePer) {
                $count = $updatePer;
                $this->stdout("Но будет обновлен блок из {$updatePer} записей" . PHP_EOL, Console::FG_GREEN);
            }

            $this->stdout("До запуска обновления {$delayUpdate} сек" . PHP_EOL, Console::FG_GREEN);
            $this->delay($delayUpdate);

            $counterStep = $count / 100; //каждый 1 процент, сколько это в штуках

            $sqlUpdate = <<<SQL
UPDATE cms_content_element SET image_id=:imageId WHERE image_id IS NULL AND id=:contentElementId
SQL;
            $imageId = null;
            $contentElementId = null;

            $updateCommand = \Yii::$app->db->createCommand($sqlUpdate)
                ->bindParam(':imageId', $imageId)
                ->bindParam(':contentElementId', $contentElementId);

            $counterGlobal = 0;
            $counter = 0;
            Console::startProgress(0, $count);
            foreach ($products as $product) {

                $counterGlobal++;
                $counter++;

                if ($counter >= $counterStep || $counterGlobal == $count) {
                    $counter = 0;
                    Console::updateProgress($counterGlobal, $count);
                }

                $contentElementId = $product['content_element_id'];
                $imageId = $product['image_id'];

                $updateCommand->execute();

                if ($updatePer && $counterGlobal == $updatePer) {
                    break;
                }
            }
        } else {
            $this->stdout("Товаров без фото для которых его можно установить не найдено." . PHP_EOL, Console::FG_GREEN);
        }

        $this->stdout("DONE" . PHP_EOL, Console::FG_GREEN);

        return;
    }

    /**
     * Экспорт товаров с их ГУИДами и названиями в файл
     *
     * @return bool
     */
    public function actionExportProductsWithGuids()
    {

        $this->stdout("Экспорт товаров с их GUID'ами" . PHP_EOL, Console::FG_YELLOW);

        $products = CmsContentElement::find()
            ->alias('product')
            ->andWhere(['product.content_id' => PRODUCT_CONTENT_ID])
            ->andWhere(['product.active' => Cms::BOOL_Y])
            ->andWhere(['>', 'product.kfss_id', 0])
            ->innerJoin(Guid::tableName() . ' AS guid', "guid.id=product.guid_id")
            ->select('product.id, product.name, product.code, guid.guid')
            ->all();

        if ($products) {
            $count = count($products);
            $counterStep = $count / 100; //каждый 1 процента, сколько это в штуках

            $this->stdout("Найдено товаров для экспорта - {$count}" . PHP_EOL, Console::FG_CYAN);

            //Экспортируем в файл
            $dir = \Yii::getAlias('@frontend/web/export/');
            $filename = "products_with_guids.csv";
            $fullPath = $dir . $filename;

            $file = fopen($fullPath, 'wb');

            $this->stdout("Экспортирую данные в файл '{$fullPath}'" . PHP_EOL, Console::FG_CYAN);

            if (!$file) {
                $this->stdout("Ошибка при создании файла '{$fullPath}'" . PHP_EOL, Console::FG_RED);
            } else {
                fputcsv($file, ['GUID', 'URL', 'NAME']);

                if ($products) {
                    $counterGlobal = 0;
                    $counter = 0;
                    Console::startProgress(0, $count);
                    /** @var CmsContentElement $product */
                    foreach ($products as $product) {
                        $counterGlobal++;
                        $counter++;

                        if ($counter >= $counterStep || $counterGlobal == $count) {
                            $counter = 0;
                            Console::updateProgress($counterGlobal, $count);
                        }
                        fputcsv($file, [$product->guid, $product->getAbsoluteUrl(), $product->name]);
                    }
                }

                fclose($file);
            }
        } else {
            $this->stdout("Товаров не найдено" . PHP_EOL, Console::FG_CYAN);
        }

        $this->stdout("Готово" . PHP_EOL, Console::FG_GREEN);

        return true;
    }

    /**
     * Экспорт товаров с брендами в названиях в файл
     *
     * @return bool
     */
    public function actionExportProductsWithBrandInName($contentId = 2)
    {
        $contentIds = [Product::LOT, Product::CARD];

        if (!in_array($contentId, $contentIds)){
            $contentId = Product::LOT;
        }

        $this->stdout("Экспорт товаров [{$contentId}] с брендами в названиях" . PHP_EOL, Console::FG_YELLOW);

        $brands = \common\models\CmsContentElement::find()->select('name')->where(['content_id' => 193])->column();

        $products = [];

        if ($brands){
            $count = count($brands);
            $counterStep = $count / 100; //каждый 1 процента, сколько это в штуках

            $this->stdout("Найдено брендов для проверки - {$count} [step={$counterStep}]" . PHP_EOL, Console::FG_CYAN);

            $counterGlobal = 0;
            $counter = 0;
            Console::startProgress(0, $count);

            $i=0;
            foreach ($brands as $brand) {
                $i++;

                $counterGlobal++;
                $counter++;

                if ($counter >= $counterStep || $counterGlobal == $count) {
                    $counter = 0;
                    Console::updateProgress($counterGlobal, $count);
                }

                $brandProductsQuery = Product::find()
                    //->select('id, name, code')
                    ->where(['cms_content_element.content_id' => $contentId])
                    ->andWhere(['like', 'cms_content_element.name', $brand]);

                if ($contentId == Product::LOT){
                    $brandProductsQuery->select('cms_content_element.id, cms_content_element.name, cms_content_element.code');
                }else{
                    $brandProductsQuery->innerJoin(Product::tableName() . ' AS lot', "lot.id=cms_content_element.parent_content_element_id");
                    $brandProductsQuery->select('cms_content_element.id, cms_content_element.name, lot.code');

                    $brandProductsQuery->andWhere(['lot.content_id' => Product::LOT]);
                }

                $brandProducts = $brandProductsQuery->asArray()->all();

                if ($brandProducts){
                    foreach ($brandProducts as $brandProduct) {
                        $products[] = [
                            'product_id' => $brandProduct['id'],
                            'lot_num' => $brandProduct['code'],
                            'brand' => $brand,
                            'name' => $brandProduct['name'],
                        ];
                    }
                }

//                if ($i > 30){
//                    break;
//                }
            }

            $this->stdout("Brands done" . PHP_EOL, Console::FG_GREEN);

            if ($products){
                $count = count($products);
                $counterStep = $count / 100; //каждый 1 процента, сколько это в штуках

                $this->stdout("Найдено товаров для экспорта - {$count}" . PHP_EOL, Console::FG_CYAN);

                //Экспортируем в файл
                $dir = \Yii::getAlias('@frontend/web/export/');
                $filename = "products_with_brands_in_name.csv";
                $fullPath = $dir . $filename;

                $file = fopen($fullPath, 'wb');

                $this->stdout("Экспортирую данные в файл '{$fullPath}'" . PHP_EOL, Console::FG_CYAN);

                if (!$file) {
                    $this->stdout("Ошибка при создании файла '{$fullPath}'" . PHP_EOL, Console::FG_RED);
                } else {
                    if ($products) {
                        $counterGlobal = 0;
                        $counter = 0;
                        Console::startProgress(0, $count);
                        /** @var CmsContentElement $product */
                        foreach ($products as $product) {
                            $counterGlobal++;
                            $counter++;

                            if ($counter >= $counterStep || $counterGlobal == $count) {
                                $counter = 0;
                                Console::updateProgress($counterGlobal, $count);
                            }
                            fputcsv($file, [$product['product_id'], $product['lot_num'], $product['brand'], $product['name']]);
                        }
                    }

                    fclose($file);
                }
            }else{
                $this->stdout("Товаров не найдено" . PHP_EOL, Console::FG_CYAN);
            }
        }else{
            $this->stdout("Брендов не найдено" . PHP_EOL, Console::FG_CYAN);
        }

        $this->stdout("Готово" . PHP_EOL, Console::FG_GREEN);

        return true;
    }

    //Из-за приходящих файлов с расширением картинок в верхнем регистре имеем проблему в карточке товара

    /** Переводит расширение картинок в нижний регистр в файлах и БД
     *
     * @param int $limit
     * @return bool
     */
    public function actionFixImageExtentions($limit = 1)
    {
        $extBad = 'JPG';
        $extGood = 'jpg';

        $files = \modules\shopandshow\models\common\StorageFile::find()
            ->andWhere("BINARY extension='{$extBad}'") //Ищем фотки с расширением в верхнем регистре
            ->orderBy(['id' => SORT_DESC]) //В первую очередь фиксим свежак
            ->limit((int)$limit);

        echo "Фикс расширений картинок в верхнем регистре. Найдено элементов (всего): " . $files->count() . PHP_EOL;
        echo "Обновляю блок элементов (шт): {$limit}" . PHP_EOL;

        if ($files){
            /** @var \modules\shopandshow\models\common\StorageFile $file */
            $i=0;
            foreach ($files->each() AS $file) {
                $i++;
                echo "[{$i}]---------" . PHP_EOL;
                $fileSrcOld = $file->src;
                $fileSrcNew = preg_replace('/'.$extBad.'$/', $extGood, $fileSrcOld);
                $file->cluster_file = preg_replace('/'.$extBad.'$/', $extGood, $file->cluster_file);
                $file->extension = $extGood;

                $filePathOld = ROOT_DIR . '/frontend/web' . $fileSrcOld;
                $filePathNew = ROOT_DIR . '/frontend/web' . $fileSrcNew;

                echo "[{$file->id}] {$filePathOld} -> {$filePathNew}" . PHP_EOL;
                echo "[{$file->id}] {$fileSrcOld} -> {$fileSrcNew}" . PHP_EOL;

                $renameErr = '';
                $isRenamed = false;
                try {
                    $isRenamed = rename($filePathOld, $filePathNew);
                } catch (\Exception $e) {
                    $renameErr = $e->getMessage() . PHP_EOL;
                }

                if (!empty($isRenamed)){
                    //Соответственно обновляем данные в БД
                    if (!$file->save()){
                        echo ">> SaveDB Error! Text: " . var_export($file->getErrors(), true) . PHP_EOL;
                    }else{
                        echo "RenameDbResult: " . 1 . PHP_EOL;
                    }

                    //Чистим превью
                    $file->deleteTmpDir();
                }else{
                    echo ">> Rename Error! Text: " . $renameErr . PHP_EOL;
                }

                echo "RenameFileResult: " . (string)$isRenamed . PHP_EOL;
            }
        }

        return true;
    }

    /** Удаление превью для файлов с расширением в верхнем регистре
     *
     * @param int $limit
     * @return bool
     */
    public function actionDeleteThumbsWithUppercaseExtention($limit = 1)
    {
        $extBad = 'JPG';

        $files = \modules\shopandshow\models\common\StorageFile::find()
            ->andWhere("BINARY original_name REGEXP '\.{$extBad}$'") //Ищем фотки с расширением в верхнем регистре
            ->orderBy(['id' => SORT_DESC]) //В первую очередь фиксим свежак
            ->limit((int)$limit);

        echo "Фикс превью картинок в верхнем регистре. Найдено элементов (всего): " . $files->count() . PHP_EOL;
        echo "Обновляю блок элементов (шт): {$limit}" . PHP_EOL;

        if ($files){
            /** @var \modules\shopandshow\models\common\StorageFile $file */
            $i=0;
            foreach ($files->each() AS $file) {
                $i++;
                echo "{$i} [{$file->id}] {$file->src}" . PHP_EOL;

                //Чистим превью
                try {
                    $file->deleteTmpDir();
                } catch (\Exception $e) {
                    echo "ERROR! " .var_export($e->getMessage(), true) . PHP_EOL;
                }

            }
        }
        echo "Done" . PHP_EOL;

        return true;
    }
}



