<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 2019-06-10
 * Time: 14:05
 */

namespace common\models\query;


use common\helpers\Common;
use common\models\CmsUserEmail;

class CmsUserEmailQuery extends \common\models\generated\query\CmsUserEmailQuery
{

    public function onlyApprovedRR()
    {
        return $this->andWhere([CmsUserEmail::tableName() . '.approved_rr' => Common::BOOL_Y_INT]);
    }

    public function onlyNotApprovedRR()
    {
        return $this->andWhere([CmsUserEmail::tableName() . '.approved_rr' => Common::BOOL_N_INT]);
    }

    public function onlyEmail()
    {
        return $this->andWhere([CmsUserEmail::tableName() . 'value_type' => CmsUserEmail::VALUE_TYPE_EMAIL]);
    }
}