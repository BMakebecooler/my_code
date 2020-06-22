<?php
/**
 * php ./yii imports/category/update-popularity
 * php ./yii imports/category/hide-empty-cats
 */

namespace console\controllers\imports;


use common\helpers\Category;
use common\helpers\Common;
use common\models\BUFECommN1234;
use common\models\CmsTree;
use common\models\Product;
use common\models\Tree;
use modules\shopandshow\lists\Guids;
use yii\db\Expression;

class CategoryController extends \yii\console\Controller
{
    public function actionUpdatePopularity()
    {
        $this->stdout('Сбрасываем популярность всех разделов '.PHP_EOL);
        $affected = CmsTree::updateAll(['popularity' => 1], ['!=', 'popularity', 1]);

        $forUpdate = [];

        $this->stdout('Собираю данные по популярности...'.PHP_EOL);
        //Проходимся по всем уровням меню и получаем популярность суммируя популярность подразделов
        for ($lvl=4; $lvl>0; $lvl--){
            $query = BUFECommN1234::find()
                ->select(["g{$lvl} AS guid", 'rub', new Expression("SUM(rub) AS popularity")])
                ->andWhere(['not', ["g{$lvl}" => null]])
                ->groupBy(["g{$lvl}"])
                ->orderBy('popularity')
                ->asArray();

            foreach ($query->each() as $analyticsCategory) {
                $guid = $analyticsCategory['guid'];
                /** @var CmsTree $category */
                $category = Guids::getEntityByGuid($guid);
                if ($category){
                    $forUpdate[$category->id] = [
                        'popularity' => $analyticsCategory['popularity'] ?: 1,
                        'category' => $category,
                    ];
                }
            }
        }

        $this->stdout("Разделов для обновления: " .count($forUpdate) . PHP_EOL);

        if ($forUpdate){
            $i=0;
            foreach ($forUpdate AS $data) {
                $i++;
                /** @var CmsTree $category */
                $category = $data['category'];
                $category->popularity = $data['popularity'];

                $this->stdout("{$i}) [lvl={$category->level}] {$category->popularity}} | {$category->name}" .PHP_EOL);

                if (!$category->save()){
                    //error
                }
            }
        }

        $this->stdout('DONE' .PHP_EOL);

        return true;
    }

    public function actionUpdatePopularityOld()
    {
        $this->stdout('Сбрасываем популярность всех разделов '.PHP_EOL);
        Category::losePopularityAllCategories();

        $result = BUFECommN1234::find()
            ->orderBy(['rub' => SORT_DESC])
            ->asArray();

        foreach ($result->each() as $data) {
            $guid = $data['g4'];
            if(!$guid){
                continue;
            }

            $popularity = (int)$data['rub'];
            if(!$popularity){
                $popularity = 1;
            }

            $model = Guids::getEntityByGuid($guid);
            if ($model) {
                $className = get_class($model);
                if($className == 'common\models\Tree') {
                    $this->stdout('Обновляем популярность ' . $popularity . ' раздела ' . $model->name . ' ' . PHP_EOL);
                    $model->popularity = $popularity;
                    $model->save();
                }
            }
        }
    }

    public function actionHideEmptyCats()
    {
        $categories = Tree::find()
            ->andWhere(['IN','level',[3,4]])
            ->andWhere(['has_children' => 0]);

        /** @var Tree $category */
        foreach ($categories->each() as $category){

            $this->stdout('Проверяем категорию '.$category->id.' '.$category->name.' '.PHP_EOL);

            $flag = false;
            $lots = Product::find()
                ->onlyLot()
                ->canSale()
                ->onlyActive()
                ->andWhere(['tree_id' => $category->id]);

            if($lots){
                foreach ($lots->each() as $lot){
                    $card = Product::getCardCanSaleWithMinPrice($lot->id);
                    if($card){
                        $flag = true;
                        break;
                    }
                }
            }

            if(!$flag) {
                $this->stdout('Отключаем категорию '.$category->id.' '.$category->name.' '.PHP_EOL);
                $category->active = Common::BOOL_N;
            }else{
                //Так как некоторые разделы хоть и с товарами, но должны быть скрыты, то включение автоматом не производим
                $this->stdout('Пригодна для включения категория '.$category->id.' '.$category->name.' '.PHP_EOL);
//                $this->stdout('Включаем категорию '.$category->id.' '.$category->name.' '.PHP_EOL);
//                $category->active = Common::BOOL_Y;
            }
            try {
                $category->update();
            }   catch (\yii\db\Exception $exception) {
                return true;
            }
        }
    }

}