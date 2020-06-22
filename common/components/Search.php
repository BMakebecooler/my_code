<?php

/**
 * Created by PhpStorm.
 * User: koval
 * Date: 16.02.17
 * Time: 11:38
 */

namespace common\components;

use skeeks\cms\search\CmsSearchComponent;
use yii\db\Expression;
use yii\helpers\Html;
use yii\sphinx\Query;

class Search extends CmsSearchComponent
{

    /**
     * Найти через sphinx
     * @param string $from
     * @return array
     */
    public function sphinxSearch($from = 'products')
    {
        if ($q = \Yii::$app->cmsSearch->searchQuery) {
            $querySearch = new Query();

            $querySearch->addOptions(['field_weights' => ['phrase' => 10, 'name' => 5]]);
            $querySearch->addOptions(['max_matches' => 10000]);
            $querySearch->addOptions(['ranker' => new Expression("expr('sum(lcs*user_weight)*1000+bm25')")]);

            return $querySearch->from($from)->match($q)->limit(2000)->all();
        }

        return [];
    }


    /**
     * Найти через sphinx и вернуть только Ид найденных элементов
     * @param string $from
     * @return array
     */
    public function sphinxSearchIds($from = 'products')
    {
        $ids = [];

        if ($search = $this->sphinxSearch($from)) {
            $ids = array_column($search, 'id');
        }

        return $ids;
    }


    public function getSearchQuery()
    {
        $q = (string)\Yii::$app->request->get($this->searchQueryParamName);
        return Html::encode($q);
    }

}