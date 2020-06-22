<?php
namespace modules\shopandshow\widgets\grid;

use common\helpers\ArrayHelper;

/**
 * Class LifestyleWidget
 * @package modules\shopandshow\widgets\grid
 */
class LifestyleWidget extends BaseWidget
{
    public $namespace = 'LifestyleWidget';
    public $viewFile = 'lifestyle';
    public $formFile = './forms/_lifestyle_widget.php';

    // заголовки
    public $header;
    public $subHeader;

    // главный баннер
    public $image_id_0;
    public $image_id_1;
    public $image_id_2;
    public $imageUrl = [];
    public $imageTitle = [];

    // эксперты
    public $expertTitle = [];
    public $expertFio = [];
    public $expertLinkTitle = [];
    public $expertLinkUrl = [];

    public $expertCount = 3;

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
                'expertTitle' => 'Описание эксперта',
                'expertFio' => 'Фио эксперта',
                'expertLinkTitle' => 'Текст ссылки с секретами',
                'expertLinkUrl' => 'Ссылка на секрет',
                'expertCount' => 'Количество экспертов'
            ]);
    }

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(),
            [
                [['imageUrl', 'imageTitle', 'expertTitle', 'expertFio', 'expertLinkTitle', 'expertLinkUrl'], 'safe'],
                [['header', 'subHeader'], 'string'],
                [['expertCount', 'image_id_0', 'image_id_1', 'image_id_2'], 'integer'],
            ]);
    }
}
