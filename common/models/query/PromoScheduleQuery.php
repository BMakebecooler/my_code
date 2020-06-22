<?php


namespace common\models\query;


use common\helpers\Common;
use yii\db\Expression;

class PromoScheduleQuery extends \common\models\generated\query\PromoScheduleQuery
{
    public function actual(){
        return $this
            ->andWhere(['active' => Common::BOOL_Y_INT])
            ->andWhere(['<=', 'date_from', date('U')])
            ->andWhere(['>=', 'date_to', date('U')]);
    }
}