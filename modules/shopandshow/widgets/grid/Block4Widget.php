<?php
namespace modules\shopandshow\widgets\grid;

use common\helpers\ArrayHelper;

/**
 * Class Block4Widget
 * @package modules\shopandshow\widgets\grid
 */
class Block4Widget extends BaseWidget
{
    public $namespace = 'Block4Widget';
    public $viewFile = 'block4';
    public $formFile = './forms/_block4_widget.php';

    // заголовки
    public $header;
    public $subHeader;

    // главный баннер
    public $image_id_0;
    public $image_id_1;
    public $image_id_2;
    public $imageUrl = ['', '', ''];
    public $imageTitle = ['', '', ''];

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(),
            [
                'header' => 'Заголовок',
                'subHeader' => 'Подзаголовок',
                'image_id_0' => 'Баннер',
                'image_id_1' => 'Баннер',
                'image_id_2' => 'Баннер',
                'imageUrl' => 'Ссылка с баннера',
                'imageTitle' => 'Название баннера',
            ]);
    }

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(),
            [
                [['imageUrl', 'imageTitle'], 'safe'],
                [['header', 'subHeader'], 'string'],
                [['image_id_0', 'image_id_1', 'image_id_2'], 'integer'],
            ]);
    }
}