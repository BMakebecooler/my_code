<?php


namespace common\helpers;


use common\models\Brand as BrandModel;
use common\models\BrandPopularityByTree;
use common\models\BUFECommBrand;
use common\models\CmsTree;
use common\models\generated\models\SsGuids;
use yii\db\Expression;

class Brand
{
    public static function syncBrandPopularityByTree()
    {
        if (App::isConsoleApplication()) {
            echo "Синхронизирую популярность брендов по разделам" . PHP_EOL;
        }

        //Корневые разделы аналитики
        $brandsByTreeRootQuery = BUFECommBrand::find()
            ->select([
                'tree_guid' => 'g1',
                'brand_guid' => 'b',
                new Expression("SUM(Rub) AS popularity"),
            ])
            ->andWhere(['not', ['b' => null]])
            ->groupBy([
                'g1', 'b'
            ])
            ->orderBy(['popularity' => SORT_DESC])
            ->asArray();

        $brandsByTreeRoot = $brandsByTreeRootQuery->all();

        //Не корневые разделы аналитики (пока есть только нижний уровень)
        $brandsByTreeDeepQuery = BUFECommBrand::find()
            ->select([
                'tree_guid' => 'g4',
                'brand_guid' => 'b',
                new Expression("SUM(Rub) AS popularity"),
            ])
            ->andWhere(['!=', 'g1', 'g4'])
            ->andWhere(['not', ['b' => null]])
            ->groupBy([
                'g4', 'b'
            ])
            ->orderBy(['popularity' => SORT_DESC])
            ->asArray();

        $brandsByTreeDeep = $brandsByTreeDeepQuery->all();

//        $catalogTreeIds = TreeList::getDescendantsById(TREE_CATEGORY_ID_CATALOG);
        $treeIdByGuid = CmsTree::find()
            ->alias('tree')
            ->select(['tree_id' => 'tree.id', 'guids.guid'])
            ->innerJoin(SsGuids::tableName() . ' AS guids', "guids.id=tree.guid_id")
//            ->andWhere(['tree.id' => $catalogTreeIds])
            ->andWhere(['like', 'tree.pids', '1/9%', false])
            ->indexBy('guid')
            ->column();

        $brandIdByGuid = \common\models\Brand::find()
            ->select(['brand_id' => 'cms_content_element.id', 'guids.guid'])
//            ->byContent(\common\models\Brand::CONTENT_ID) //Подставляется автоматок при поиске через класс Бренда
            ->innerJoin(SsGuids::tableName() . ' AS guids', "guids.id=cms_content_element.guid_id")
            ->andWhere(['>', 'guid_id', 0])
            ->indexBy('guid')
            ->column();

        if (App::isConsoleApplication()) {
            echo "Корневых разделов/брендов - " . count($brandsByTreeRoot) . PHP_EOL;
            echo "Вложенных разделов/брендов - " . count($brandsByTreeDeep) . PHP_EOL;
            echo "Разделов в каталоге - " . count($treeIdByGuid) . PHP_EOL;
            echo "Брендов - " . count($brandIdByGuid) . PHP_EOL;
        }

        $batchInsert = [];

        if ($brandsByTreeRoot) {

            if (App::isConsoleApplication()) {
                echo "Добавляю бренды корневых разделов." . PHP_EOL;
            }

            foreach ($brandsByTreeRoot as $brand) {

                $brandId = $brandIdByGuid[$brand['brand_guid']] ?? null;
                $treeId = $treeIdByGuid[$brand['tree_guid']] ?? null;

//                echo "brandGuid = {$brand['brand_guid']}" . PHP_EOL;
//                echo "treeGuid = {$brand['tree_guid']}" . PHP_EOL;
//                echo "brandId = {$brandId}" . PHP_EOL;
//                echo "treeId = {$treeId}" . PHP_EOL;

                if ($brandId && $treeId) {
                    $batchInsert[] = [$brandId, $treeId, $brand['popularity']];
                }
            }

            if (App::isConsoleApplication()) {
                echo "Записей для вставки: " . count($batchInsert) . PHP_EOL;
            }
        }

        if ($brandsByTreeDeep) {

            if (App::isConsoleApplication()) {
                echo "Добавляю бренды вложенных разделов." . PHP_EOL;
            }

            foreach ($brandsByTreeDeep as $brand) {
                $brandId = $brandIdByGuid[$brand['brand_guid']] ?? null;
                $treeId = $treeIdByGuid[$brand['tree_guid']] ?? null;

                if ($brandId && $treeId) {
                    $batchInsert[] = [$brandId, $treeId, $brand['popularity']];
                }
            }

            if (App::isConsoleApplication()) {
                echo "Записей для вставки: " . count($batchInsert) . PHP_EOL;
            }
        }

        if ($batchInsert) {
            BrandPopularityByTree::deleteAll();
            $affected = \Yii::$app->db->createCommand()->batchInsert(BrandPopularityByTree::tableName(), ['brand_id', 'tree_id', 'popularity'], $batchInsert)->execute();

            if (App::isConsoleApplication()) {
                echo "Вставлено записей - {$affected}" . PHP_EOL;
            }
        } else {
            if (App::isConsoleApplication()) {
                echo "Нет данных для вставки" . PHP_EOL;
            }
        }

        if (App::isConsoleApplication()) {
            echo "Done" . PHP_EOL;
        }

        return true;
    }

    public static function prepareToApi($brandsData, $mode = BrandModel::LITERAL_MODE, $count = BrandModel::COUNT_DEFAULT)
    {
        $return = [];

        $return['categories'] = [];
        $return['brands'] = [];

        $countProductsData = \common\models\Product::getCardsCountByBrand(true);

        switch ($mode) {

            case BrandModel::LITERAL_MODE:

                $idCategoryCyrillic = null;
                $idCategoryDigit = null;
                $idCategoryReturn = 1;


                //Буквы латинского алфавита
                if (isset(BrandModel::$literalCategories['latin'])) {
                    $latAlphabet = str_split(BrandModel::$literalCategories['latin']);

                    foreach ($latAlphabet as $letter) {
                        self::addApiDataCategory($return, $idCategoryReturn, $letter);
                        $idCategoryReturn++;
                    }
                }

                //Буквы кириллические
                if (isset(BrandModel::$literalCategories["cyrillic"])) {
                    $idCategoryCyrillic = $idCategoryReturn;
                    self::addApiDataCategory($return, $idCategoryReturn, BrandModel::$literalCategories["cyrillic"]);
                    $idCategoryReturn++;
                }

                //цифры
                if (isset(BrandModel::$literalCategories["digit"])) {
                    $idCategoryDigit = $idCategoryReturn;
                    self::addApiDataCategory($return, $idCategoryReturn, BrandModel::$literalCategories["digit"]);
                }

                foreach ($brandsData as $brand) {
                    $firstChar = mb_strtoupper(ltrim(rtrim($brand->name)))[0];

                    if (preg_match("/^[а-я]+$/i", $firstChar)) {
                        if (isset($return['categories'][$idCategoryCyrillic])) {
                            self::addApiDataItem($return, $brand, $idCategoryCyrillic, $count, $countProductsData);
                        }
                    } elseif (preg_match("/^[0-9]+$/i", $firstChar)) {
                        if (isset($return['categories'][$idCategoryDigit])) {
                            self::addApiDataItem($return, $brand, $idCategoryDigit, $count, $countProductsData);
                        }
                    } else {
                        foreach ($return['categories'] as $category) {
                            if ($category['name'] == $firstChar) {
                                self::addApiDataItem($return, $brand, $category['id'], $count, $countProductsData);
                            }
                        }
                    }
                }
                break;
            case BrandModel::CATALOG_MODE:
                break;
        }

        //удаляем категории, для которых не найдено брендов
        $tmp = $return['categories'];
        $return['categories'] = [];
        foreach ($tmp as $category) {
            if ($category['count']) {
                $return['categories'][] = $category;
            }
        }

        return $return;

    }

    private static function addApiDataCategory(&$return, $idCategoryReturn, $name)
    {
        $return['categories'][$idCategoryReturn] = [
            "id" => $idCategoryReturn,
            'name' => $name,
//            'count_return' => 0,
            'count' => 0
        ];
    }


    private static function addApiDataItem(&$return, $brand, $idCategory, $count, $countProductsData)
    {
//        $return['categories'][$idCategory]['count']++;

        $flag = false;
        if ($count && $return['categories'][$idCategory]['count'] < $count) {
            $flag = true;
        }
        if (!$count) {
            $flag = true;
        }

        $countProducts = $countProductsData[$brand->id] ?? 0;

        if ($flag && $countProducts) {
            $return['brands'][] = [
                "id" => $brand->id,
                "name" => $brand->name,
                "category" => $idCategory,
                "url" => $brand->getUrl(),
                "countProducts" => $countProducts
            ];
            $return['categories'][$idCategory]['count']++;
        }

    }

}