<?php

/**
 * php ./yii export/yandex-products/feed-google-flash-price
 * php ./yii export/yandex-products/market
 * php ./yii export/yandex-products/feed
 * php ./yii export/yandex-products/feed-google
 * php ./yii export/yandex-products/feed-flash-price
 * php ./yii export/yandex-products/site-map
 * php ./yii export/yandex-products/black-friday-feed
 * php ./yii export/yandex-products/black-friday-feed-alt
 * php ./yii export/yandex-products/retail-rocket
 * php ./yii export/yandex-products/floctory
 * php ./yii export/yandex-products/criteo
 * php ./yii export/yandex-products/blagof
 */

namespace console\controllers\export;

use common\models\cmsContent\CmsContentElement;
use common\models\Tree;
use common\thumbnails\Thumbnail;
use modules\shopandshow\components\export\ExportSitemapHandler;
use modules\shopandshow\models\shop\ShopContentElement;
use modules\shopandshow\models\shop\ShopProduct;
use modules\shopandshow\models\shop\SsShopProductPrice;
use skeeks\cms\components\Cms;
use skeeks\cms\export\models\ExportTask;
use skeeks\cms\helpers\FileHelper;
use skeeks\cms\models\CmsContentElementProperty;
use yii\helpers\Console;

/**
 * Class YandexProductsController
 * @package console\controllers
 */
class YandexProductsController extends ExportController
{

    public function actionSiteMap()
    {
        return $this->export(1);
    }

    /**
     * Рекламный фид - /export/yandex/feed-blagof.xml
     */
    public function actionBlagof()
    {
        return $this->export(16);
    }


    /**
     * Рекламный фид - /export/yandex-market/feedMarket.xml
     */
    public function actionMarket()
    {
        return $this->export(14);
    }

    /**
     * Рекламный фид - /export/yandex-market/feed.xml
     */
    public function actionFeed()
    {
        return $this->export(2);
    }

    /**
     * Рекламный фид яндекс выгода на час - /export/yandex-market/feed-flashprice.xml
     */
    public function actionFeedFlashPrice()
    {
        return $this->export(15);
    }

    /**
     * Рекламный фид яндекс выгода на час - /export/google/feed-flashprice.xml
     */
    public function actionFeedGoogleFlashPrice()
    {
        return $this->export(17);
    }

    /**
     * Рекламный фид /export/retailrocket/feed.xml
     */
    public function actionRetailRocket()
    {
        return $this->export(10);
    }

    /**
     * Рекламный фид /export/floctory/feed.xml
     */
    public function actionFloctory()
    {
        return $this->export(9);
    }

    /**
     * Рекламный фид /export/criteo/feed.xml
     */
    public function actionCriteo()
    {
        return $this->export(8);
    }

    /**
     * Рекламный фид /export/google/feed.xml
     */
    public function actionFeedGoogle()
    {
        return $this->export(3);
    }

    public function actionFeedPriceRu()
    {
        return $this->export(4);
    }

    public function actionBlackFridayFeed()
    {
        $productsIds = [
            5236476,
            4881231,
            4862684,
            5134723,
            1688594,
            2220968,
            3269847,
            1988729,
            1795728,
            5362142,
            4284041,
            4010449,
            5796918,
            5030931,
            3590418,
            3905936,
            5798083,
            2464041,
            2464191,
            2723101,
            2940991,
            4230498,
            4230505,
            4230510,
            4578800,
            5624093,
            4400651,
            4748909,
            4910380,
            161585,
            1108822,
            2023768,
            3190991,
            3280324,
            3616122,
            4723701,
            1824970,
            1847489,
            2190805,
            2629116,
            1778492,
            6124463,
            1726052,
            4465735,
            6094059,
            6110779,
            3381059,
            1763891,
            1852192,
            5957875,
            5880271,
            6071215,
            6090107,
            4244874,
            3406559,
            3520140,
            3625015,
            6126829,
            3519730,
            6121099,
            2235833,
            3713524,
            3761904,
            3520789,
            1711202,
            5957869,
            3519822,
            3743827,
            3796950,
            6100158,
            6080383,
            5679350,
            6034759,
            6091792,
            3613058,
            6134293,
            3641540,
            6103308,
            3743825,
            4096302,
            5880282,
            6124397,
            6110789,
            6098721,
            6124466,
            5718722,
            5609317,
            3721291,
            5977993,
            1625867,
            3641030,
            3498968,
            5610710,
            3795748,
            6034143,
            4032643,
            5928157,
            1120711,
            2622436,
            3296775,
            2235832,
            5779608,
            1990414,
            4329770,
            4307636,
            2848977,
            6079716,
            1241293,
            3667795,
            3021387,
            6097095,
            3224180,
            4277530,
            4583229,
            4379199,
            4447916,
            3339032,
            4928761,
            4575517,
            4422521,
            25792,
            4268205,
            2570746,
            6105785,
            6087027,
            1958979,
            5086415,
            4955511,
            4560187,
            4465699,
            4773933,
            4926504,
            2280709,
            4352135,
            3796320,
            4575685,
            5897196,
            4365359,
            4795615,
            6086996,
            3677775,
            5609315,
            6043172,
            1915216,
            4724503,
            2139173,
            3003759,
            1292464,
            1894682,
            5248925,
            5424044,
            1600748,
            5806612,
            1875536,
            3269847,
            3537552,
            5016024,
            5052939,
            4358237,
            1063468,
            1779855,
            3031940,
            1805840,
            4417159,
            6074114,
            5599873,
            4575559,
            4627619,
            4324887,
            3933193,
            5287377,
            5880281,
            5961790,
            25658,
            1677867,
            5451207,
            3412476,
            5134723,
            5679531,
            5221445,
            2334302,
            3314647,
            5044074,
            4010848,
            2010870,
            4726527,
            4617959,
            6117994,
            3197257,
            6043289,
            2292684,
            5776757,
            1656715,
            5270683,
            2766913,
            5293999,
            3384738,
            5293591,
            3972932,
            3000551,
            5573749,
            3762387,
            5806609,
            1953142,
            5777397,
            4118878,
            3697288,
            2652478,
            4505773,
            1367431,
            5717178,
            5610514,
            1688798,
            126298,
            5294517,
            2493889,
            3693982,
            3996640,
            1652343,
            5539530,
            4435800,
            5083477,
            4881231,
            6088352,
            3660802,
            4918580,
            3629275,
            4457358,
            2140746,
            1120992,
            3300257,
            2371124,
            4812131,
            5670572,
            6069805,
            5044761,
            25158,
            5769143,
            5994283,
            2164189,
            5052947,
            4748817,
            2220968,
            2484780,
            6079546,
            4617960,
            4454422,
            2240546,
            2227051,
            6099168
        ];

        return $this->exportBlackFridayFeed($productsIds);
    }

    public function actionBlackFridayFeedAlt()
    {
        $productsIds = [
            1090532,
            1352674,
            1418026,
            1418544,
            1543274,
            1654846,
            1654956,
            1689519,
            1696817,
            1756393,
            1875681,
            1954958,
            1994992,
            2023768,
            2048785,
            2140755,
            2292684,
            2300561,
            2300580,
            2370807,
            2383705,
            2405064,
            2412459,
            2466763,
            2511654,
            2511704,
            2561713,
            2766913,
            2808190,
            2925092,
            3011443,
            3161523,
            3190991,
            3239583,
            3241002,
            3419285,
            3421108,
            3453367,
            3533594,
            3585664,
            3585819,
            3626357,
            3628340,
            3677812,
            3744244,
            3828105,
            4112236,
            4163239,
            4293240,
            4365439,
            4496959,
            4670565,
            4757163,
            4783046,
            4783047,
            4783595,
            4977904,
            5016024,
            5354008,
            5378213,
            5378479,
            5468154,
            5624093,
            5670574,
            5805410,
            5806610,
            5973182,
            6077486,
            6077497,
            6080188,
            6099168,
            12892,
            18246,
            96056,
            125167,
            130048,
            606553,
            5126538,
            6080408,
            5178620,
            2554146,
            4617318,
            3011123,
            5685948,
            2222306,
            3412539,
            5677225,
            1353119,
            5231026,
            4161261,
            5220944,
            2466197,
            1485228,
            2599211,
            2681953,
            4926529,
            4906863,
            1329902,
            4617313,
            3183750,
            2550238
        ];

        return $this->exportBlackFridayFeed($productsIds, 100);
    }

    protected function export($id)
    {
        if ($exportTask = ExportTask::findOne($id)) {


            /**
             * @var $handler
             */
            $handler = $exportTask->handler;

            if ($handler) {
                $result = $handler->export();
                $log = (string)$result;

                var_dump($handler->file_path);
            }
        }
    }

    protected function exportBlackFridayFeed($productsIds, $limit = 250)
    {
        $this->stdout("Генерация фида для черной пятницы" . PHP_EOL, Console::FG_YELLOW);

        ini_set("memory_limit", "8192M");
        set_time_limit(0);

        $MIN_H = 603;
        $MIN_W = 603;

        $productsFeedData = [];

        $products = CmsContentElement::find()
            ->alias('product')
//            ->innerJoin(ShopProduct::tableName() . ' as shop_product', "shop_product.id=product.id")
            ->innerJoin(SsShopProductPrice::tableName() . ' as price', "price.product_id=product.id")
            ->leftJoin(CmsContentElementProperty::tableName() . ' as not_public', "not_public.element_id=product.id AND not_public.property_id=83")
            ->andWhere(['product.content_id' => PRODUCT_CONTENT_ID])
            ->andWhere(['product.bitrix_id' => $productsIds])
            ->andWhere(['product.active' => Cms::BOOL_Y])
            ->andWhere(['>', 'product.image_id', 0])
            ->andWhere(['>', 'new_quantity', 0])
            ->andWhere(['>', 'price.price', 2])
            ->andWhere(['OR', ['not_public.value' => null], ['not_public.value' => '']])
            ->limit((int)$limit)
            ->all();

        $this->stdout("Товаров выбрано = " . count($products) . PHP_EOL, Console::FG_YELLOW);

        if ($products) {
            /** @var CmsContentElement $product */
            foreach ($products as $product) {
                $shopCmsCE = new ShopContentElement($product->toArray());
                $shopProduct = ShopProduct::getInstanceByContentElement($shopCmsCE);
                /** @var Tree $productTree */
                $productTree = $product->getCmsTree()->one();

                $productsFeedData[$product->id] = [
                    'name' => $product->name,
                    'description' => trim(htmlspecialchars(strip_tags(htmlspecialchars_decode($product->relatedPropertiesModel->getAttribute('HARAKTERISTIKI'))))),
                    'oldprice' => $shopProduct->maxPrice(),
                    'price' => $shopProduct->basePrice(),
                    'discount' => (int)$shopProduct->badgeDiscount() . '%',
                    'category' => $productTree->name,
                    'category2' => '',
                    'promo' => '',
                    'url' => $product->getAbsoluteUrl(),
                    'picture' => \Yii::$app->imaging->thumbnailUrlSS($shopCmsCE->image->src,
                        new Thumbnail([
                            'h' => $MIN_H,
                            'w' => $MIN_W,
                        ])
                    ),
                    'picture2' => '',
                    'picture3' => '',
                    'picture4' => '',
                    'keywords' => '',
                ];
            }

            if ($productsFeedData) {

                $this->stdout("Заполняю XLSX файл данными" . PHP_EOL, Console::FG_YELLOW);

                \PhpOffice\PhpSpreadsheet\Settings::setLocale('ru');

                $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

                $spreadsheet->getProperties()
                    ->setTitle('Фид для черной пятницы')
                    ->setSubject('Фид для черной пятницы');

                $spreadsheet->getActiveSheet()
                    ->setTitle('Products');

                $spreadsheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);

                $sheet = $spreadsheet->getActiveSheet();

                $sheet->setCellValue('A1', 'name')
                    ->setCellValue('B1', 'description')
                    ->setCellValue('C1', 'oldprice')
                    ->setCellValue('D1', 'price')
                    ->setCellValue('E1', 'discount')
                    ->setCellValue('F1', 'category')
                    ->setCellValue('G1', 'category')
                    ->setCellValue('H1', 'promo')
                    ->setCellValue('I1', 'url')
                    ->setCellValue('J1', 'picture')
                    ->setCellValue('K1', 'picture')
                    ->setCellValue('L1', 'picture')
                    ->setCellValue('M1', 'picture')
                    ->setCellValue('N1', 'keywords');

                foreach (range('A', 'N') as $colId) {
                    $sheet->getColumnDimension($colId)->setAutoSize(true);
                }

                $sheet->fromArray($productsFeedData, null, 'A2', true);
                //$sheet->setAutoFilter($spreadsheet->getActiveSheet()->calculateColumnWidths()->calculateWorksheetDimension());

                $dir = \Yii::getAlias('@frontend/web/export/yandex-market/');
                $filename = "black-friday-" . ((int)$limit) . ".xlsx"; //save our workbook as this file name
                $fullPath = $dir . $filename;

                //Создание дирректории
                if ($dirName = dirname($fullPath)) {
                    $this->stdout("Создание дирректории" . PHP_EOL);

                    if (!is_dir($dirName) && !FileHelper::createDirectory($dirName)) {
                        throw new \Exception("Не удалось создать директорию для файла");
                    }
                }

                $this->stdout("Сохраняю файл: " . $fullPath . PHP_EOL, Console::FG_YELLOW);

                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                //$writer->setPreCalculateFormulas(true);
                $writer->save($fullPath);
            }
        }

        $this->stdout("Готово" . PHP_EOL, Console::FG_GREEN);

        return true;
    }
}