<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 21.01.19
 * Time: 11:40
 */

namespace common\models\user;

use yii\db\ActiveRecord;

class ExpertUser extends ActiveRecord
{
    public static function tableName()
    {
        return '{{expert_users}}';
    }
}