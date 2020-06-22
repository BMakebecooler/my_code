<?php


namespace common\helpers;


use common\components\cache\PageCache;
use common\models\CmsTree;
use common\models\Tree;

class Category
{
    const COUNT_TREE_IDS_COLLAPSED = 4;

    public static $rootCategoryId = 9;

    public static $modaCategoryIds = [1626, 2672];//категории мода

    public static $footwearCategoryIds = [1649, 2227, 2761];//категории обувь

    public static $jewelryCategoryIds = [1623, 2226, 2243, 2263];//украшения

    public static $kitchenCategoryIds = [1931, 2241, 2321];

    public static $beautyCategoryIds = [2855];//категории красота

    public static $interiorCategoryIds = [2406];//категории интерьер

    public static $electricalCategoryIds = [1937, 2586];//категории электроника

    public static $homeCategoryIds = [2645];//категории товары товары для дома

    public static $gardenCategoryIds = [2501];//категории сад и огород

    public static $hobbyCategoryIds = [2549];//категории хобби

    public static $maxDiscountCategories = [2100, 2103, 2106, 2109, 2112, 2115, 2118, 2121];

    public static $misunderstoodIds = [3083, 3091, 3120, 3122];

    public static function getIdsCategoriesByPid($pid)
    {
        $return = [];
        $parentCats = Tree::find()
            ->select([
                'id'
            ])
            ->andWhere(['active' => 'Y'])
            ->andWhere(['pid' => $pid])
            ->asArray()
            ->all();

        foreach ($parentCats as $row) {
            $return[] = $row['id'];
        }

        return count($return) ? $return : null;

    }

    public static function losePopularityAllCategories()
    {
        $sql = "UPDATE " . CmsTree::tableName() . " SET popularity = 1 WHERE 1=1";
        \Yii::$app->db->createCommand($sql, [])->query();
    }

    public static function isMaxDiscountType($treeId)
    {
        return in_array($treeId, self::$maxDiscountCategories);
    }

    private static function checkInCategories($model, $categories)
    {
        $flag = false;
        if (in_array($model->id, $categories)) {
            $flag = true;
        }

        foreach ($model->pids as $pid) {
            if (in_array($pid, $categories)) {
                $flag = true;
            }
        }
        return $flag;
    }

    public static function checkIsElectrical($model)
    {
        return static::checkInCategories($model, static::$electricalCategoryIds);
    }

    public static function checkIsKitchen($model)
    {
        return static::checkInCategories($model, static::$kitchenCategoryIds);
    }

    public static function checkIsHobby($model)
    {
        return static::checkInCategories($model, static::$hobbyCategoryIds);
    }

    public static function checkIsGarden($model)
    {
        return static::checkInCategories($model, static::$gardenCategoryIds);
    }

    public static function checkIsHome($model)
    {
        return static::checkInCategories($model, static::$homeCategoryIds);
    }

    public static function checkIsBeauty($model)
    {
        return static::checkInCategories($model, static::$beautyCategoryIds);
    }

    public static function checkIsInterior($model)
    {
        return static::checkInCategories($model, static::$interiorCategoryIds);
    }

    public static function checkIsModa($model)
    {
        return static::checkInCategories($model, static::$modaCategoryIds);
    }

    public static function checkIsFootwear($model)
    {
        return static::checkInCategories($model, static::$footwearCategoryIds);
    }

    public static function checkIsJewelry($model)
    {
        return static::checkInCategories($model, static::$jewelryCategoryIds);
    }

    public static function getIdsTableSizes()
    {
        $return = \Yii::$app->cache->get('ids_table_sizes');

        if ($return === false) {

            $returnOld = Size::$dataTableSizes;

            $return = [
                'sizeClothes' => [
                    2728
                ],
                'womenShoes' => [
                    2777
                ],
                'ringsSizes' => [
                    2287,
                    2301,
                    3100,
                ],
                'menShoes' => [
                    2808
                ],
                'babyShoes' => [
                    2682
                ],
                'manClothes' => [
                    2705
                ],
            ];

            $sizeClothes = CmsTree::getChildrenIds($return['sizeClothes'][0]);
            foreach ($sizeClothes as $id) {
                $return['sizeClothes'][] = $id;
            }
            $womenShoes = CmsTree::getChildrenIds($return['womenShoes'][0]);
            foreach ($womenShoes as $id) {
                $return['womenShoes'][] = $id;
            }
            $menShoes = CmsTree::getChildrenIds($return['menShoes'][0]);
            foreach ($menShoes as $id) {
                $return['menShoes'][] = $id;
            }
            $babyShoes = CmsTree::getChildrenIds($return['babyShoes'][0]);
            foreach ($babyShoes as $id) {
                $return['babyShoes'][] = $id;
            }
            $manClothes = CmsTree::getChildrenIds($return['manClothes'][0]);
            foreach ($manClothes as $id) {
                $return['manClothes'][] = $id;
            }

            foreach ($returnOld as $groupName => $data) {
                $return[$groupName] = array_unique(array_merge($return[$groupName], $data));
            }

            \Yii::$app->cache->set('ids_table_sizes', $return, PageCache::CACHE_DURATION);
        }

        return $return;
    }

    public static function isCategory($model)
    {
        if ($model instanceof \common\models\Tree || $model instanceof \common\models\CmsTree) {
            return true;
        } else {
            return false;
        }
    }

    public static function sort($categories)
    {

        usort($categories, function ($item1, $item2) {
            if ($item2['priority'] == $item1['priority']) {
                return $item2['popularity'] <=> $item1['popularity'];
            } else {
                return $item2['priority'] <=> $item1['priority'];
            }

        });

        return $categories;
    }


}