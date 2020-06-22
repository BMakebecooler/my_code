<?php

/**
 * Виджет для лукабука
 */

namespace common\widgets\content\lookbook;

use common\models\cmsContent\CmsContentElement;
use common\widgets\content\ContentElementWidget;
use skeeks\cms\components\Cms;
use yii\base\Widget;

class LookBook extends Widget
{

    public $label = '';

    public $viewFile = '@template/widgets/Lookbook/home';
    public $viewListFile = '@template/widgets/Lookbook/_list';

    /**
     * @var int
     */
    public $limitProduct = 8;

    /**
     * @var CmsContentElement
     */
    public $model = null;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        return $this->render($this->viewFile);
    }

    public function getList()
    {
        $cmsContentElement = new ContentElementWidget([
            'contentElementClass' => CmsContentElement::className(),
            'namespace' => 'ContentElementsCmsWidget-lookbook',
            'viewFile' => $this->viewListFile,
            'active' => Cms::BOOL_Y,
            'limit' => 20,
            'orderBy' => 'priority',
            'isAdditionalConditional' => false,
            'content_ids' => [LOOKBOOK_SECTION_CONTENT_ID],
            'enabledCurrentTree' => false,
            'enabledRunCache' => Cms::BOOL_N,
            'runCacheDuration' => HOUR_5,
            'data' => [
                /*                'thumbnail' => [
                                    'width' => 180,
                                    'height' => 180
                                ],*/
            ],
            'dataProviderCallback' => function (\yii\data\ActiveDataProvider $activeDataProvider) {

                $query = $activeDataProvider->query;

//            $query->with('relatedProperties');
//            $query->innerJoinWith('shopProduct');
//            $query->innerJoinWith('price');
//            $query->with('cmsTree');
            }
        ]);

        return $cmsContentElement->run();

    }

}