<?php
namespace modules\shopandshow\widgets\grid;

use common\helpers\ArrayHelper;

/**
 * Class DefaultWidget
 * @package modules\shopandshow\widgets\grid
 */
class DefaultWidget extends BaseWidget
{
    public $namespace = 'DefaultWidget';
    public $viewFile = 'default';
    public $formFile = './forms/_default_widget.php';

    // заголовки
    public $header;
    public $subHeader;

    // главный баннер
    public $image_id;
    public $imageUrl;
    public $imageTitle;

    // список продуктов
    public $products = [];

    // кнопка
    public $button = 0;
    public $buttonUrl;
    public $buttonTitle;

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(),
            [
                'header' => 'Заголовок',
                'subHeader' => 'Подзаголовок',
                'image_id' => 'Баннер',
                'imageUrl' => 'Ссылка с баннера',
                'imageTitle' => 'Название баннера',
                'products' => 'Лоты',
                'button' => 'Нужна кнопка',
                'buttonUrl' => 'Ссылка с кнопки',
                'buttonTitle' => 'Название кнопки',
            ]);
    }

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(),
            [
                [['image_id'], 'integer'],
                [['header', 'subHeader', 'imageUrl', 'imageTitle', 'button', 'buttonUrl', 'buttonTitle'], 'string'],
                [['products'], 'safe'],
            ]);
    }
}
