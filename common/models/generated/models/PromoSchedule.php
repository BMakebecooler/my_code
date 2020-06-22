<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "promo_schedule".
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $active
 * @property integer $date_from
 * @property integer $date_to
 * @property string $coupon
 * @property integer $discount_percent
 * @property string $discount_on_text
 * @property string $url
 * @property integer $is_main
 */
class PromoSchedule extends \common\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'promo_schedule';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at', 'active', 'date_from', 'date_to', 'discount_percent', 'is_main'], 'integer'],
            [['url'], 'string'],
            [['coupon', 'discount_on_text'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Создано',
            'updated_at' => 'Обновлено',
            'active' => 'Активен',
            'date_from' => 'Начало активности',
            'date_to' => 'Конец активности',
            'coupon' => 'Код купона',
            'discount_percent' => 'Процент скидки',
            'discount_on_text' => 'Скидка на ...',
            'url' => 'Ссылка на сборку',
            'is_main' => 'Для поля купон',
        ];
    }

    /**
     * @inheritdoc
     * @return \common\models\generated\query\PromoScheduleQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\generated\query\PromoScheduleQuery(get_called_class());
    }
}
