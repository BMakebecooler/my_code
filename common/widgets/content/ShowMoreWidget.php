<?php
/**
 * Created by PhpStorm.
 * User: Soskov_da
 * Date: 30.08.2017
 * Time: 11:11
 */

namespace common\widgets\content;

use function GuzzleHttp\Psr7\parse_query;
use skeeks\cms\base\Widget;

class ShowMoreWidget extends Widget
{

    /** @var string $pageParam название переменной текущей страницы пейджера */
    public $pageParam;

    /** @var string $url адрес, откуда забирать новые данные */
    public $url;

    /** @var int $page номер страницы */
    public $page;

    /** @var \yii\data\ActiveDataProvider $dataProvider */
    public $dataProvider;

    /** @var \common\widgets\content\ContentElementWidget */
    public $contentElementWidget;

    /** @var string $event */
    public $event = 'click';

    /** @vat string $eventSourceSelector */
    public $eventSourceSelector;

    /** @var  string js selector элемента, в который загружать новые данные */
    public $destinationSelector;

    /** @var  array массив доп. атрибутов в GET запросе */
    public $attrs = [];

    /** @var string js callback function */
    public $callback = '{}';

    /** @vat string $eventSourcePrevSelector */
    public $eventSourcePrevSelector;

    /** @var string js callback function */
    public $callbackPrev = '{}';

    public function init()
    {
        parent::init();

        if (!($this->contentElementWidget || $this->dataProvider)){
            throw new \Exception('contentElementWidget or dataProvider not specified');
        }

        $this->dataProvider = ($this->dataProvider) ?: $this->contentElementWidget->dataProvider;

        if (empty($this->pageParam)) $this->pageParam = $this->dataProvider->pagination->pageParam ?: 'page';
        if (empty($this->page)) $this->page = (int)\Yii::$app->request->get($this->pageParam, 1);
        if (empty($this->url)) $this->url = \Yii::$app->request->url;

        $this->removePageFromUrl();
    }

    public function run()
    {
        if ($this->contentElementWidget) {
            $totalCount = $this->contentElementWidget->getCountCategory();
        } else {
            $totalCount = $this->dataProvider->totalCount;
        }

        $totalPages = ceil($totalCount / $this->dataProvider->pagination->pageSize);

        $requestData = \common\helpers\ArrayHelper::merge(['page' => $this->page], $this->attrs);
        $requestData = json_encode($requestData);

        \Yii::$app->view->registerJs(<<<JS
	(function(sx, $, _){
        var dest = $('{$this->destinationSelector}');
        var preLoaded = new sx.classes.PreLoaded();
        var requestData = {$requestData};
        
        $(document).on('{$this->event}', '{$this->eventSourceSelector}', function () {
            preLoaded.show();
            var source = $(this);
            var callback = {$this->callback};
            requestData.page++;
            
            $.ajax({
                type: 'get',
                url: '{$this->url}',
                data: requestData,
                success: function(data)
                {
                    window.history.pushState(null, "", this.url.replace('&infinite=1', ''));
                    
                    dest.append(data);
                    
                    preLoaded.hide();
                    if (requestData.page >= {$totalPages}) {
                        source.hide();
                    }
                    
                    if (sx.Favorite) {
                        sx.Favorite.showFavorites();
                        sx.Favorite.addFavoritesClickHandler();
                    }
                    
                    if (typeof callback === 'function') {
                        callback(requestData);
                    }
                },
                fail: function(e, data)
                {
                    preLoaded.hide();
                    sx.notify.error('Не удалось загрузить данные');
                }
            });    
           
        });
	})(sx, sx.$, sx._);
JS
        );

        if ($this->eventSourcePrevSelector) {
            \Yii::$app->view->registerJs(<<<JS
	(function(sx, $, _){
        var dest = $('{$this->destinationSelector}');
        var preLoaded = new sx.classes.PreLoaded();
        var requestData = {$requestData};
        
        $(document).on('{$this->event}', '{$this->eventSourcePrevSelector}', function () {
            preLoaded.show();
            var source = $(this);
            var callback = {$this->callbackPrev};
            requestData.page--;
            
            $.ajax({
                type: 'get',
                url: '{$this->url}',
                data: requestData,
                success: function(data)
                {
                    dest.prepend(data);
                    
                    preLoaded.hide();
                    
                    if (requestData.page <= 1) {
                        source.hide();
                    }
                    
                    if (sx.Favorite) {
                        sx.Favorite.showFavorites();
                        sx.Favorite.addFavoritesClickHandler();
                    }
                    
                    if (typeof callback === 'function') {
                        callback(requestData);
                    }
                },
                fail: function(e, data)
                {
                    preLoaded.hide();
                    sx.notify.error('Не удалось загрузить данные');
                }
            });    
           
        });
	})(sx, sx.$, sx._);
JS
            );
        }
    }

    public static function isAjax()
    {
        return \Yii::$app->request->isAjax && !\Yii::$app->request->isPjax;
    }

    public static function ajaxContent(\yii\web\View $view, $out)
    {
        if (self::isAjax()) {

            $view->context->layout = null;

            echo $out;
            \Yii::$app->end();
        }
    }

    private function removePageFromUrl()
    {
        $parts = parse_url($this->url);
        if (!isset($parts['query'])) {
            return;
        }

        $query = parse_query($parts['query']);
        unset($query['page']);

        if ($query) {
            $parts['query'] = '?' . http_build_query($query);
        } else {
            unset($parts['query']);
        }
        $this->url = join('', $parts);
    }
}