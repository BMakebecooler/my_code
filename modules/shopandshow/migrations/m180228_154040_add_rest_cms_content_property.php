<?php

use common\models\cmsContent\CmsContentProperty;
use yii\db\Migration;

class m180228_154040_add_rest_cms_content_property extends Migration
{
    const CODE = 'REST';

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createProperty(PRODUCT_CONTENT_ID, 310);
        $this->createProperty(OFFERS_CONTENT_ID, 311);
    }

    private function createProperty($contentId, $vendorId)
    {
        $cmsContentProperty = CmsContentProperty::findOne(['code' => self::CODE, 'content_id' => $contentId]);
        if ($cmsContentProperty) return;

        $cmsContentProperty = new CmsContentProperty();
        $cmsContentProperty->name = 'Остаток';
        $cmsContentProperty->code = self::CODE;
        $cmsContentProperty->content_id = $contentId;
        $cmsContentProperty->active = \skeeks\cms\components\Cms::BOOL_Y;
        $cmsContentProperty->property_type = 'N';
        $cmsContentProperty->list_type = 'L';
        $cmsContentProperty->multiple = \skeeks\cms\components\Cms::BOOL_N;
        $cmsContentProperty->component = 'skeeks\cms\relatedProperties\propertyTypes\PropertyTypeNumber';
        $cmsContentProperty->vendor_id = $vendorId;

        if(!$cmsContentProperty->save()) {
            var_dump($cmsContentProperty->getErrors());
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        CmsContentProperty::deleteAll(['code' => self::CODE]);
    }
}
