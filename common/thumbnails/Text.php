<?php

namespace common\thumbnails;

use yii\base\Exception;

/**
 * Class Text
 * @package common\thumbnails
 */
class Text extends BaseThumbnail
{
    public $font = 'v2/common/fonts/glober_bold.woff';
    public $color = '#000';
    public $size = '12';
    public $text = '';

    public $start = [0, 0];
    public $angle = 0;

    public function init()
    {
        parent::init();

        if (!$this->font && !$this->color && !$this->size) {
            throw new Exception("Необходимо указать все параметры шрифта: название, цвет, размер");
        }

        if (!$this->text) {
            throw new Exception("Необходимо указать текст");
        }
    }

    protected function _save()
    {
        $options = $this->getOptions();

        $fontOptions = [
            'size' => $this->size,
            'color' => $this->color,
            'angle' => $this->angle
        ];

        Image::text($this->_originalRootFilePath, $this->text, $this->font, $this->start, $fontOptions)
            ->save($this->_newRootFilePath, $options);

    }
}