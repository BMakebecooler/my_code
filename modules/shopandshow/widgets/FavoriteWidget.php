<?php
namespace modules\shopandshow\widgets;

use modules\shopandshow\assets\FavoriteAsset;
use skeeks\cms\helpers\UrlHelper;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * Class FavoriteWidget
 * @package modules\shopandshow\widgets
 */
class FavoriteWidget extends Widget
{
    static public $isRegisteredAssets = false;

    public $clientOptions = [];

    public function init()
    {
        parent::init();
        $this->clientOptions = ArrayHelper::merge($this->baseClientOptions(), $this->clientOptions);
    }

    /**
     * @return array
     */
    public function baseClientOptions()
    {
        return [
            'backend-change-favorite' => UrlHelper::construct('shopandshow/favorite/change')->toString(),
            'backend-get-my-favorite' => UrlHelper::construct('shopandshow/favorite/get-my-favorite')->toString(),
        ];
    }

    /**
     * Подготовка данных для шаблона
     * @return $this
     */
    public function run()
    {
        if (static::$isRegisteredAssets === false) {
            FavoriteAsset::register($this->getView());

            $options = (array)$this->clientOptions;
            $options = Json::encode($options);

            $this->getView()->registerJs(<<<JS
    (function(sx, $, _) {
        sx.Favorite = new sx.classes.favorite.App($options);
        
        sx.Favorite.bind('changeFavorite', function()
        {
            sx.Observer.trigger('changeFavorite');
        });
        
    })(sx, sx.$, sx._);
JS
            );

            static::$isRegisteredAssets = true;
        }

        return parent::run();
    }


}
