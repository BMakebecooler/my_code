<?php


namespace modules\api\resource\v2;


use common\models\Brand as BrandModel;

class Brand extends BrandModel
{
    public function fields()
    {
        return [
            'id' => function () {
                return $this->id;
            },
            'name' => function () {
                return $this->name;
            },
        ];
    }
}