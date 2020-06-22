<?php

/**
 * Виджет для акции лукабук клиента
 */

namespace common\widgets\content\lookbook;

use common\models\cmsContent\CmsContentElement;
use common\widgets\content\ContentElementWidget;
use modules\shopandshow\models\users\UserVotes;
use skeeks\cms\components\Cms;
use yii\base\Widget;

class LookBookClients extends Widget
{
    public $viewFile = '@template/widgets/Lookbook/clients/clients';
    public $viewListFile = '@template/widgets/Lookbook/clients/_client_list';

    public $tree_id;

    public function run()
    {
        return $this->render($this->viewFile);
    }

    public function getList()
    {
        $cmsContentElement = new ContentElementWidget([
            'contentElementClass' => CmsContentElement::className(),
            'namespace' => 'ContentElementsCmsWidget-lookbook-clients',
            'viewFile' => $this->viewListFile,
            'active' => Cms::BOOL_Y,
            'limit' => 20,
            'orderBy' => 'priority',
            'isAdditionalConditional' => false,
            'content_ids' => [LOOKBOOK_CLIENTS_CONTENT_ID],
            'enabledCurrentTree' => true,
            'enabledRunCache' => Cms::BOOL_N,
            'runCacheDuration' => HOUR_5,
            'data' => [
                'thumbnail' => [
                    'width' => 220,
                    'height' => 220,
                ],
                'userVotes' => UserVotes::findForUser(UserVotes::findForLookbook())->indexBy('cms_content_element_id')->all()
            ],
            'dataProviderCallback' => function (\yii\data\ActiveDataProvider $activeDataProvider) {

//            $query = $activeDataProvider->query;

//            $query->with('relatedProperties');
//            $query->innerJoinWith('shopProduct');
//            $query->innerJoinWith('price');
//            $query->with('cmsTree');
            },
        ]);

        return $cmsContentElement->run();

    }

}