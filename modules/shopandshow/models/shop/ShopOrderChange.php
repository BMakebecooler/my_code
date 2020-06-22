<?php


namespace modules\shopandshow\models\shop;

use common\helpers\ArrayHelper;
use skeeks\cms\shop\models\ShopOrderChange AS SxShopOrderChange;


class ShopOrderChange extends SxShopOrderChange
{
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['status_code'], 'string']
        ]);
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            ['status_code' => 'Статус заказа'],
        ]);
    }
}