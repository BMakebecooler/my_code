<?php

namespace modules\shopandshow\components\export;


use common\helpers\Url;
use common\interfaces\Export;
use common\lists\TreeList;
use common\models\NewProduct;
use common\models\Tree;
use DOMDocument;
use Exception;
use modules\shopandshow\models\shop\ShopProduct;
use skeeks\cms\components\Cms;
use skeeks\cms\exportShopYandexMarket\ExportShopYandexMarketHandler as SXExportShopYandexMarketHandler;
use skeeks\cms\helpers\FileHelper;
use skeeks\cms\shop\models\ShopCmsContentElement;
use yii\helpers\Console;

class CriteoHandler extends GoogleHandler implements Export
{

    public $xml = null;

    public function init()
    {
        parent::init();
        $this->name = 'Criteo';


        if (!$this->file_path) {
            $this->file_path = "/export/criteo/feed.xml";
        }
    }

    public function getProductQuery()
    {

        return parent::getProductQuery()
            ->hasQuantityNew();
//            ->prizeMoreThanZero();
    }


    public function getNameForCPC()
    {
        return 'criteo';
    }
}