<?php
namespace common\helpers;

use skeeks\cms\helpers\CmsContentElementHelper;
use skeeks\cms\models\CmsContentElement;
use common\models\Product;

/**
 * @property CmsContentElement $brand
 * @property string $article
 * Class User
 * @package common\models
 */
class ProductHelper extends CmsContentElementHelper
{
    public function getArticle()
    {
        return (string)$this->model->relatedPropertiesModel->getAttribute('article');
    }

    /**
     * @return CmsContentElement
     */
    public function getBrand()
    {
        return CmsContentElement::findOne((int)$this->model->relatedPropertiesModel->getAttribute('brand'));
    }

    public function getCharacters()
    {
        return $this->model->relatedPropertiesModel->getAttribute('characters');
    }

    public function getTextUnderBasket()
    {
        return $this->model->relatedPropertiesModel->getAttribute('textUnderBasket');
    }


    /**
     * Есть ли видео о товаре
     * @return bool
     */
    public function isVideo()
    {

        if(!User::isDeveloper()){
            return false;
        }

        $priceCode = $this->model->price->typePrice->code;

        if ($priceCode == 'DISCOUNT') {
            $priceCode = 'DISCOUNTED';
        } elseif ($priceCode == 'SHOPANDSHOW') {
            $priceCode = 'BASE';
        }

        $paramName = 'VIDEO_PRICE_' . $priceCode;

        $sql = <<<SQL
SELECT param.value  AS video
FROM cms_content_element_property AS param 
INNER JOIN cms_content_property AS property ON property.id = param.property_id
WHERE param.element_id = :element_id AND property.code = :code
SQL;

        $data = \Yii::$app->db->createCommand($sql, [
            ':element_id' => $this->model->id,
            ':code' => $paramName,
        ])->queryOne();

        return ($data) ? $data['video'] : false;
    }


    /**
     * Есть ли 3д о товаре
     * @return bool
     */
    public function is3D()
    {
        /** пока нет этого */
        return false;
    }

    public function getCardsIds()
    {
        $return = [];
//        $offers =  Product::getProductOffersCanSale($this->model->id);
        $offers = Product::find()->where(['parent_content_element_id' => $this->model->id])->all();
        foreach ($offers as $offer) {
            $return[] = $offer->id;
        }
        return $return;

    }
}