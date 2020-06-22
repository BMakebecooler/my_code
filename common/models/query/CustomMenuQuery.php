<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 2019-07-31
 * Time: 12:06
 */

namespace common\models\query;


use common\models\CustomMenu;

class CustomMenuQuery extends \common\models\generated\query\CustomMenuQuery
{

    public function init()
    {
        if (\Yii::$app->id == 'app-frontend') {
            $this->onlyActive();
        }
        parent::init(); // TODO: Change the autogenerated stub
    }

    public function onlyActive()
    {
        $this->andWhere(['is_active' => true]);
    }

    public function typePromo(){
        return $this->andWhere(['type_id' => CustomMenu::TYPE_PROMO]);
    }
    public function typeStock(){
        return $this->andWhere(['type_id' => CustomMenu::TYPE_STOCK]);
    }
}