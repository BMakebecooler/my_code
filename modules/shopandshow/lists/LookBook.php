<?php

/**
 * Created by PhpStorm.
 * User: ubuntu5
 * Date: 30.08.17
 * Time: 17:37
 */

namespace modules\shopandshow\lists;


use common\models\cmsContent\CmsContentElement;

class LookBook
{


    /**
     * Получить модель лук бука в котором есть - $productId
     * @param $productId
     * @return null|\yii\db\ActiveRecord
     */
    public static function getLbByProductId($productId)
    {
        $look = CmsContentElement::find();
        $look->innerJoin('cms_content_element_property AS property', 'cms_content_element.id = property.element_id AND 
            property.property_id = (SELECT id FROM `cms_content_property` WHERE `content_id` = :content_id AND `code` = :property_code)');
        $look->andWhere('cms_content_element.content_id = :content_id AND property.value = :product_id', [
            ':product_id' => $productId,
            ':content_id' => LOOKBOOK_CONTENT_ID,
            ':property_code' => 'products',
        ]);

        $look->orderBy('cms_content_element.id ASC');

        return $look->one();
    }

}