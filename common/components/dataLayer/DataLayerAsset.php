<?php

    /**
     * @author Arkhipov Andrei <arhan89@gmail.com>
     * @copyright (c) K-Gorod
     * Date: 11.04.2019
     * Time: 8:39
     */

    namespace common\components\dataLayer;

    use yii\web\AssetBundle;

    class DataLayerAsset extends AssetBundle
    {
        public $sourcePath = '@common/components/dataLayer/assets';
        public $js = [
            'js/dataLayer.js',
        ];
    }