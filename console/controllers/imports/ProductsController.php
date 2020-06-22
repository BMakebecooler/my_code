<?php

/**
 * php ./yii imports/products/import-descriptions-from-file
 * php ./yii imports/products/import-segmentation-from-file
 */

namespace console\controllers\imports;

use common\helpers\Strings;
use common\models\cmsContent\CmsContentElement;
use skeeks\cms\models\CmsContentElementProperty;
use yii\helpers\Console;

/**
 * Class ProductsController
 * @package console\controllers
 */
class ProductsController extends \yii\console\Controller
{

    public $propDescrId;

    public function init()
    {
        parent::init();

        $propDescrIdSrc = \common\models\cmsContent\CmsContentProperty::find()->where(['code' => 'PREIMUSHESTVA_ADDONS'])->column();
        $this->propDescrId = $propDescrIdSrc ? current($propDescrIdSrc) : 0;
    }

    /**
     * Импорт описаний товаров из файла
     */
    public function actionImportDescriptionsFromFile()
    {
        $file = __DIR__ . '/files/products_descrs.csv';

        $descrs = [];

        $this->stdout("Начинаем сбор данных из файла: $file\n", Console::FG_YELLOW);

        if (!file_exists($file)) {
            $this->stdout("Файл '$file' не найден\n", Console::FG_RED);
        } else {
            $rows = file($file);

            $this->stdout("Товаров для обновления: " . count($rows) . "\n", Console::FG_YELLOW);

            foreach ($rows as $row) {
                if (empty($row)) {
                    continue;
                }

                //Ищем ID лота
                preg_match("/^([\d\s-\[\]]+)\s*;/U", $row, $lotMatches);

                //чистим если нашелся
                $bitrixId = !empty($lotMatches['1']) ? (int)Strings::onlyInt($lotMatches[1]) : '';

                //Выбираем текст
                $descr = Strings::trim(preg_replace("/^.*;/U", '', $row));

                //Если не указано что либо - ошибка, пропускаем
                if (empty($bitrixId) || empty($descr)) {
                    $this->stdout("С товаром что то не так, данные: $row\n", Console::FG_RED);
                    continue;
                }

                //Проверяем на возможные дубликаты обновления одних и тех же товаров
                if (!isset($descrs[$bitrixId])) {
                    $descrs[$bitrixId] = $descr;
                } else {
                    $this->stdout("Дубль! Данные: $row\n", Console::FG_RED);
                }
            }

            $this->stdout("Товаров для обновления после проверок: " . count($descrs) . "\n", Console::FG_GREEN);
        }

        $this->loadProductsDescrs($descrs);

        $this->stdout("Импорт закончен!\n", Console::FG_YELLOW);

        return;
    }

    /**
     * Обновления свойства Описание (PREIMUSHESTVA) товаров
     *
     * @param array $descrs
     * @return bool
     */
    protected function loadProductsDescrs(array $descrs)
    {
        if (!$descrs) {
            return false;
        }

        $this->stdout("Начинаем импорт полученных данных в БД \n", Console::FG_YELLOW);
        $count = sizeof($descrs);
        $counter = 0;
        Console::startProgress(0, $count);

        if ($this->propDescrId) {
            foreach ($descrs as $bitrixId => $descr) {

                $counter++;
                Console::updateProgress($counter, $count);
                $contentElement = CmsContentElement::findOne(['content_id' => PRODUCT_CONTENT_ID, 'bitrix_id' => $bitrixId]);

                if (!$contentElement) {
                    continue;
                }

                $elPropDescr = CmsContentElementProperty::find()
                    ->where(['property_id' => $this->propDescrId, 'element_id' => $contentElement->id])
                    ->one();

                if ($elPropDescr) {
                    $descrOld = $elPropDescr->value;
                } else {
                    $descrOld = '';
                    //Create elPropDescr
                    $elPropDescr = new CmsContentElementProperty();
                    $elPropDescr->property_id = $this->propDescrId;
                    $elPropDescr->element_id = $contentElement->id;
                }

                $descrNew = (!empty($descrOld) ? $descrOld . "\n\n" : '') . $descr;
                $elPropDescr->value = $descrNew;

                if (!$elPropDescr->save()) {
                    var_dump($elPropDescr->getErrors());
                }
            }
        }

        return true;
    }

    /**
     * Импорт сегментации товаров
     */
    public function actionImportSegmentationFromFile()
    {
        $file = __DIR__ . '/files/lots_and_segments.csv';

        $products = [];

        $this->stdout("Начинаем сбор данных из файла: $file\n", Console::FG_YELLOW);

        if (!file_exists($file)) {
            $this->stdout("Файл '$file' не найден\n", Console::FG_RED);
        } else {
            $rows = file($file);

            $this->stdout("Товаров для обновления (предварительно): " . count($rows) . "\n", Console::FG_YELLOW);

            foreach ($rows as $row) {
                if (empty($row)) {
                    continue;
                }

                $items = explode(',', $row);

                // нет второго итема => ошибка
                if (count($items) < 2) {
                    continue;
                }

                list($bitrixId, $segment) = $items;

                $bitrixId = trim($bitrixId);
                $segment = trim($segment);

                //Проверяем на возможные дубликаты обновления одних и тех же товаров
                if (!isset($products[$bitrixId])) {
                    $products[$bitrixId] = $segment;
                } else {
                    //Дубль
                    //$this->stdout("Дубль! Данные: $row\n", Console::FG_RED);
                }
            }

            $this->stdout("Товаров для обновления (после дедупликации): " . count($products) . "\n", Console::FG_GREEN);
        }

        $productsInsertedNum = 0;

        if ($products) {
            $productsInsertedNum = $this->loadProductsSegmentation($products);
        }

        $this->stdout("Импорт закончен! Импортировано записей: {$productsInsertedNum}\n", Console::FG_YELLOW);

        return;
    }

    /**
     * Загрузка данных сегментации продуктов в БД
     *
     * @param $products
     * @return int
     */
    private function loadProductsSegmentation($products)
    {
        ksort($products);

        $bitrixMap = \common\lists\Contents::getIdsByBitrixIds(array_keys($products));

        $this->stdout("Собираю список товаров для пакетной вставки\n", Console::FG_YELLOW);

        $count = sizeof($products);
        $counter = 0;
        Console::startProgress(0, $count);

        $batchInsert = [];

        foreach ($products as $bitrixId => $segment) {
            $counter++;
            Console::updateProgress($counter, $count);

            if (isset($bitrixMap[$bitrixId])) {
                $productId = $bitrixMap[$bitrixId];

                $product = [
                    'product_id' => $productId,
                    'bitrix_id' => $bitrixId,
                    'segment' => $segment,
                ];

                $batchInsert[] = $product;
            }
        }

        $this->stdout("Выполняю запрос вставки\n", Console::FG_YELLOW);

        $productsInsertedNum = \Yii::$app->db->createCommand()
            ->batchInsert("{{ss_products_segments}}", ['product_id', 'bitrix_id', 'segment'], $batchInsert)
            ->execute();

        return $productsInsertedNum;
    }
}