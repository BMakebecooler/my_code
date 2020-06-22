<?php

namespace modules\shopandshow\assets;

use yii\web\AssetBundle;

/**
 * Class ShopAndShowAsset
 * @package modules\shopandshow\assets
 */
class ShopAndShowAsset extends AssetBundle
{
    public $sourcePath = '@modules/shopandshow/assets';

    public $css = [
    ];

    public $js =
        [
            'actions/files/files.js',
        ];

    public $depends = [
        '\skeeks\sx\assets\Core',
        '\skeeks\sx\assets\Widget',
        '\skeeks\widget\simpleajaxuploader\Asset',
    ];
}
