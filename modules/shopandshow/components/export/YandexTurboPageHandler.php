<?php

namespace modules\shopandshow\components\export;


use common\helpers\ArrayHelper;
use common\interfaces\Export;
use common\interfaces\ExportType;
use common\models\Tree;
use common\seo\YmlCatalogFactory;
use Exception;
use modules\shopandshow\models\shop\ShopProduct;
use skeeks\cms\components\Cms;
use skeeks\cms\exportShopYandexMarket\ExportShopYandexMarketHandler as SXExportShopYandexMarketHandler;
use skeeks\cms\helpers\FileHelper;
use skeeks\cms\models\CmsTree;
use skeeks\cms\shop\models\ShopCmsContentElement;
use yii\helpers\Console;

/**
 * Class YandexHandler
 * @package modules\shopandshow\components\export
 * @property string $nameType
 */
class YandexTurboPageHandler extends BaseHandler implements ExportType
{

    public function init()
    {
        parent::init();
        $this->name = 'YandexTurboPage';

        if (!$this->file_path) {
            $this->file_path = "/export/yandex-yml/yml.xml";
        }
    }

    public function getNameType()
    {
        return 'yandex-turbo-page';
    }

    public function export()
    {
        YmlCatalogFactory::create()
            ->make()
            ->save($this->rootFilePath);
    }
}