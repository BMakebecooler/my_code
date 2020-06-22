<?php
/**
 * Created by PhpStorm.
 * User: ubuntu5
 * Date: 07.03.17
 * Time: 15:18
 */

namespace common\thumbnails;


use common\helpers\ArrayHelper;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Imagine\Image\ManipulatorInterface;
use Imagine\Image\Point;
use skeeks\imagine\BaseImage;
use Yii;
use yii\base\InvalidParamException;

class Image extends BaseImage
{

    /**
     * Creates a thumbnail image. The function differs from `\Imagine\Image\ImageInterface::thumbnail()` function that
     * it keeps the aspect ratio of the image.
     * @param string $filename the image file path or path alias.
     * @param integer $width the width in pixels to create the thumbnail
     * @param integer $height the height in pixels to create the thumbnail
     * @param string $mode
     * @return ImageInterface
     */
    public static function thumbnail($filename, $width, $height, $mode = ManipulatorInterface::THUMBNAIL_OUTBOUND)
    {
        //todo непонятно зачем использовался старый код
        if($mode ==  ManipulatorInterface::THUMBNAIL_INSET ) {
            $img = \yii\imagine\Image::thumbnail($filename, $width, $height, $mode);
            return $img;
        }else {

            $box = new Box($width, $height);
            $img = static::getImagine()->open(Yii::getAlias($filename));

            $size = $img->getSize(); //Размер оригинальной фотки

            //Если ширина и высота меньше чем мы хотим то возвращаем исходную фотку, убрал это поведение
            if (($size->getWidth() <= $box->getWidth() && $size->getHeight() <= $box->getHeight()) || (!$box->getWidth() && !$box->getHeight())) {
                //return $img->copy();
            }

            // create empty image to preserve aspect ratio of thumbnail
            $palette = new \Imagine\Image\Palette\RGB();
            $thumb = static::getImagine()->create($box, $palette->color('#FFFFFF', 100));

            $startX = 0;
            $startY = 0;

            /**
             * Кароч, если исходная фотка меньше желаемой то делаем ресайз, иначе thumbnail подставляет #FFFFFF по бокам
             */
            if ($size->getWidth() < $width && $size->getHeight() < $height) {
                $img = $img->resize($size->widen($width));
            } else {
                $img = $img->thumbnail($box, $mode);

                if ($size->getWidth() < $width) {
                    $startX = ceil($width - $size->getWidth()) / 2;
                }
                if ($size->getHeight() < $height) {
                    $startY = ceil($height - $size->getHeight()) / 2;
                }
            }

            $thumb->paste($img, new Point($startX, $startY));

            return $thumb;
        }
    }

    /**
     * Draws a text string on an existing image.
     * @param string $filename the image file path or path alias.
     * @param string $text the text to write to the image
     * @param string $fontFile the file path or path alias
     * @param array $start the starting position of the text. This must be an array with two elements representing `x` and `y` coordinates.
     * @param array $fontOptions the font options. The following options may be specified:
     *
     * - color: The font color. Defaults to "fff".
     * - size: The font size. Defaults to 12.
     * - angle: The angle to use to write the text. Defaults to 0.
     *
     * @return ImageInterface
     * @throws InvalidParamException if `$fontOptions` is invalid
     */
    public static function text($filename, $text, $fontFile, array $start = [0, 0], array $fontOptions = [])
    {
        if (!isset($start[0], $start[1])) {
            throw new InvalidParamException('$start must be an array of two elements.');
        }

        $fontSize = ArrayHelper::getValue($fontOptions, 'size', 12);
        $fontColor = ArrayHelper::getValue($fontOptions, 'color', '#000');
        $fontAngle = ArrayHelper::getValue($fontOptions, 'angle', 0);

        $palette = new \Imagine\Image\Palette\RGB();

        $img = static::getImagine()->open(Yii::getAlias($filename));
        $font = static::getImagine()->font(Yii::getAlias($fontFile), $fontSize, $palette->color($fontColor, 100));

        $img->draw()->text($text, $font, new Point($start[0], $start[1]), $fontAngle);

        return $img;
    }

    /**
     * Выводит текст и перечеркивает его по диагонали
     * @param string $filename the image file path or path alias.
     * @param string $text the text to write to the image
     * @param string $fontFile the file path or path alias
     * @param array $start the starting position of the text. This must be an array with two elements representing `x` and `y` coordinates.
     * @param array $fontOptions the font options. The following options may be specified:
     *
     * - color: The font color. Defaults to "fff".
     * - size: The font size. Defaults to 12.
     * - angle: The angle to use to write the text. Defaults to 0.
     *
     * @param array $lineOptions the line options. The following options may be specified:
     *
     * - text: Text to render striked
     * - color: The font color. Defaults to "fff".
     * - width: Line width. Defaults to 1
     *
     * @return ImageInterface
     */
    public static function textStriked($filename, $text, $fontFile, array $start = [0, 0], array $fontOptions = [], $lineOptions = [])
    {
        $img = self::text($filename, $text, $fontFile, $start, $fontOptions);

        $palette = new \Imagine\Image\Palette\RGB();

        $fontSize = ArrayHelper::getValue($fontOptions, 'size', 12);
        $fontAngle = ArrayHelper::getValue($fontOptions, 'angle', 0);
        $fontBox = imagettfbbox($fontSize, $fontAngle, Yii::getAlias($fontFile), $lineOptions['text']);

        if ($fontBox == false) {
            throw new \RuntimeException('failed to calc font box');
        }

        $lineColor = ArrayHelper::getValue($lineOptions, 'color', '#000');
        $lineWidth = ArrayHelper::getValue($lineOptions, 'width', 1);

        $fontHeight = $fontBox[1] - $fontBox[7];
        $fontWidth = $fontBox[4] - $fontBox[0];

        $lineStart = [$start[0], $start[1] + $fontHeight];
        $lineEnd = [$start[0] + $fontWidth, $start[1]];

        $img->draw()->line(new Point(...$lineStart), new Point(...$lineEnd), $palette->color($lineColor, 100), $lineWidth);

        return $img;
    }
}