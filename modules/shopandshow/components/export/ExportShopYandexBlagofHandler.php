<?php


namespace modules\shopandshow\components\export;


use common\interfaces\Export;
use common\models\Brand;
use yii\db\Exception;

class ExportShopYandexBlagofHandler extends YandexHandler implements Export
{
    const BRAND_GUID = '75442377ED80D1BBE0538301090A7D27';
//    const BRAND_CODE = 'blagof';

    private $modelBrand;

    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
        $this->name = 'YandexMarketBlagof';

        if (!$this->file_path) {
            $this->file_path = "/export/yandex-market/feed-blagof.xml";
        }

        $this->modelBrand = Brand::find()
            ->byGuid(self::BRAND_GUID)
//            ->andWhere(['code' => self::BRAND_CODE])
            ->one();

        if (!$this->modelBrand) {
            throw new Exception('Brand model not found');
        }
    }


    public function getProductQuery()
    {
        return parent::getProductQuery()
            ->byBrand($this->modelBrand->id);
    }

    public function getNameForCPC()
    {
        return 'YandexMarketBlagof';
        // TODO: Implement getNameForCPC() method.
    }
}