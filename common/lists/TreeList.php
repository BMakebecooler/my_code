<?php

/**
 * Created by PhpStorm.
 * User: ubuntu5
 * Date: 28.03.17
 * Time: 13:22
 */

namespace common\lists;

use common\models\CmsTree;
use common\models\SavedFilter;
use common\models\Tree;
use skeeks\cms\components\Cms;
use skeeks\cms\models\CmsTreeProperty;
use skeeks\cms\models\CmsTreeTypeProperty;
use skeeks\cms\models\Tree as SXTree;
use Yii;

class TreeList
{

    const PROPERTY_CODE_TREE_SAVED_FILTER = 'savedFilter';
    const PROPERTY_CODE_TREE_SAVED_FILTERS = 'savedFilters';

    /**
     * ИД корневого раздела каталога
     */
    const CATALOG_ID = 9;

    /**
     * ИД корневого раздела украшений
     */
    const UKRASHENIYA_ID = 1623;

    /**
     * @param $code
     * @return SXTree
     */
    public static function getTreeByCode($code)
    {
        return SXTree::getDb()->cache(function () use ($code) {
            return SXTree::findOne(['code' => $code]);
        }, HOUR_1);
    }

    /**
     * @param $dir
     * @param $active
     * @return SXTree
     */
    public static function getTreeByDir($dir, $active = true)
    {
        return SXTree::getDb()->cache(function () use ($dir, $active) {
            $params = ['dir' => $dir];
            if ($active) {
                $params['active'] = Cms::BOOL_Y;
            }
            return SXTree::findOne($params);
        }, HOUR_1);
    }

    /**
     * @param $id
     * @return SXTree
     */
    public static function getTreeById($id)
    {
        return Tree::getDb()->cache(function () use ($id) {
            return Tree::findOne(['id' => $id]);
        }, HOUR_1);
    }

    /**
     * @param $bitrixId
     * @return SXTree
     */
    public static function getTreeByBitrixId($bitrixId)
    {
        return Tree::findOne(['bitrix_id' => $bitrixId]);
    }

    /**
     * @param $bitrixId
     * @return int
     */
    public static function getIdTreeByBitrixId($bitrixId)
    {
        if ($tree = self::getTreeByBitrixId($bitrixId)) {
            return $tree->id;
        }

        return null;
    }

    /**
     * @param $code
     * @return int|null
     */
    public static function getIdTreeByCode($code)
    {
        if ($tree = self::getTreeByCode($code)) {
            return $tree->id;
        }

        return null;
    }

    /**
     * @param $dir
     * @return int|null
     */
    public static function getIdTreeByDir($dir)
    {
        if ($tree = self::getTreeByDir($dir)) {
            return $tree->id;
        }

        return null;
    }

    /**
     * Получить всех потомков по имени родителя
     * @param $name
     * @return array
     */
    public static function getDescendantsByCode($code)
    {
        if ($tree = self::getTreeByCode($code)) {
            return self::getDescendants($tree);
        }

        return [];
    }


    /**
     * Получить всех потомков по ид родителя
     * @param $id
     * @return array
     */
    public static function getDescendantsById($id)
    {
        if ($tree = self::getTreeById($id)) {
            return self::getDescendants($tree);
        }

        return [];
    }

    /**
     * @param SXTree $tree
     * @return mixed
     */
    public static function getDescendants(SXTree $tree)
    {
        $keyCache = $tree->name . '_' . __CLASS__ . '_' . __FUNCTION__;
        $cache = Yii::$app->cache;
//        $cache->delete($keyCache);

        $descendants = $cache->getOrSet($keyCache, function () use ($tree) {

            $treeIds = $tree->getDescendants()->select(['id'])->indexBy('id')->asArray()->all();

            if ($treeIds) {
                return array_keys($treeIds);
            } else {
                return [$tree->id];
            }


        }, HOUR_1);

        return $descendants;
    }

    /**
     * @param int $tree_i
     * @return array
     */
    public static function getParentsIdsByTreeId($tree_id)
    {
        $data = CmsTree::find()
            ->select(['pids'])
            ->where(['id' => $tree_id])
            ->asArray()
            ->one();

        $return = isset($data) ? explode('/', $data['pids']) : [];
        $return[$tree_id] = $tree_id;
        return $return;

    }

    /**
     * @param $code
     * @return CmsTreeTypeProperty
     */
    public static function getPropertyTypeByCode($code)
    {
        return CmsTreeTypeProperty::getDb()->cache(function () use ($code) {
            return CmsTreeTypeProperty::findOne(['code' => $code]);
        }, HOUR_1);
    }

    /**
     * @param $code
     * @return int|null
     */
    public static function getPropertyIdByCode($code)
    {
        if ($property = self::getPropertyTypeByCode($code)) {
            return $property->id;
        }

        return null;
    }


    /**
     * @param SXTree $tree
     * @return static[]
     */
    public static function getSavedFiltersBySavedPropertyMenu($tree)
    {
        $savedFiltersMenu = $tree->relatedPropertiesModel->getAttribute(self::PROPERTY_CODE_TREE_SAVED_FILTERS);

        $propertyId = self::getPropertyIdByCode(self::PROPERTY_CODE_TREE_SAVED_FILTER);

        $cmsTreeProperty = CmsTreeProperty::find()
            ->select('value')->where([
                'element_id' => array_values($savedFiltersMenu),
                'property_id' => $propertyId,
            ]);

        return SavedFilter::findAll(['id' => $cmsTreeProperty]);
    }

    /**
     * Получить баннер для каталога
     * @param $treeId
     * @return array|bool
     */
    public static function getCatalogBannerById($treeId)
    {
        /**
         * @var $tree Tree
         */

        $tree = self::getTreeById($treeId);

        if (!$tree) {
            return false;
        }

        $image = $tree->relatedPropertiesModel->getAttribute('catalogBanner');
        $link = $tree->relatedPropertiesModel->getAttribute('catalogBannerLink');

        if (!$image && !$link && $tree->level > 2) {
            $trees = $tree->getParents()
                ->andWhere('cms_tree.id > :catalog_id', [':catalog_id' => self::CATALOG_ID])
                ->orderBy('cms_tree.id DESC')->all();

            foreach ($trees as $tree) {

                $image = $tree->relatedPropertiesModel->getAttribute('catalogBanner');
                $link = $tree->relatedPropertiesModel->getAttribute('catalogBannerLink');

                if ($image && $link) {
                    break;
                }
            }

        } elseif (!$image && !$link && $tree->level == 2) {
            $image = $tree->relatedPropertiesModel->getAttribute('catalogBanner');
            $link = $tree->relatedPropertiesModel->getAttribute('catalogBannerLink');
        }

        if (!$image && !$link) {
            return false;
        }

        return [
            'image' => $image,
            'link' => $link,
        ];
    }

    /**
     * Получить список ювелирных категорий
     * @return array
     */
    public static function getJewelryList()
    {
        $ukrasheniya = self::getDescendantsByCode('ukrasheniya');

        return array_merge([
            self::UKRASHENIYA_ID
        ], $ukrasheniya);
    }

    /**
     * Получить список всех категорий кроме ювелирки
     * @return array
     */
    public static function getNotJewelryList()
    {
        $catalogTree = Tree::findOne(['code' => 'catalog']);

        $all = $catalogTree->getDescendants()->select(['id'])
            ->andWhere(['NOT', ['cms_tree.id' => self::getJewelryList()]])
            ->andWhere(['NOT', ['cms_tree.id' => self::UKRASHENIYA_ID]])
            ->indexBy('id')
            ->asArray()
            ->all();

        return ($all) ? array_keys($all) : [];
    }
}