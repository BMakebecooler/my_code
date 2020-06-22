<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 26/12/2018
 * Time: 20:06
 */

namespace common\models\search;

use common\lists\TreeList;
use modules\api\resource\Category;
use skeeks\cms\components\Cms;
use yii\data\ActiveDataProvider;

class CategorySearch extends Category
{

    public function search($params = [])
    {
        $treeCatalog = Category::findOne(['id' => $params['branch'] ?? TreeList::CATALOG_ID]) ;

        $query = $treeCatalog->getDescendants()
            ->andWhere(['active' => Cms::BOOL_Y])
//            ->andWhere('count_content_element > 0')
            ->addOrderBy(['priority' => SORT_DESC])
            ->addOrderBy(['popularity' => SORT_DESC]);


//        if ((isset($params['per_page']) && $params['per_page'] == 0) || !isset($params['per_page'])){
//            $pagination = false;
//        }else{
//            $pagination = [
////                'pageSize' => 1000
////                'pageSizeParam' => 'per_page'
//                'pageSizeParam' => 'per_page',
//                'pageSize' => 100,
//            ];
//        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
//            'pagination' => [
//                'pageSizeParam' => 'per_page',
//                'pageSize' => 600,
//            ]
        ]);

        if ($params && !($this->load($params, '') && $this->validate())) {
            return $dataProvider;
        }

        return $dataProvider;
    }

    public function searchExtended($params = [])
    {
        $treeCatalog = Category::findOne(['id' => $params['branch'] ?? TreeList::CATALOG_ID]) ;

        $query = $treeCatalog->getDescendants()
            ->select([
                'id',
                'code',
                'pid',
                'name',
                'dir',
                'description_short',
                'redirect',
                'redirect_tree_id',
                'count_content_element',
                'priority',
                'popularity',
                new \yii\db\Expression('0 AS extendedMenuItem'),
                new \yii\db\Expression('1 AS sort_extended'), //Что бы подмесные пункты не шли вперемешку с остальными
            ])
            ->andWhere(['active' => Cms::BOOL_Y])
//            ->andWhere('count_content_element > 0')
            ->addOrderBy(['priority' => SORT_DESC])
            ->addOrderBy(['popularity' => SORT_DESC]);

        if (empty($params['branch'])){
            //* ДопКатегории для меню *//

            $categoryTreeAddsItems = [];

            if (true) { //На случай экстренного отключения, возможно сделать через настройку
                $categoryTreeAddsItems = \Yii::$app->cache->getOrSet(
                    'category_tree_adds_items',
                    function () {
                        \Yii::error("get NOT from cache category_tree_adds_items", 'debug');

                        $popularSubcategoriesQuery = \common\models\BUFECommN1234::getPopularSubcategoriesQuery();
                        $popularSubcategoriesIds = $popularSubcategoriesQuery ? $popularSubcategoriesQuery->select([\common\models\CmsTree::tableName() . '.id'])->column() : [];

                        $categoryTreeAddsQuery = \common\models\CmsTree::find()
                            ->select([
                                'id',
                                'name'
                            ])
                            ->andWhere(['active' => \common\helpers\Common::BOOL_Y])
                            ->andWhere(['>', 'count_content_element', 0])
                            ->andWhere(['!=', 'pid', TREE_CATEGORY_ID_CATALOG])
                            ->andWhere(['has_children' => \common\helpers\Common::BOOL_N_INT])
                            ->andFilterWhere(['id' => $popularSubcategoriesIds])
                            ->orderBy(['popularity' => SORT_DESC])
                            ->limit(10); //Вряд ли будет влезать больше 10 пунктов, даже если они будут короткими

                        //* Вычисляем допустимое кол-во разделов для отображения на основе длины названий *//

                        $categoryTreeAddsItems = $categoryTreeAddsQuery->asArray()->all();

                        return $categoryTreeAddsItems;
                    },
                    MIN_30
                );
            }

            if ($categoryTreeAddsItems){
                $categoryTreeAdds = [];
                $namesLettersCount = 0;
                foreach ($categoryTreeAddsItems as $categoryTreeAddsItem) {
                    $namesLettersCount += mb_strlen($categoryTreeAddsItem['name']);

                    if ($namesLettersCount > \common\models\CmsTree::NAV_ADDS_LETTERS_COUNT_LIMIT){
                        break;
                    }

                    $categoryTreeAdds[] = $categoryTreeAddsItem['id'];
                }

                if ($categoryTreeAdds){
                    $query2 = Category::find()
                        ->select([
                            'id',
                            'code',
                            new \yii\db\Expression(TREE_CATEGORY_ID_CATALOG . ' AS pid'),
                            'name',
                            'dir',
                            'description_short',
                            'redirect',
                            'redirect_tree_id',
                            'count_content_element',
                            'priority',
                            'popularity',
                            new \yii\db\Expression('1 AS extendedMenuItem'),
                            new \yii\db\Expression('2 AS sort_extended'), //Что бы подмесные пункты не шли вперемешку с остальными
                        ])
                        ->andWhere(['id' => $categoryTreeAdds])
                        ->andWhere(['active' => Cms::BOOL_Y])
//                        ->andWhere('count_content_element > 0')
                        ->addOrderBy(['priority' => SORT_DESC])
                        ->addOrderBy(['popularity' => SORT_DESC]);

                    $query->union($query2);

                    $query = Category::findBySql($query->createCommand()->getRawSql() . " ORDER BY sort_extended ASC, priority DESC, popularity DESC");
                }
            }

            //* /Вычисляем допустимое кол-во разделов для отображения на основе длины названий *//
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
//            'pagination' => [
//                'pageSizeParam' => 'per_page',
//                'pageSize' => 600,
//            ]
        ]);

        if ($params && !($this->load($params, '') && $this->validate())) {
            return $dataProvider;
        }

        return $dataProvider;
    }
}