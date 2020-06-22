<?php
/**
 * Created by PhpStorm.
 * User: andrei
 * Date: 2019-03-24
 * Time: 21:07
 */

namespace common\models;


use yii\base\Object;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

class TreeFactory extends Object
{

    const BOOL_Y = "Y";
    const BOOL_N = "N";

    public static $useRubricator = false;

    public $modelClass = CmsTree::class;
    public $rootCategory = TREE_CATEGORY_ID_CATALOG;
    public $rootRubricator = TREE_CATEGORY_ID_RUBRICATOR;
    public $level;
    public $typeIds = [CATALOG_TREE_TYPE_ID, RUBRICATOR_TREE_TYPE_ID];
    public $enabledCache = self::BOOL_N;
    public $onlyActive = true;
    public $orderBy = "priority";
    public $order = SORT_DESC;
    public $items = [];
    public $categories = [];

    /**
     * Добавить условие выборки разделов, только текущего сайта
     * @var string
     */
    public $enabledCurrentSite = self::BOOL_Y;

    /**
     * @return TreeFactory
     */
    public static function create(): TreeFactory
    {
        return new static();
    }

    /**
     * Получаем все категории и стоим на онове их (списка категорий) рекурсивно дерево
     * @return array
     */
    public static function make(): array
    {
        return static::create()
            ->findAll()
            ->build();
    }

    /**
     * Строим дерево категорий
     * @return array
     */
    public function build(): array
    {
//            $return = $this->buildTree($this->items);
        $return = $this->buildTree($this->items, self::$useRubricator ? TREE_CATEGORY_ID_RUBRICATOR : TREE_CATEGORY_ID_CATALOG);
        return $return;
//            return $this->sortByPopularity($return);
    }

    protected function sortByPopularityItem($tree): array
    {
        usort($tree, function ($item1, $item2) {
            return $item2['popularity'] <=> $item1['popularity'];
        });
        return $tree;
    }

    /**
     * Сортировка дочерних узлов дерева по популярности
     * @return array
     */
    protected function sortByPopularity(array &$tree): array
    {
        foreach ($tree as &$node) {
            if (isset($node['children']) && is_array($node['children'])) {
                $node['children'] = $this->sortByPopularityItem($node['children']);
                foreach ($node['children'] as &$child) {
                    if (isset($child['children']) && is_array($child['children'])) {
                        $child['children'] = $this->sortByPopularityItem($child['children']);
                    }
                }
            }
        }
        return $tree;
    }

    /**
     * Рекурсия для сборки дерева по родительской категории
     *
     * @param array $elements
     * @param int $parentId
     *
     * @return array
     */
    public function buildTree(array &$elements, $parentId = TREE_CATEGORY_ID_CATALOG): array
//        public function buildTree (array &$elements, $parentId = TREE_CATEGORY_ID_RUBRICATOR): array
    {
        $branch = [];

        foreach ($elements as $element) {
            //редирект на другой элемент дерева
            if ($element['redirect_tree_id']) {
                $redirectTree = Tree::findOne($element['redirect_tree_id']);
                $element['url'] = Url::to([$redirectTree->dir]);

                //произвольный редирект
            } elseif ($element['redirect']) {
                $element['url'] = $element['redirect'];

                //просто ссылка на сам элемент дерева
            } else {
//                    $element['url'] = Url::to([$element['url']]);
                $element['url'] = '/' . $element['url'] . (substr($element['url'], -1) != '/' ? '/' : '');
            }

            if ($element['parent_id'] == $parentId) {
                $children = $this->buildTree($elements, $element['id']);
                if ($children) {
                    $element['children'] = $children;
                }
                $branch[$element['id']] = $element;
                unset($elements[$element['id']]);
            }
        }
        return $branch;
    }

    /**
     * Разбить массив дочерних категорий на части по атрибуту COLUMN
     *
     * @param array $node
     *
     * @return array
     */
    public function childChunk(array $node): array
    {
        $childMenu = [];
        $column = 1;

        foreach ($node as $key => $children) {

            $columnAttr = ArrayHelper::getValue($children, 'column.value');
            $column = ($columnAttr && $columnAttr > $column) ? $columnAttr : $column;


            if (!key_exists($column, $childMenu)) {
                $childMenu[$column] = [];
            }

            $childMenu[$column][] = [
                'popularity' => $children['popularity'],
                'id' => $children['id'],
                'parent_id' => $children['parent_id'],
                'has_children' => $children['has_children'],
                'name' => $children['name'],
                'url' => $children['url'],
                'children' => key_exists('children', $children) ? $children['children'] : null,
            ];
        }

        return $childMenu;
    }

    /**
     * Находим все категории удовлетворяющих условию
     * @return TreeFactory
     */
    public function findAll(): TreeFactory
    {
        /** @var CmsTree $modelClass */
        $modelClass = $this->modelClass;
        $query = $modelClass::find()
            ->select([
                'id',
                'popularity',
                'parent_id' => 'pid',
                'has_children',
                'tree_type_id',
                'name',
                'code',
                'level',
                'url' => 'dir',
                'redirect',
                'redirect_tree_id',
                'count_content_element',
            ]);
        if ($this->categories) {
            $query->andWhere(['in', 'id', $this->categories]);
        }

        //если задана родительская категория
        if ($this->rootCategory) {
            $query->andWhere([
                'like', 'pids', implode('/', [TREE_CATEGORY_ID_ROOT, self::$useRubricator ? $this->rootRubricator : $this->rootCategory]),
            ]);
        }

        if ($this->typeIds) {
            $query->type($this->typeIds);
        }

        if ($this->level) {
            $query->level($this->level);
        }

        if ($this->onlyActive) {
            $query->active();
        }

        if ($this->onlyActive) {
            $query
                ->andWhere([
                    'OR',
                    ['>', 'count_content_element', 0],
                    ['<', 'level', 4],
                ]);
        }

        if ($this->enabledCurrentSite && \Yii::$app->cms->site) {
            $query->siteCode(\Yii::$app->cms->site);
        }

        $query->addOrderBy(['priority' => SORT_DESC]);
        $query->addOrderBy(['popularity' => SORT_DESC]);

//            if ($this->orderBy) {
//                $query->orderBy([$this->orderBy => (int)$this->order]);
//            }

        $this->items = $query
            ->with([
                'column',
                'rightBannerImage', 'rightBannerName', 'rightBannerLink',
                'rightBannerSubTitleFirst', 'rightBannerSubTitleSecond',
            ])
            ->asArray()
            ->all();

        return $this;
    }

    public static function checkCategoryShow($id, $tree_id = null, $children = [], $parents = [])
    {
        if (!$tree_id) {
            return true;
        } else {
            $flag = false;
            if (count($children) && in_array($id, $children)) {
                $flag = true;
            }
            if (count($parents) && in_array($id, $parents)) {
                $flag = true;
            }
            return $flag;
        }

    }

}