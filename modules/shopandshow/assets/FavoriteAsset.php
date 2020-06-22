<?php

namespace modules\shopandshow\assets;

use yii\web\AssetBundle;

/**
 * Class FavoriteAsset
 * @package modules\shopandshow\assets
 */
class FavoriteAsset extends AssetBundle
{
    public $sourcePath = '@modules/shopandshow/assets';

    public $css = [
    ];

    public $js =
        [
            'classes/favorites/Favorite.js?v=180201-1',
        ];

    public $depends = [
        '\skeeks\sx\assets\Core',
//        '\skeeks\sx\assets\Custom',
    ];
}
