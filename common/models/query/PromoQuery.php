<?php


namespace common\models\query;

use common\helpers\Common;
use common\models\Promo;

class PromoQuery extends \common\models\generated\query\PromoQuery
{
    public function onlyActive()
    {
        return $this->andWhere([Promo::tableName().'.active' => Common::BOOL_Y_INT]);
    }

    public function onlyNoneActive()
    {
        return $this->andWhere([Promo::tableName().'.active' => Common::BOOL_N_INT]);
    }

    public function onlyHaveImageBanner()
    {
        return $this->andWhere([Promo::tableName().'.have_image_banner' => Common::BOOL_Y_INT]);
    }

    public function onlyInMenu()
    {
        return $this->andWhere([Promo::tableName().'.in_menu' => Common::BOOL_Y_INT]);
    }

    public function onlyInMain()
    {
        return $this->andWhere([Promo::tableName().'.in_main' => Common::BOOL_Y_INT]);
    }

    public function onlyInActions()
    {
        return $this->andWhere([Promo::tableName().'.in_actions' => Common::BOOL_Y_INT]);
    }

    public function onlyTimeStamp()
    {
        return $this->andWhere(['AND',['!=','start_timestamp',Common::BOOL_Y_INT],['!=','end_timestamp',Common::BOOL_Y_INT]]);
    }

    public function onlyActiveTime()
    {
        $time = time();

        return $this->andWhere([
            'AND',
            ['OR',['<=','start_timestamp',$time],['start_timestamp' => Common::BOOL_Y_INT]],
            ['OR',['>=','end_timestamp',$time],['end_timestamp' => Common::BOOL_Y_INT]],
        ]);
    }

}