<?php


namespace common\models;


use common\models\generated\query\CallbackQuery;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

class Callback extends \common\models\generated\models\Callback
{

    public function behaviors ()
    {
        return [
            TimestampBehavior::class,
            BlameableBehavior::class,
        ];
    }

    /**
     * @inheritdoc
     * @return CallbackQuery the active query used by this AR class.
     */
    public static function find ()
    {
        return new CallbackQuery(get_called_class());
    }

}