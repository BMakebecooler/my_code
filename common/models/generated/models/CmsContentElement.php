<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "cms_content_element".
 *
 * @property integer $id ID
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property integer $published_at Published At
 * @property integer $published_to Published To
 * @property integer $priority Priority
 * @property string $active Active
 * @property string $name Name
 * @property integer $image_id Image ID
 * @property integer $image_full_id Image Full ID
 * @property string $code Code
 * @property string $description_short Description Short
 * @property string $description_full Description Full
 * @property integer $content_id Content ID
 * @property integer $tree_id Tree ID
 * @property integer $show_counter Show Counter
 * @property integer $show_counter_start Show Counter Start
 * @property string $meta_title Meta Title
 * @property string $meta_description Meta Description
 * @property string $meta_keywords Meta Keywords
 * @property string $description_short_type Description Short Type
 * @property string $description_full_type Description Full Type
 * @property integer $parent_content_element_id Parent Content Element ID
 * @property integer $bitrix_id Bitrix ID
 * @property string $is_base Is Base
 * @property integer $count_children Count Children
 * @property integer $guid_id Guid ID
 * @property integer $kfss_id Kfss ID
 * @property integer $count_images Count Images
 * @property string $new_guid New Guid
 * @property string $new_lot_num New Lot Num
 * @property string $new_lot_name New Lot Name
 * @property string $new_characteristics New Characteristics
 * @property string $new_technical_details New Technical Details
 * @property string $new_product_kit New Product Kit
 * @property string $new_advantages New Advantages
 * @property string $new_advantages_addons New Advantages Addons
 * @property integer $new_not_public New Not Public
 * @property integer $new_quantity New Quantity
 * @property integer $new_rest New Rest
 * @property integer $new_price_active New Price Active
 * @property string $new_price New Price
 * @property string $new_price_old New Price Old
 * @property string $new_discount_percent New Discount Percent
 * @property integer $new_brand_id New Brand ID
 * @property integer $new_season_id New Season ID
 * @property string $new_rating New Rating
 * @property integer $hide_from_catalog Hide From Catalog
 * @property integer $badge_1 Badge 1
 * @property integer $badge_2 Badge 2
 * @property integer $sort_weight Sort Weight
 * @property integer $hide_from_catalog_image Hide From Catalog Image
 *
     * @property BuhECommAbc[] $buhECommAbcs
     * @property CmsContentElement $parentContentElement
     * @property CmsContentElement[] $cmsContentElements
     * @property CmsStorageFile $imageFull
     * @property CmsStorageFile $image
     * @property CmsContent $content
     * @property CmsUser $createdBy
     * @property CmsTree $tree
     * @property CmsUser $updatedBy
     * @property CmsContentElement2cmsUser[] $cmsContentElement2cmsUsers
     * @property CmsUser[] $cmsUsers
     * @property CmsContentElementFile[] $cmsContentElementFiles
     * @property CmsStorageFile[] $storageFiles
     * @property CmsContentElementImage[] $cmsContentElementImages
     * @property CmsStorageFile[] $storageFiles0
     * @property CmsContentElementProperty[] $cmsContentElementProperties
     * @property CmsContentElementRelation[] $cmsContentElementRelations
     * @property CmsContentElementRelation[] $cmsContentElementRelations0
     * @property CmsContentElement[] $relatedContentElements
     * @property CmsContentElement[] $contentElements
     * @property CmsContentElementTree[] $cmsContentElementTrees
     * @property CmsTree[] $trees
     * @property ProductAbcAddition[] $productAbcAdditions
     * @property ProductAbcAddition[] $productAbcAdditions0
     * @property ProductParamProduct[] $productParamProducts
     * @property ProductParam[] $productParams
     * @property Reviews2Message[] $reviews2Messages
     * @property SegmentProducts[] $segmentProducts
     * @property Segment[] $segments
     * @property ShopBasket[] $shopBaskets
     * @property ShopFuser[] $shopFusers
     * @property ShopFuserFavorites[] $shopFuserFavorites
     * @property ShopOrder[] $shopOrders
     * @property ShopProduct $shopProduct
     * @property ShopViewedProduct[] $shopViewedProducts
     * @property SsShares[] $ssShares
     * @property SsShopProductPrices $ssShopProductPrices
     * @property SsUserVote[] $ssUserVotes
    */
class CmsContentElement extends \common\ActiveRecord
{
    private $called_class_namespace;

    public function __construct()
    {
        $this->called_class_namespace = substr(get_called_class(), 0, strrpos(get_called_class(), '\\'));
        parent::__construct();
    }

                                                                                                                                                                                                                        
    /**
     * @inheritdoc
    */
    public function behaviors()
    {
        return [
            'author' => \yii\behaviors\BlameableBehavior::class,
            'timestamp' => \yii\behaviors\TimestampBehavior::class,
        ];
    }

    /**
    * @inheritdoc
    */
    public static function tableName()
    {
        return 'cms_content_element';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['created_by', 'updated_by', 'created_at', 'updated_at', 'published_at', 'published_to', 'priority', 'image_id', 'image_full_id', 'content_id', 'tree_id', 'show_counter', 'show_counter_start', 'parent_content_element_id', 'bitrix_id', 'count_children', 'guid_id', 'kfss_id', 'count_images', 'new_not_public', 'new_quantity', 'new_rest', 'new_price_active', 'new_brand_id', 'new_season_id', 'hide_from_catalog', 'badge_1', 'badge_2', 'sort_weight', 'hide_from_catalog_image'], 'integer'],
            [['name', 'code', 'meta_title'], 'required'],
            [['description_short', 'description_full', 'meta_description', 'meta_keywords', 'new_characteristics', 'new_technical_details', 'new_product_kit', 'new_advantages', 'new_advantages_addons'], 'string'],
            [['new_price', 'new_price_old', 'new_discount_percent', 'new_rating'], 'number'],
            [['active', 'is_base'], 'string', 'max' => 1],
            [['name', 'code', 'new_guid', 'new_lot_num', 'new_lot_name'], 'string', 'max' => 255],
            [['meta_title'], 'string', 'max' => 500],
            [['description_short_type', 'description_full_type'], 'string', 'max' => 10],
            [['content_id', 'code'], 'unique', 'targetAttribute' => ['content_id', 'code'], 'message' => 'The combination of Code and Content ID has already been taken.'],
            [['tree_id', 'code'], 'unique', 'targetAttribute' => ['tree_id', 'code'], 'message' => 'The combination of Code and Tree ID has already been taken.'],
            [['new_guid'], 'unique'],
            [['parent_content_element_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsContentElement::className(), 'targetAttribute' => ['parent_content_element_id' => 'id']],
            [['image_full_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsStorageFile::className(), 'targetAttribute' => ['image_full_id' => 'id']],
            [['image_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsStorageFile::className(), 'targetAttribute' => ['image_id' => 'id']],
            [['content_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsContent::className(), 'targetAttribute' => ['content_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['tree_id'], 'exist', 'skipOnError' => true, 'targetClass' => CmsTree::className(), 'targetAttribute' => ['tree_id' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => CmsUser::className(), 'targetAttribute' => ['updated_by' => 'id']],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'published_at' => 'Published At',
            'published_to' => 'Published To',
            'priority' => 'Priority',
            'active' => 'Active',
            'name' => 'Name',
            'image_id' => 'Image ID',
            'image_full_id' => 'Image Full ID',
            'code' => 'Code',
            'description_short' => 'Description Short',
            'description_full' => 'Description Full',
            'content_id' => 'Content ID',
            'tree_id' => 'Tree ID',
            'show_counter' => 'Show Counter',
            'show_counter_start' => 'Show Counter Start',
            'meta_title' => 'Meta Title',
            'meta_description' => 'Meta Description',
            'meta_keywords' => 'Meta Keywords',
            'description_short_type' => 'Description Short Type',
            'description_full_type' => 'Description Full Type',
            'parent_content_element_id' => 'Parent Content Element ID',
            'bitrix_id' => 'Bitrix ID',
            'is_base' => 'Is Base',
            'count_children' => 'Count Children',
            'guid_id' => 'Guid ID',
            'kfss_id' => 'Kfss ID',
            'count_images' => 'Count Images',
            'new_guid' => 'New Guid',
            'new_lot_num' => 'New Lot Num',
            'new_lot_name' => 'New Lot Name',
            'new_characteristics' => 'New Characteristics',
            'new_technical_details' => 'New Technical Details',
            'new_product_kit' => 'New Product Kit',
            'new_advantages' => 'New Advantages',
            'new_advantages_addons' => 'New Advantages Addons',
            'new_not_public' => 'New Not Public',
            'new_quantity' => 'New Quantity',
            'new_rest' => 'New Rest',
            'new_price_active' => 'New Price Active',
            'new_price' => 'New Price',
            'new_price_old' => 'New Price Old',
            'new_discount_percent' => 'New Discount Percent',
            'new_brand_id' => 'New Brand ID',
            'new_season_id' => 'New Season ID',
            'new_rating' => 'New Rating',
            'hide_from_catalog' => 'Hide From Catalog',
            'badge_1' => 'Badge 1',
            'badge_2' => 'Badge 2',
            'sort_weight' => 'Sort Weight',
            'hide_from_catalog_image' => 'Hide From Catalog Image',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getBuhECommAbcs()
    {
        return $this->hasMany($this->called_class_namespace . '\BuhECommAbc', ['product_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getParentContentElement()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsContentElement', ['id' => 'parent_content_element_id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsContentElements()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsContentElement', ['parent_content_element_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getImageFull()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsStorageFile', ['id' => 'image_full_id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getImage()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsStorageFile', ['id' => 'image_id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getContent()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsContent', ['id' => 'content_id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCreatedBy()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsUser', ['id' => 'created_by']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getTree()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsTree', ['id' => 'tree_id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getUpdatedBy()
    {
        return $this->hasOne($this->called_class_namespace . '\CmsUser', ['id' => 'updated_by']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsContentElement2cmsUsers()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsContentElement2cmsUser', ['cms_content_element_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsUsers()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsUser', ['id' => 'cms_user_id'])->viaTable('cms_content_element2cms_user', ['cms_content_element_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsContentElementFiles()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsContentElementFile', ['content_element_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getStorageFiles()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsStorageFile', ['id' => 'storage_file_id'])->viaTable('cms_content_element_file', ['content_element_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsContentElementImages()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsContentElementImage', ['content_element_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getStorageFiles0()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsStorageFile', ['id' => 'storage_file_id'])->viaTable('cms_content_element_image', ['content_element_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsContentElementProperties()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsContentElementProperty', ['element_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsContentElementRelations()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsContentElementRelation', ['content_element_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsContentElementRelations0()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsContentElementRelation', ['related_content_element_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getRelatedContentElements()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsContentElement', ['id' => 'related_content_element_id'])->viaTable('cms_content_element_relation', ['content_element_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getContentElements()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsContentElement', ['id' => 'content_element_id'])->viaTable('cms_content_element_relation', ['related_content_element_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getCmsContentElementTrees()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsContentElementTree', ['element_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getTrees()
    {
        return $this->hasMany($this->called_class_namespace . '\CmsTree', ['id' => 'tree_id'])->viaTable('cms_content_element_tree', ['element_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getProductAbcAdditions()
    {
        return $this->hasMany($this->called_class_namespace . '\ProductAbcAddition', ['product_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getProductAbcAdditions0()
    {
        return $this->hasMany($this->called_class_namespace . '\ProductAbcAddition', ['source_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getProductParamProducts()
    {
        return $this->hasMany($this->called_class_namespace . '\ProductParamProduct', ['product_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getProductParams()
    {
        return $this->hasMany($this->called_class_namespace . '\ProductParam', ['id' => 'product_param_id'])->viaTable('product_param_product', ['product_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getReviews2Messages()
    {
        return $this->hasMany($this->called_class_namespace . '\Reviews2Message', ['element_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getSegmentProducts()
    {
        return $this->hasMany($this->called_class_namespace . '\SegmentProducts', ['product_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getSegments()
    {
        return $this->hasMany($this->called_class_namespace . '\Segment', ['id' => 'segment_id'])->viaTable('segment_products', ['product_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopBaskets()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopBasket', ['main_product_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopFusers()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopFuser', ['store_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopFuserFavorites()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopFuserFavorites', ['shop_product_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopOrders()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopOrder', ['store_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopProduct()
    {
        return $this->hasOne($this->called_class_namespace . '\ShopProduct', ['id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getShopViewedProducts()
    {
        return $this->hasMany($this->called_class_namespace . '\ShopViewedProduct', ['shop_product_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getSsShares()
    {
        return $this->hasMany($this->called_class_namespace . '\SsShares', ['image_product_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getSsShopProductPrices()
    {
        return $this->hasOne($this->called_class_namespace . '\SsShopProductPrices', ['product_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getSsUserVotes()
    {
        return $this->hasMany($this->called_class_namespace . '\SsUserVote', ['cms_content_element_id' => 'id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\CmsContentElementQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\CmsContentElementQuery(get_called_class());
    }
}
