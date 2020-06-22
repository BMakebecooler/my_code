<?php


namespace modules\api\resource\v2;


use common\models\User AS UserModel;

class User extends UserModel
{
    public function fields()
    {
        return [
            'id',
            'name',
        ];
    }
}