<?php
/**
 * Created by PhpStorm.
 * User: nikitaignatenkov
 * Date: 09/01/2019
 * Time: 18:50
 */

namespace common\models;


use common\helpers\ArrayHelper;
use common\helpers\Url;
use common\models\cmsContent\CmsContentElement;
use common\models\cmsContent\CmsContentProperty;
use common\models\query\NewProductQuery;
use common\thumbnails\Thumbnail;
use modules\shopandshow\models\shop\ShopProduct;
use skeeks\cms\components\Cms;
use skeeks\cms\models\CmsContentElementProperty;
use skeeks\cms\models\CmsTreeProperty;
use skeeks\cms\models\CmsTreeTypeProperty;


/**
 * Class NewProduct
 * @property ShopProduct shopProduct
 * @package common\models
 *
 * @property $thumbnail
 *
 */
class NewProduct extends CmsContentElement
{

    const LOT = 2;
    const CARD = 5;
    const MOD = 10;


//    public function behaviors()
//    {
//        return [
//            'history' => [
//                'class' => \nhkey\arh\ActiveRecordHistoryBehavior::className(),
//                'ignoreFields' => [
//                    'id',
//                    'created_by',
//                    'updated_by',
//                    'created_at',
//                    'updated_at',
//                    'published_at',
//                    'published_to',
//                    'priority',
//                    'active',
//                    'name',
//                    'image_id',
//                    'image_full_id',
//                    'code',
//                    'description_short',
//                    'description_full',
//                    'content_id',
//                    'tree_id',
//                    'show_counter',
//                    'show_counter_start',
//                    'meta_title',
//                    'meta_description',
//                    'meta_keywords',
//                    'description_short_type',
//                    'description_full_type',
//                    'parent_content_element_id',
//                    'bitrix_id',
//                    'is_base',
//                    'count_children',
//                    'guid_id',
//                    'kfss_id',
//                    'count_images',
//                    'new_guid',
//                    'new_lot_num',
//                    'new_lot_name',
//                    'new_characteristics',
//                    'new_technical_details',
//                    'new_product_kit',
//                    'new_advantages',
//                    'new_advantages_addons',
//                    'new_not_public',
//                    'new_quantity',
//                    'new_rest',
////                    'new_price_active',
////                    'new_price',
////                    'new_price_old',
////                    'new_discount_percent',
//                    'new_brand_id',
//                    'new_season_id',
//                    'new_rating',
//                ]
//            ]
//        ];
//    }

    /**
     * @return NewProductQuery
     */
    public static function getQueryForFeed()
    {

        $query = self::find()
            ->innerJoin('cms_tree', 'cms_tree.id=cms_content_element.tree_id')
            ->innerJoin('shop_product', 'shop_product.id=cms_content_element.id')
            ->innerJoin('ss_shop_product_prices', 'ss_shop_product_prices.product_id=cms_content_element.id');
//            ->innerJoin('cms_storage_file', 'cms_storage_file.id=cms_content_element.image_id');

        return $query;
    }

    public function getStorageFile()
    {
        if ($this->image) {
            return Url::getCdnUrl() . Url::getUploadsImagesPath() . $this->image->cluster_file;
        }
        if ($this->parentContentElement && $this->parentContentElement->image) {
            return Url::getCdnUrl() . Url::getUploadsImagesPath() . $this->parentContentElement->image->cluster_file;
        }

        return \common\helpers\Image::getPhotoDefault();
    }

    public function getIsAvailability()
    {
        return $this->new_quantity > 0;
    }

    public function getCategoryId()
    {
        if ($this->parentContentElement) {
            return $this->parentContentElement->tree_id;
        }
        return $this->tree_id;
    }


    public function getCurrentPrice()
    {
        return $this->price->price;
    }

    public function getNameForFeed()
    {
        if ($this->parentContentElement) {
            return $this->parentContentElement->name;
        }
        return $this->name;
    }

    public function getOldPrice()
    {
        return $this->price->max_price;
    }

    public function getLotNumber($code = null)
    {
        if (!$code) {
            $code = $this->code;
            if ($this->content_id == 5) {
                $code = $this->parentContentElement->code;
            }
        }

        if ($this->content_id == 10) {
            $code = $this->parentContentElement->parentContentElement->code;
        }
        return $this->id . '-' . $code;
    }

    public function getPublicUrl($schema = false, $params = [], $lotNum = null)
    {
        $url = ['/products/' . $this->getLotNumber($lotNum)];
        $url = ArrayHelper::merge($url, $params);
        return \yii\helpers\Url::to($url, $schema);
    }


    public function getShopProduct()
    {
        return $this->hasOne(ShopProduct::class, ['id' => 'id']);
    }

    public function getBrandName()
    {
        $subQuery = CmsContentElementProperty::find()->select('value')->andWhere(['element_id' => $this->id, 'property_id' => 218])->andWhere('value IS NOT NULL');
        $brand = CmsContentElement::findOne($subQuery);
        if ($brand) {
            return $brand->name;
        }

        return 'ShopAndShow';
    }

    public function getGoogleCategoryName()
    {
        $subQuery = CmsTreeTypeProperty::find()->select('id')->andWhere(['code' => 'googleCategoryName']);
        $model = CmsTreeProperty::find()->andWhere(['property_id' => $subQuery, 'element_id' => $this->tree_id])->one();

        if ($model) {
            return $model->value;
        }
        return '';
    }

    public function getIsBasePrice()
    {
        return $this->price->price == $this->price->max_price;
        if ($this->content_id == self::LOT) {
            return $this->price->type_price_id == 10;
        }
        if ($this->content_id == self::CARD) {
            return $this->parentContentElement->price->type_price_id == 10;
        }
        return true;
    }


    public function isActiveForSale()
    {
        $notPublic = $this->getPropertyNotPublic();
//        $notPublic = $this->relatedPropertiesModel->getAttribute('NOT_PUBLIC');

        return ($notPublic == Cms::BOOL_Y || $this->active == Cms::BOOL_N || $this->new_quantity < 1) ? false : true;
    }


    public static function getLot($id)
    {
        /** @var CmsContentElement $model */
        $model = self::find()->andWhere(['id' => $id])->one();
        if ($model->parentContentElement) {
            $model = $model->parentContentElement;
            if ($model->parentContentElement) {
                $model = $model->parentContentElement;
            }
        }

        return $model;
    }

    public function getPropertyActivePriceId()
    {
        // 174 or 82
//        $cmsContentProperty = CmsContentProperty::getDb()->cache(function ($db) {
        $cmsContentProperty = CmsContentProperty::find()->andWhere(['code' => 'PRICE_ACTIVE', 'content_id' => $this->content_id])->one();
//        });
        if ($cmsContentProperty) {
            $model = CmsContentElementProperty::find()->andWhere(['property_id' => $cmsContentProperty->id, 'element_id' => $this->id])->one();
            if ($model) {
                return $model->value;
            }
        }
        return false;
    }

    public function getPropertyNotPublic()
    {
//        $cmsContentProperty = CmsContentProperty::getDb()->cache(function ($db) {
        $cmsContentProperty = CmsContentProperty::find()->andWhere(['code' => 'NOT_PUBLIC', 'content_id' => $this->content_id])->one();

//        });
        if ($cmsContentProperty) {
            $model = CmsContentElementProperty::find()->andWhere(['property_id' => $cmsContentProperty->id, 'element_id' => $this->id])->one();
            if ($model) {
                return $model->value;
            }
        }
    }

    /**
     * @inheritdoc
     * @return NewProductQuery
     */
    public static function find()
    {
        return new NewProductQuery(get_called_class());
    }

    public function getThumbnail()
    {
        /** Квадратные картинки */
        $w = 191;
        $h = 191;
        /** В Моде, кромеме Обуви картинки вытянутые */
        if (preg_match('/moda(?!\/obuv).*/xi', $this->url)) {
            $w = 169;
            $h = 284;
        }
        $image = null;

        if ($this->image) {
            $image = \Yii::$app->imaging->thumbnailUrlSS($this->image->src,
                new Thumbnail([
                    'w' => $w,
                    'h' => $h,
                ]), $this->code
            );

        }

        return $image;
    }

    public function hasDiscount()
    {
        return $this->new_price < $this->new_price_old;
    }
}