<?php

namespace common\thumbnails;

use Imagine\Image\ManipulatorInterface;
use yii\base\Exception;

/**
 * Class Thumbnail
 * @package common\thumbnails
 */
class Thumbnail extends BaseThumbnail
{
    //todo включить отключить ресайз квадратных изображений в слайдерах
    public static $enableExtraResizeSlider = true;
//    public static $enableExtraResizeSlider = false;

    public $w = 0;
    public $h = 0;
    public $m = ManipulatorInterface::THUMBNAIL_OUTBOUND;

    public function init()
    {
        parent::init();

        if (!$this->w && !$this->h) {
            throw new Exception("Необходимо указать ширину или высоту");
        }

    }

    protected function _save()
    {
        $options = $this->getOptions();

        if (!$this->w) {
            $size = Image::getImagine()->open($this->_originalRootFilePath)->getSize();

            $width = ($size->getWidth() * $this->h) / $size->getHeight();
            Image::thumbnail($this->_originalRootFilePath, (int)round($width), $this->h, $this->m)
                ->save($this->_newRootFilePath, $options);

        } else if (!$this->h) {
            $size = Image::getImagine()->open($this->_originalRootFilePath)->getSize();

            $height = ($size->getHeight() * $this->w) / $size->getWidth();

            Image::thumbnail($this->_originalRootFilePath, $this->w, (int)round($height), $this->m)
                ->save($this->_newRootFilePath, $options);
        } else {
            Image::thumbnail($this->_originalRootFilePath, $this->w, $this->h, $this->m)
                ->save($this->_newRootFilePath, $options);
        }
    }
}