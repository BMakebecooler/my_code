<?php
namespace modules\shopandshow\widgets\grid;

use common\helpers\ArrayHelper;

/**
 * Class CtsWidget
 * @package modules\shopandshow\widgets\grid
 */
class CtsWidget extends BaseWidget
{
    public $namespace = 'CtsWidget';
    public $viewFile = 'cts';
    public $formFile = './forms/_cts_widget.php';

    // заголовки
    public $header;
    public $description;
    public $descriptionColored;
    public $descriptionColor;

    // главный баннер
    public $image_id;
    public $imageUrl;
    public $imageTitle;

    // кнопка
    public $button = 0;
    public $buttonUrl;
    public $buttonTitle;

    public $timer = 0;

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(),
            [
                'header' => 'Заголовок',
                'description' => 'Описание снизу',
                'descriptionColored' => 'Описание снизу (цветом)',
                'descriptionColor' => 'Цвет описания',
                'image_id' => 'Баннер',
                'imageUrl' => 'Ссылка с баннера',
                'imageTitle' => 'Название баннера',
                'button' => 'Нужна кнопка',
                'buttonUrl' => 'Ссылка с кнопки',
                'buttonTitle' => 'Название кнопки',
                'timer' => 'Нужен таймер'
            ]);
    }

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(),
            [
                [['image_id'], 'integer'],
                [['header', 'description', 'descriptionColored', 'descriptionColor', 'imageUrl', 'imageTitle', 'button', 'buttonUrl', 'buttonTitle', 'timer'], 'string'],
            ]);
    }
}
