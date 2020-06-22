<?php

/**
 * php ./yii sync/api/categories
 */

namespace console\controllers\sync\api;

use common\helpers\Url;
use common\lists\TreeList;
use common\models\Tree;
use modules\api\models\mongodb\Category;
use skeeks\cms\components\Cms;

/**
 * Class CategoriesController
 * @package console\controllers
 */
class CategoriesController extends \yii\console\Controller
{

    const DEFAULT_CATALOG_LOGO = '/v2/common/img/category_nophoto.png';

    public function actionIndex()
    {

        /**
         * @var Tree[] $trees
         */

        $treeCatalog = TreeList::getTreeById(TreeList::CATALOG_ID);

        $trees = $treeCatalog->getDescendants()
            ->andWhere(['active' => Cms::BOOL_Y])
            ->andWhere('count_content_element > 0')
            ->addOrderBy(['priority' => SORT_ASC])
            ->all();

        $mongoDB = \Yii::$app->mongodb->createCommand();

        foreach ($trees as $tree) {

            $catalogLogo = null;
            if ($catalogLogoSrc = $tree->relatedPropertiesModel->getAttribute('catalogLogo')) {
                $catalogLogo = ['src' => sprintf('%s%s', Url::getBaseUrl(), $catalogLogoSrc)];
            }

            $catalogInfo = [
                'id' => $tree->id,
                'name' => $tree->name,
                'slug' => $tree->code,
                'parent' => $tree->pid == 9 ? 0 : $tree->pid,
                'description' => htmlentities($tree->description_short),
                'display' => 'default',
                'menu_order' => $tree->priority,
                'count' => $tree->count_content_element,
                'image' => $catalogLogo
            ];

            $mongoDB->addUpdate(['id' => $tree->id], $catalogInfo, ['upsert' => true]);
        }

        return $mongoDB->executeBatch(Category::collectionName());
    }

}