<?php

use common\models\cmsContent\CmsContent;
use common\models\cmsContent\CmsContentElement;
use common\models\cmsContent\CmsContentProperty;
use skeeks\cms\models\CmsContentElementProperty;
use yii\db\Migration;

class m180920_120142_add_content_property_dioptrii extends Migration
{
    private $propertyCode = 'dioptrii';
    private $vendorId = 646;

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $propertyCodeKfss = $this->generateKfssCode($this->propertyCode);

        $cmsContentKfss = CmsContent::findOne(['code' => $propertyCodeKfss]);
        $cmsContent = CmsContent::findOne(['code' => $this->propertyCode]);
        if (!$cmsContent && !$cmsContentKfss){
            $cmsContent = new CmsContent();

            $cmsContent->code = $this->propertyCode;
            $cmsContent->name = "Диоптрии";
            $cmsContent->content_type = 'info';

            if(!$cmsContent->save()) {
                var_dump($cmsContent->getErrors());
            }
        }

        $cmsContentPropertyKfss = CmsContentProperty::findOne(['code' => $propertyCodeKfss, 'content_id' => OFFERS_CONTENT_ID]);
        $cmsContentProperty = CmsContentProperty::findOne(['code' => $this->propertyCode, 'content_id' => OFFERS_CONTENT_ID]);

        if (!$cmsContentProperty && !$cmsContentPropertyKfss){
            $cmsContentProperty = new CmsContentProperty();
            $cmsContentProperty->name = 'Диоптрии';
            $cmsContentProperty->code = $this->propertyCode;
            $cmsContentProperty->content_id = OFFERS_CONTENT_ID;
            $cmsContentProperty->active = \skeeks\cms\components\Cms::BOOL_Y;
            $cmsContentProperty->property_type = 'L';
            $cmsContentProperty->list_type = 'L';
            $cmsContentProperty->multiple = \skeeks\cms\components\Cms::BOOL_N;
            $cmsContentProperty->component = 'skeeks\cms\relatedProperties\propertyTypes\PropertyTypeElement';
            $cmsContentProperty->vendor_id = $this->vendorId;

            if(!$cmsContentProperty->save()) {
                var_dump($cmsContentProperty->getErrors());
            }
        }

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $cmsContentProperty = CmsContentProperty::findOne(['code' => $this->propertyCode, 'content_id' => OFFERS_CONTENT_ID]);
        if ($cmsContentProperty){
            CmsContentElementProperty::deleteAll(['property_id' => $cmsContentProperty->id]);
            $cmsContentProperty->delete();

            $cmsContent = CmsContent::findOne(['code' => $this->propertyCode]);
            if ($cmsContent){
                CmsContentElement::deleteAll(['content_id' => $cmsContent->id]);
                $cmsContent->delete();
            }
        }
    }

    protected function generateKfssCode($string)
    {
        return 'KFSS_'.mb_strtoupper(str_replace('-', '_', \common\helpers\Strings::translit($string)));
    }
}
