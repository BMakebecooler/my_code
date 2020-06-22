<?php


namespace common\models;

use common\models\generated\models\SsGuids;
use Yii;

class BUFECommN1234 extends \common\models\generated\models\BUFECommN1234
{
    //Кол-во подразделов для корневых разделов в главном меню
    public static $treeSubsCountByGuid = [
        '98DE73C15A7B34BFE0538201090AAF9D' => 3, //Мода
        '98DE73C15A8034BFE0538201090AAF9D' => 2, //Обувь
        '98DE73C15ACB34BFE0538201090AAF9D' => 2, //Красота
    ];

    public static function getDb()
    {
        return Yii::$app->dbStat;
    }

    //Выбирает по самому популярному разделу для каждого корневого раздела меню
    public static function getPopularSubcategoriesQuery()
    {
        if (YII_ENV != YII_ENV_PROD) {
            return [];
        }

        //Необходимо для каждого раздела верхнего уровня показывать произвольное кол-во подраздеалов

        //Просто список ГУИДов разделов, который используем в условии поиска
        $popularSubcategoriesGuids = [];

        //Список ГУИДов разложенный по разделам для учета кол-ва элементов в каждом разделе
        $popularSubcategoriesGuidsByTree = [];

        $analyticsRootTreeGuids = self::find()->select(['g1'])->distinct()->column();

        if ($analyticsRootTreeGuids){

            //* Не будем выбирать отключенные разделы и разделы с потомками *//

            $treesSubQuery = CmsTree::find()
                ->select([
                    'guids.guid'
                ])
                ->innerJoin(SsGuids::tableName() . ' AS guids', sprintf("guids.id=%s.guid_id", CmsTree::tableName()))
                ->andWhere(['!=', 'pid', TREE_CATEGORY_ID_CATALOG])
                ->andWhere(['OR',
                    ['!=', 'active', \common\helpers\Common::BOOL_Y],
                    ['has_children' => \common\helpers\Common::BOOL_Y_INT],
                ]);

            //* /Не будем выбирать отключенные разделы *//

            foreach ($analyticsRootTreeGuids as $analyticsRootTreeGuid) {
                $analyticsTreeGuids = self::find()
                    ->select(['g4'])
                    ->andWhere(['g1' => $analyticsRootTreeGuid])
                    ->andWhere(['not', ['g4' => $treesSubQuery->column()]])
                    ->orderBy(['rub' => SORT_DESC])
                    ->all();

                if ($analyticsTreeGuids){
                    //TODO В этом методе такая проверка не самое удачное решение
                    foreach ($analyticsTreeGuids as $analyticsTreeGuid) {
                        //* Проверка на то что в разделе есть достаточное кол-во товаров что бы показывать в галвном меню *//
                        $tree = CmsTree::find()
//                            ->byGuid($analyticsTreeGuid['g4']) //работает только для товаров
                            ->leftJoin(SsGuids::tableName(), SsGuids::tableName() . '.id=' . CmsTree::tableName() . '.guid_id')
                            ->andWhere([SsGuids::tableName() . '.guid' => $analyticsTreeGuid['g4']])
                            ->one();

                        /** @var $tree CmsTree */
                        if ($tree) {
                            $relatedProducts = \common\models\CmsTree::getRelatedProductsForTopNav($tree->id);

                            if ($relatedProducts && count($relatedProducts) >= 4) {
                                $popularSubcategoriesGuidsByTree[$analyticsRootTreeGuid][] = $analyticsTreeGuid['g4'];
                                $popularSubcategoriesGuids[] = $analyticsTreeGuid['g4'];

                                $treeSubCount = self::$treeSubsCountByGuid[$analyticsRootTreeGuid] ?? 1;

                                if (count($popularSubcategoriesGuidsByTree[$analyticsRootTreeGuid]) >= $treeSubCount) {
                                    break; //Учитываем ограничения на кол-во элементов в разделе
                                }
                            }
                        }

                        //* /Проверка на то что в разделе есть достаточное кол-во товаров что бы показывать в галвном меню *//
                    }
                }
            }
        }

        if ($popularSubcategoriesGuids) {
            $treesQuery = CmsTree::find()
                ->select([
                    CmsTree::tableName() . '.id',
                    'active',
                    'name',
                    'pid',
                    'pids',
                    'url' => 'dir',
                ])
                ->innerJoin(SsGuids::tableName() . ' AS guids', sprintf("guids.id=%s.guid_id", CmsTree::tableName()))
                ->andWhere(['guids.guid' => $popularSubcategoriesGuids])
                ->andWhere(['!=', 'pid', TREE_CATEGORY_ID_CATALOG]);
        }

        return $treesQuery ?? false;
    }
}