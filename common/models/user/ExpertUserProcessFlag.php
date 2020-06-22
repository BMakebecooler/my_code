<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 21.01.19
 * Time: 11:50
 */

namespace common\models\user;


use yii\db\ActiveRecord;

class ExpertUserProcessFlag extends ActiveRecord
{
    public static function tableName()
    {
        return '{{expert_users_process_flag}}';
    }
}