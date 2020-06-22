<?php

namespace modules\shopandshow\components\export;


use common\interfaces\Export;
use common\lists\TreeList;
use common\models\Tree;
use DOMDocument;
use Exception;
use modules\shopandshow\models\shop\ShopProduct;
use skeeks\cms\components\Cms;
use skeeks\cms\exportShopYandexMarket\ExportShopYandexMarketHandler as SXExportShopYandexMarketHandler;
use skeeks\cms\helpers\FileHelper;
use skeeks\cms\shop\models\ShopCmsContentElement;
use yii\helpers\Console;

class ExportShopGoogleMerchantCenterHandler extends GoogleHandler implements Export
{

    public $xml = null;

    private $trees = [];

    private $site = 'www2';

    public function init()
    {
        $catalogTree = TreeList::getTreeById(TreeList::CATALOG_ID);
        $this->trees = $catalogTree->getDescendants()->indexBy('id')->all();

        parent::init();
        $this->name ='Google export';
    }

    public function getNameForCPC()
    {
        return 'google';
        // TODO: Implement getNameForCPC() method.
    }
}