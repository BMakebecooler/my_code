<?php


namespace common\models;


class StatisticProductsImages extends \common\models\generated\models\StatisticProductsImages
{
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'count_all' => 'Количество всех карточек без фото',
            'count_all_stock' => 'Количество карточек без фото в наличие',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}