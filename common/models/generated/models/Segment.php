<?php

namespace common\models\generated\models;

use Yii;

/**
 * This is the model class for table "segment".
 *
 * @property integer $id ID
 * @property string $name Name
 * @property string $description Description
 * @property integer $active Active
 * @property integer $generated Generated
 * @property string $generated_file Generated File
 * @property string $products Products
 * @property double $price_from Price From
 * @property double $price_to Price To
 * @property double $sale_from Sale From
 * @property double $sale_to Sale To
 * @property string $sort Sort
 * @property string $etalon_clothing_size Etalon Clothing Size
 * @property string $etalon_shoe_size Etalon Shoe Size
 * @property string $etalon_sock_size Etalon Sock Size
 * @property string $etalon_jewelry_size Etalon Jewelry Size
 * @property string $etalon_textile_size Etalon Textile Size
 * @property string $etalon_pillow_size Etalon Pillow Size
 * @property string $etalon_bed_linen_size Etalon Bed Linen Size
 * @property string $etalon_bra_size Etalon Bra Size
 * @property string $etalon_cap_size Etalon Cap Size
 * @property string $color Color
 * @property string $brand Brand
 * @property string $tree_ids Tree Ids
 * @property string $material Material
 * @property string $insert Insert
 * @property string $price_types Price Types
 * @property integer $created_at Created At
 * @property integer $updated_at Updated At
 * @property integer $created_by Created By
 * @property integer $updated_by Updated By
 * @property string $season Season
 * @property string $param_types Param Types
 * @property string $disable_products Disable Products
 * @property string $first_products First Products
 * @property string $name_lot Name Lot
 * @property integer $only_discount Only Discount
 * @property integer $hide_from_catalog Hide From Catalog
 * @property integer $regenerate Regenerate
 * @property integer $start_timestamp Start Timestamp
 * @property integer $end_timestamp End Timestamp
 * @property integer $modification_available_percent Modification Available Percent
 * @property integer $calc_price_modifications Calc Price Modifications
 * @property integer $without_sale Without Sale
 * @property string $badge Badge
 * @property string $badge_2 Badge 2
 * @property integer $disabled Disabled
 *
     * @property SegmentCardsDisable[] $segmentCardsDisables
     * @property SegmentProductCard[] $segmentProductCards
     * @property SegmentSegmentFilters[] $segmentSegmentFilters
    */
class Segment extends \common\ActiveRecord
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
            'timestamp' => \yii\behaviors\TimestampBehavior::class,
            'author' => \yii\behaviors\BlameableBehavior::class,
        ];
    }

    /**
    * @inheritdoc
    */
    public static function tableName()
    {
        return 'segment';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['name', 'sort'], 'required'],
            [['description', 'products', 'etalon_clothing_size', 'etalon_shoe_size', 'etalon_sock_size', 'etalon_jewelry_size', 'etalon_textile_size', 'etalon_pillow_size', 'etalon_bed_linen_size', 'etalon_bra_size', 'etalon_cap_size', 'color', 'tree_ids', 'price_types', 'season', 'param_types', 'disable_products', 'first_products'], 'string'],
            [['active', 'generated', 'created_at', 'updated_at', 'created_by', 'updated_by', 'only_discount', 'hide_from_catalog', 'regenerate', 'start_timestamp', 'end_timestamp', 'modification_available_percent', 'calc_price_modifications', 'without_sale', 'disabled'], 'integer'],
            [['price_from', 'price_to', 'sale_from', 'sale_to'], 'number'],
            [['name', 'generated_file', 'sort', 'brand', 'material', 'insert', 'badge', 'badge_2'], 'string', 'max' => 255],
            [['name_lot'], 'string', 'max' => 256],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'description' => 'Description',
            'active' => 'Active',
            'generated' => 'Generated',
            'generated_file' => 'Generated File',
            'products' => 'Products',
            'price_from' => 'Price From',
            'price_to' => 'Price To',
            'sale_from' => 'Sale From',
            'sale_to' => 'Sale To',
            'sort' => 'Sort',
            'etalon_clothing_size' => 'Etalon Clothing Size',
            'etalon_shoe_size' => 'Etalon Shoe Size',
            'etalon_sock_size' => 'Etalon Sock Size',
            'etalon_jewelry_size' => 'Etalon Jewelry Size',
            'etalon_textile_size' => 'Etalon Textile Size',
            'etalon_pillow_size' => 'Etalon Pillow Size',
            'etalon_bed_linen_size' => 'Etalon Bed Linen Size',
            'etalon_bra_size' => 'Etalon Bra Size',
            'etalon_cap_size' => 'Etalon Cap Size',
            'color' => 'Color',
            'brand' => 'Brand',
            'tree_ids' => 'Tree Ids',
            'material' => 'Material',
            'insert' => 'Insert',
            'price_types' => 'Price Types',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'season' => 'Season',
            'param_types' => 'Param Types',
            'disable_products' => 'Disable Products',
            'first_products' => 'First Products',
            'name_lot' => 'Name Lot',
            'only_discount' => 'Only Discount',
            'hide_from_catalog' => 'Hide From Catalog',
            'regenerate' => 'Regenerate',
            'start_timestamp' => 'Start Timestamp',
            'end_timestamp' => 'End Timestamp',
            'modification_available_percent' => 'Modification Available Percent',
            'calc_price_modifications' => 'Calc Price Modifications',
            'without_sale' => 'Without Sale',
            'badge' => 'Badge',
            'badge_2' => 'Badge 2',
            'disabled' => 'Disabled',
            ];
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getSegmentCardsDisables()
    {
        return $this->hasMany($this->called_class_namespace . '\SegmentCardsDisable', ['segment_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getSegmentProductCards()
    {
        return $this->hasMany($this->called_class_namespace . '\SegmentProductCard', ['segment_id' => 'id']);
    }

        /**
     * @return \yii\db\ActiveQuery
     * @throws \Exception
    */
    public function getSegmentSegmentFilters()
    {
        return $this->hasMany($this->called_class_namespace . '\SegmentSegmentFilters', ['segment_id' => 'id']);
    }
    
    /**
     * @inheritdoc
     * @return \common\models\query\SegmentQuery the active query used by this AR class.
    */
    public static function find()
    {
        return new \common\models\query\SegmentQuery(get_called_class());
    }
}
