<?php
/**
 * Created by PhpStorm.
 * User: ubuntu5
 * Date: 22.06.17
 * Time: 19:14
 */

namespace common\helpers;


class Image
{
    public static $modResize = true;

    public static $quality = 90;

    public static $cachePath = '@runtime/';

    public static $typss = [

        'floctory' => [
            'w' => 720,
            'h' => 360,
        ],

        'mail' => [
            'w' => 600,
            'h' => 400
        ],

        'singlePromo' => [
            'w' => 800,
            'h' => 1200
        ],
        'doublePromo' => [
            'w' => 575,
            'h' => 373
        ],
        'ctsPromo' => [
            'w' => 373,
            'h' => 242
        ],
        'triplePromo' => [
            'w' => 373,
            'h' => 242
        ],
        'menuPromo' => [
            'w' => 373,
            'h' => 242
        ],
        'defaultPromo' => [
            'w' => 373,
            'h' => 242
        ],
    ];

    public function clearImageCache($image)
    {
        foreach (self::$typss as $type => $params){
            $new_name = md5($image).'_'.$type.'.jpg';
            $imageFile = \Yii::getAlias( self::$cachePath. $new_name);
            @unlink($imageFile);
        }
    }

    public static function getParamsByType($type = null)
    {
        return self::$typss[$type] ?? self::$typss['defaultPromo'];
    }

    /**
     * Вернуть фотку по умолчанию
     * @return string
     */
    public static function getPhotoDefault()
    {
        if (\common\helpers\App::isConsoleApplication()){
            return \Yii::$app->urlManager->baseUrl. '/v2/common/img/no-photo/no-photo_1000-1000.jpg';
        }else{
            return \frontend\assets\v2\CommonAsset::getAssetUrl('img/no-photo/no-photo_1000-1000.jpg');
        }
    }

    /**
     * Вернуть фотку по умолчанию для иконок цвета
     * @return string
     */
    public static function getColorPhotoDefault()
    {
        return \frontend\assets\v2\CommonAsset::getAssetUrl('img/no-photo/no-photo_70-70.jpg');
    }

    public static function getImageResized($src,$type)
    {
        return $src;
        //todo не работает нормально!
        if(self::$modResize) {
            return \Yii::$app->urlManager->createUrl([
                'images/view',
                'image' => $src,
                'type' => $type
            ]);
        }else{
            return $src;
        }
    }
}