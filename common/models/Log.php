<?php


namespace common\models;


class Log extends \common\models\generated\models\Log
{
    const SEGMENT_CLASS = 'common\models\Segment';

    const PROMO_CLASS = 'common\models\Promo';

    const MESSAGE_TYPE_ERROR = 'error';

    const MESSAGE_TYPE_INFO = 'info';

    const LIMIT = 10;

    public static $modelClassNames = [
        self::SEGMENT_CLASS => 'Сегмент',
        self::PROMO_CLASS => 'Промо'
    ];

    public static function add($model, $text, $type = self::MESSAGE_TYPE_INFO)
    {
        if (!$model) {
            return false;
        }

        if (!$text) {
            return false;
        }

        $modelLog = new self();
        $modelLog->type = $type;
        $modelLog->model_class = get_class($model);
        $modelLog->model_id = $model->id;
        $modelLog->text = $text;
        $modelLog->save();
    }

    public static function getLastRecordsQuery($id, $modelClass = self::SEGMENT_CLASS)
    {
        if (!$id) {
            return false;
        }

        return self::find()
            ->byModelClass($modelClass)
            ->andWhere(['model_id' => $id])
            ->addOrderBy(['id' => SORT_DESC])
            ->limit(self::LIMIT);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Тип',
            'model_class' => 'Модель',
            'model_id' => 'Id модели',
            'description' => 'Описание',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата обновления',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }

}