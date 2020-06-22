<?php
/**
 * Thumbnail
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010-2014 SkeekS (Sx)
 * @date 11.12.2014
 * @since 1.0.0
 */
namespace common\thumbnails;

use Imagine\Image\Box;
use Imagine\Image\Point;
use skeeks\imagine\Image;
use yii\base\Exception;

/**
 * Class CropAndResize
 *
 * @package skeeks\cms\components\imaging\filters
 */
class CropAndResize extends BaseThumbnail
{
    public $widthCrop = 1180;
    public $widthResize = 750;

    public function init()
    {
        parent::init();
        if (!$this->widthCrop || !$this->widthResize) {
            throw new Exception("Необходимо указать ширину или высоту");
        }
    }

    protected function _save()
    {
        $originalImage = Image::getImagine()->open($this->_originalRootFilePath);

        $w = $originalImage->getSize()->getWidth();
        $h = $originalImage->getSize()->getHeight();

        $options = $this->getOptions();

        if ($w > $this->widthCrop) {
            $cropStartFrom = new Point(($w - $this->widthCrop) / 2, 0);
            $cropBox = new Box($this->widthCrop, $h);

            $originalImage->crop($cropStartFrom, $cropBox);
            $w = $this->widthCrop;
        }

        if ($w > $this->widthResize) {
            $resizeHeight = round(($this->widthResize / $this->widthCrop) * $h);
            $resizeBox = new Box($this->widthResize, $resizeHeight);

            $originalImage->resize($resizeBox);
        }

        $originalImage->save($this->_newRootFilePath, $options);
    }
}