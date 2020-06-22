<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 14/03/2019
 * Time: 12:16
 */

namespace common\models\query;


class CmsContentPropertyQuery extends \common\models\generated\query\CmsContentPropertyQuery
{
    public function byCode($code)
    {
        return $this->andWhere(['code' => $code]);
    }
}