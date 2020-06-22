<?php

use common\models\cmsContent\CmsContentProperty;
use yii\db\Migration;

/**
 * Class m171201_085600_insert_cms_content_property_prices_vary
 */
class m171201_085600_insert_cms_content_property_prices_vary extends Migration
{
    const CODE = 'PRICES_VARY';

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $cmsContentProperty = CmsContentProperty::findOne(['code' => self::CODE]);
        if ($cmsContentProperty) return;

        $cmsContentProperty = new CmsContentProperty();
        $cmsContentProperty->name = 'Цены модификаций различаются';
        $cmsContentProperty->code = self::CODE;
        $cmsContentProperty->content_id = PRODUCT_CONTENT_ID;
        $cmsContentProperty->active = \skeeks\cms\components\Cms::BOOL_Y;
        $cmsContentProperty->property_type = 'S';
        $cmsContentProperty->list_type = 'L';
        $cmsContentProperty->multiple = \skeeks\cms\components\Cms::BOOL_N;
        $cmsContentProperty->component = 'skeeks\cms\relatedProperties\propertyTypes\PropertyTypeString';

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
